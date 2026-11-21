<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class ZazuEmptyPrivateStorageRestoreTest extends TestCase
{
    public function test_restore_clears_stale_private_files_when_backup_declares_empty_private_storage(): void
    {
        $this->skipWhenZipIsUnavailable();
        $environment = $this->prepareEmptyPrivateStorageEnvironment();

        try {
            $archive = $this->createEmptyPrivateStorageBackup($environment['output']);
            $this->assertArchiveDeclaresEmptyPrivateStorage($archive);
            $this->restoreOverStalePrivateFile($archive, $environment['private_root']);
            $this->assertDatabaseAndPrivateStorageWereRestored($environment);
        } finally {
            $this->restoreTestEnvironment($environment);
        }
    }

    private function skipWhenZipIsUnavailable(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }
    }

    private function prepareEmptyPrivateStorageEnvironment(): array
    {
        $environment = [
            'database' => storage_path('app/zazu-empty-private-restore.sqlite'),
            'output' => storage_path('app/zazu-empty-private-restore-backups'),
            'private_root' => storage_path('app/private'),
            'preserved_private' => storage_path('app/.zazu-private-preserve-'.Str::ulid()),
            'original_default' => config('database.default'),
            'original_database' => config('database.connections.sqlite.database'),
            'had_private_directory' => is_dir(storage_path('app/private')),
        ];

        File::ensureDirectoryExists(dirname($environment['database']));
        File::delete($environment['database']);
        File::deleteDirectory($environment['output']);

        if ($environment['had_private_directory']
            && !rename($environment['private_root'], $environment['preserved_private'])) {
            throw new \RuntimeException('Could not preserve the pre-test private storage directory.');
        }

        File::ensureDirectoryExists($environment['private_root']);
        File::put($environment['database'], '');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $environment['database'],
        ]);

        return $environment;
    }

    private function createEmptyPrivateStorageBackup(string $output): string
    {
        DB::purge('sqlite');
        DB::connection('sqlite')->getSchemaBuilder()->create('empty_private_restore_probe', function ($table) {
            $table->id();
            $table->string('value');
        });
        DB::table('empty_private_restore_probe')->insert(['value' => 'before backup']);

        $this->artisan('zazu:backup', ['--output' => $output])
            ->assertExitCode(0);

        $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
        $this->assertCount(1, $archives);

        return $archives[0];
    }

    private function assertArchiveDeclaresEmptyPrivateStorage(string $archive): void
    {
        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive));

        try {
            $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame(['database', 'private_storage'], $manifest['contents'] ?? null);

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $this->assertStringNotContainsString('storage/private/', (string) $zip->getNameIndex($index));
            }
        } finally {
            $zip->close();
        }
    }

    private function restoreOverStalePrivateFile(string $archive, string $privateRoot): void
    {
        File::put($privateRoot.'/stale-private-file.txt', 'must not survive an empty private-storage restore');
        DB::table('empty_private_restore_probe')->update(['value' => 'changed after backup']);

        $this->artisan('zazu:restore', [
            'archive' => $archive,
            '--force' => true,
        ])->assertExitCode(0);

        DB::purge('sqlite');
    }

    private function assertDatabaseAndPrivateStorageWereRestored(array $environment): void
    {
        $privateRoot = $environment['private_root'];
        $this->assertSame(
            'before backup',
            DB::connection('sqlite')->table('empty_private_restore_probe')->value('value'),
        );
        $this->assertDirectoryExists($privateRoot);
        $this->assertFileDoesNotExist($privateRoot.'/stale-private-file.txt');
        $this->assertSame([], File::allFiles($privateRoot));
    }

    private function restoreTestEnvironment(array $environment): void
    {
        DB::purge('sqlite');
        File::deleteDirectory($environment['private_root']);

        if ($environment['had_private_directory'] && is_dir($environment['preserved_private'])) {
            if (!rename($environment['preserved_private'], $environment['private_root'])) {
                throw new \RuntimeException('Could not restore the pre-test private storage directory.');
            }
        } elseif (!$environment['had_private_directory']) {
            File::deleteDirectory($environment['preserved_private']);
        }

        foreach ([
            $environment['database'],
            $environment['database'].'-wal',
            $environment['database'].'-shm',
        ] as $path) {
            File::delete($path);
        }

        File::deleteDirectory($environment['output']);
        config([
            'database.default' => $environment['original_default'],
            'database.connections.sqlite.database' => $environment['original_database'],
        ]);
        DB::purge('sqlite');
    }
}
