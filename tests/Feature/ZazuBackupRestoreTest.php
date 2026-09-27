<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use ZipArchive;

class ZazuBackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_and_restore_round_trip_preserves_a_sqlite_record(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $database = storage_path('app/zazu-roundtrip.sqlite');
        $output = storage_path('app/zazu-roundtrip-backups');
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');

        File::ensureDirectoryExists(dirname($database));
        File::delete($database);
        File::deleteDirectory($output);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
        ]);

        DB::purge('sqlite');
        DB::connection('sqlite')->getSchemaBuilder()->create('backup_probe', function ($table) {
            $table->id();
            $table->string('value');
        });
        DB::table('backup_probe')->insert(['value' => 'before backup']);

        $this->artisan('zazu:backup', ['--output' => $output])
            ->assertExitCode(0);

        $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
        $this->assertCount(1, $archives);

        $archive = $archives[0];
        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive));
        $this->assertNotFalse($zip->locateName('manifest.json'));
        $this->assertNotFalse($zip->locateName('database.sqlite'));
        $zip->close();

        DB::table('backup_probe')->update(['value' => 'changed after backup']);

        $this->artisan('zazu:restore', [
            'archive' => $archive,
            '--force' => true,
        ])->assertExitCode(0);

        DB::purge('sqlite');

        $this->assertSame(
            'before backup',
            DB::connection('sqlite')->table('backup_probe')->value('value')
        );

        File::delete($database);
        File::deleteDirectory($output);
        config([
            'database.default' => $originalDefault,
            'database.connections.sqlite.database' => $originalDatabase,
        ]);
    }

    public function test_restore_rejects_unsafe_archive_paths_before_writing_files(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $archive = storage_path('app/zazu-unsafe-test.zip');
        File::ensureDirectoryExists(dirname($archive));

        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode(['application' => 'Zazu EMP']));
        $zip->addFromString('../outside.txt', 'must not be written');
        $zip->close();

        $outside = base_path('outside.txt');
        File::delete($outside);

        $this->artisan('zazu:restore', ['archive' => $archive, '--force' => true])
            ->assertExitCode(1);

        $this->assertFileDoesNotExist($outside);
        File::delete($archive);
    }
}
