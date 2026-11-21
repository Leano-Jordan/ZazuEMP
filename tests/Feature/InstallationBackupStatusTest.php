<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
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
