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
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $database = storage_path('app/zazu-empty-private-restore.sqlite');
        $output = storage_path('app/zazu-empty-private-restore-backups');
        $privateRoot = storage_path('app/private');
        $preservedPrivate = storage_path('app/.zazu-private-preserve-'.Str::ulid());
        $staleFile = $privateRoot.'/stale-private-file.txt';
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');
        $hadPrivateDirectory = is_dir($privateRoot);

        File::ensureDirectoryExists(dirname($database));
        File::delete($database);
        File::deleteDirectory($output);

        if ($hadPrivateDirectory && !rename($privateRoot, $preservedPrivate)) {
            $this->fail('Could not preserve the pre-test private storage directory.');
        }

        File::ensureDirectoryExists($privateRoot);
        File::put($database, '');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
        ]);

        try {
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

            $zip = new ZipArchive();
            $this->assertSame(true, $zip->open($archives[0]));
            $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame(['database', 'private_storage'], $manifest['contents'] ?? null);
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $this->assertStringNotContainsString('storage/private/', (string) $zip->getNameIndex($index));
            }
            $zip->close();

            File::put($staleFile, 'must not survive an empty private-storage restore');
            DB::table('empty_private_restore_probe')->update(['value' => 'changed after backup']);

            $this->artisan('zazu:restore', [
                'archive' => $archives[0],
                '--force' => true,
            ])->assertExitCode(0);

            DB::purge('sqlite');

            $this->assertSame(
                'before backup',
                DB::connection('sqlite')->table('empty_private_restore_probe')->value('value'),
            );
            $this->assertDirectoryExists($privateRoot);
            $this->assertFileDoesNotExist($staleFile);
            $this->assertSame([], File::allFiles($privateRoot));
        } finally {
            DB::purge('sqlite');
            File::deleteDirectory($privateRoot);
            if ($hadPrivateDirectory && is_dir($preservedPrivate)) {
                rename($preservedPrivate, $privateRoot);
            }
            foreach ([$database, $database.'-wal', $database.'-shm'] as $path) {
                File::delete($path);
            }
            File::deleteDirectory($output);
            File::deleteDirectory($preservedPrivate);
            config([
                'database.default' => $originalDefault,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
            DB::purge('sqlite');
        }
    }
}
