<?php

namespace Tests\\Feature;

use Illuminate\\Support\\Facades\\Artisan;
use Illuminate\\Support\\Facades\\File;
use Tests\\TestCase;

class InstallationBackupStorageGuardTest extends TestCase
{
    public function test_installation_backup_fails_closed_for_remote_private_storage(): void
    {
        $output = storage_path('app/remote-private-backup-guard-test');
        File::deleteDirectory($output);
        config(['filesystems.disks.private.driver' => 's3']);

        try {
            $exitCode = Artisan::call('zazu:backup', ['--output' => $output]);

            $this->assertSame(1, $exitCode);
            $this->assertStringContainsString('only local private storage', Artisan::output());
            $this->assertDirectoryDoesNotExist($output);
        } finally {
            config(['filesystems.disks.private.driver' => 'local']);
            File::deleteDirectory($output);
        }
    }

    public function test_installation_restore_fails_closed_before_reading_archive_for_remote_private_storage(): void
    {
        config(['filesystems.disks.private.driver' => 's3']);

        try {
            $exitCode = Artisan::call('zazu:restore', [
                'archive' => storage_path('app/does-not-exist-zazu-backup.zip'),
                '--force' => true,
            ]);

            $this->assertSame(1, $exitCode);
            $this->assertStringContainsString('only local private storage', Artisan::output());
        } finally {
            config(['filesystems.disks.private.driver' => 'local']);
        }
    }
}
