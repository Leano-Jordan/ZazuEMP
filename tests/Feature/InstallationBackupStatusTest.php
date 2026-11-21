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


    public function test_fresh_local_archive_without_verified_independent_copy_remains_attention(): void
    {
        $directory = storage_path('framework/testing/zazu-backup-status-'.Str::uuid());
        File::ensureDirectoryExists($directory);
        File::put($directory.DIRECTORY_SEPARATOR.'zazu-backup-fresh.zip', 'archive');

        try {
            $status = (new ZazuBackupStatus($directory))->inspect();

            $this->assertSame('attention', $status['status']);
            $this->assertSame('attention', $status['secondary_status']);
            $this->assertStringContainsString('No independent backup destination', $status['secondary_message']);

            File::put($directory.DIRECTORY_SEPARATOR.'.secondary-copy-status.json', json_encode([
                'status' => 'verified',
                'last_successful_at' => now()->toIso8601String(),
                'archive' => 'zazu-backup-fresh.zip',
            ], JSON_THROW_ON_ERROR));

            $verified = (new ZazuBackupStatus($directory))->inspect();
            $this->assertSame('healthy', $verified['status']);
            $this->assertSame('healthy', $verified['secondary_status']);
        } finally {
            File::deleteDirectory($directory);
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
