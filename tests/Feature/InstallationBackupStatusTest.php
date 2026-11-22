<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\ZazuBackupStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class InstallationBackupStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_founder_control_centre_warns_when_no_installation_backup_exists(): void
    {
        $founder = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');
        $directory = storage_path('app/zazu-backups/daily');
        File::deleteDirectory($directory);

        try {
            $this->actingAs($founder)
                ->get(route('admin.sentinel'))
                ->assertOk()
                ->assertSee('Installation backup')
                ->assertSee('No scheduled backup has been recorded')
                ->assertSee('separate USB/cloud storage');
        } finally {
            File::deleteDirectory($directory);
        }
    }


    public function test_secondary_backup_health_requires_the_verified_archive_to_remain_present_and_unchanged(): void
    {
        $directory = storage_path('framework/testing/zazu-backup-status-'.Str::uuid());
        $secondaryDirectory = $directory.'-secondary';
        $secondaryArchive = $secondaryDirectory.DIRECTORY_SEPARATOR.'zazu-backup-fresh.zip';
        File::ensureDirectoryExists($directory);
        File::ensureDirectoryExists($secondaryDirectory);
        File::put($directory.DIRECTORY_SEPARATOR.'zazu-backup-fresh.zip', 'local archive');
        File::put($secondaryArchive, 'verified archive');

        try {
            $status = (new ZazuBackupStatus($directory))->inspect();

            $this->assertSame('attention', $status['status']);
            $this->assertSame('attention', $status['secondary_status']);
            $this->assertStringContainsString('No independent backup destination', $status['secondary_message']);

            File::put($directory.DIRECTORY_SEPARATOR.'.secondary-copy-status.json', json_encode([
                'status' => 'verified',
                'last_successful_at' => now()->toIso8601String(),
                'archive' => basename($secondaryArchive),
                'secondary_archive_path' => $secondaryArchive,
                'secondary_archive_sha256' => hash_file('sha256', $secondaryArchive),
                'secondary_archive_size_bytes' => filesize($secondaryArchive),
                'secondary_archive_modified_unix' => filemtime($secondaryArchive),
            ], JSON_THROW_ON_ERROR));

            $verified = (new ZazuBackupStatus($directory))->inspect();
            $this->assertSame('healthy', $verified['status']);
            $this->assertSame('healthy', $verified['secondary_status']);

            File::delete($secondaryArchive);
            $missing = (new ZazuBackupStatus($directory))->inspect();
            $this->assertSame('attention', $missing['status']);
            $this->assertSame('attention', $missing['secondary_status']);
            $this->assertStringContainsString('missing or its drive is unavailable', $missing['secondary_message']);

            File::put($secondaryArchive, 'tampered archive with changed size');
            $changed = (new ZazuBackupStatus($directory))->inspect();
            $this->assertSame('attention', $changed['status']);
            $this->assertSame('attention', $changed['secondary_status']);
            $this->assertStringContainsString('changed after verification', $changed['secondary_message']);
        } finally {
            File::deleteDirectory($directory);
            File::deleteDirectory($secondaryDirectory);
        }
    }

    public function test_business_owner_sees_backup_warning_but_staff_do_not_see_installation_backup_status(): void
    {
        $business = Business::create([
            'name' => 'Owner Backup Warning Business',
            'slug' => 'owner-backup-warning-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $business->users()->attach($owner->id, ['role' => 'owner']);
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $directory = storage_path('app/zazu-backups/daily');
        File::deleteDirectory($directory);

        try {
            $this->actingAs($owner)
                ->withSession(['zazu_business_id' => $business->id])
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Backup protection needs attention')
                ->assertSee('No scheduled backup has been recorded on this PC for this installation.')
                ->assertSee('Restoring one affects every business on this PC');

            $this->actingAs($staff)
                ->withSession(['zazu_business_id' => $business->id])
                ->get(route('dashboard'))
                ->assertOk()
                ->assertDontSee('Backup protection needs attention');
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_founder_control_centre_flags_an_overdue_installation_backup(): void
    {
        $founder = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');
        $directory = storage_path('app/zazu-backups/daily');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);
        $archive = $directory.DIRECTORY_SEPARATOR.'zazu-backup-test.zip';
        File::put($archive, 'backup-placeholder');
        touch($archive, now()->subHours(48)->timestamp);

        try {
            $this->actingAs($founder)
                ->get(route('admin.sentinel'))
                ->assertOk()
                ->assertSee('Backup overdue')
                ->assertSee('48 hours ago');
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
