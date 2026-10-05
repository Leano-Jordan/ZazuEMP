<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use ZipArchive;

class ZazuBackupRestoreTest extends TestCase
{

    public function test_backup_and_restore_round_trip_preserves_a_sqlite_record(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $database = storage_path('app/zazu-roundtrip.sqlite');
        $output = storage_path('app/zazu-roundtrip-backups');
        $privateProbe = storage_path('app/private/zazu-roundtrip/media-probe.txt');
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');

        File::ensureDirectoryExists(dirname($database));
        File::delete($database);
        File::put($database, '');
        File::deleteDirectory($output);
        File::ensureDirectoryExists(dirname($privateProbe));
        File::put($privateProbe, 'private media before backup');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
        ]);

        try {
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

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $this->assertStringNotContainsString('\\', (string) $zip->getNameIndex($index));
            }

            $zip->close();

            DB::table('backup_probe')->update(['value' => 'changed after backup']);
            File::delete($privateProbe);

            $restore = $this->artisan('zazu:restore', [
    'archive' => $archive,
    '--force' => true,
]);

$exitCode = $restore->run();

$this->assertSame(0, $exitCode, Artisan::output());

            DB::purge('sqlite');

            $this->assertSame(
                'before backup',
                DB::connection('sqlite')->table('backup_probe')->value('value')
            );
            $this->assertFileExists($privateProbe);
            $this->assertSame('private media before backup', File::get($privateProbe));
        } finally {
            DB::purge('sqlite');
            File::delete($database);
            File::deleteDirectory($output);
            File::delete($privateProbe);
            config([
                'database.default' => $originalDefault,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
            DB::purge('sqlite');
        }
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

    public function test_restore_rejects_a_corrupt_sqlite_database_before_replacing_private_storage(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $archive = storage_path('app/zazu-corrupt-sqlite-test.zip');
        $probe = storage_path('app/private/corrupt-restore-probe.txt');
        File::ensureDirectoryExists(dirname($archive));
        File::ensureDirectoryExists(dirname($probe));
        File::put($probe, 'must survive');

        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'Zazu EMP',
            'format_version' => 1,
            'database_driver' => 'sqlite',
        ]));
        $zip->addFromString('database.sqlite', 'not a sqlite database');
        $zip->addFromString('storage/private/example.txt', 'restored later');
        $zip->close();

        $originalDefault = config('database.default');

        config(['database.default' => 'sqlite']);

        try {
            $this->artisan('zazu:restore', ['archive' => $archive, '--force' => true])
                ->assertExitCode(1);

            $this->assertSame('must survive', File::get($probe));
        } finally {
            config(['database.default' => $originalDefault]);
            File::delete($archive);
            File::delete($probe);
        }
    }

    public function test_restore_rejects_unsupported_entries_before_touching_the_installation(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $archive = storage_path('app/zazu-unsupported-test.zip');
        $probe = storage_path('app/private/restore-safety-probe.txt');
        File::ensureDirectoryExists(dirname($archive));
        File::ensureDirectoryExists(dirname($probe));
        File::put($probe, 'must survive');

        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'Zazu EMP',
            'format_version' => 1,
            'database_driver' => 'sqlite',
        ]));
        $zip->addFromString('database.sqlite', 'not a real database');
        $zip->addFromString('unexpected.txt', 'must be rejected');
        $zip->close();

        try {
            $this->artisan('zazu:restore', ['archive' => $archive, '--force' => true])
                ->assertExitCode(1);

            $this->assertSame('must survive', File::get($probe));
        } finally {
            File::delete($archive);
            File::delete($probe);
        }
    }

    public function test_restore_rejects_duplicate_archive_entries(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $archive = storage_path('app/zazu-duplicate-test.zip');
        File::ensureDirectoryExists(dirname($archive));

        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'Zazu EMP',
            'format_version' => 1,
            'database_driver' => 'sqlite',
        ]));
        // ZipArchive may overwrite/reject same-name additions depending on the
        // libzip build. Build two distinct same-length names first, then make both
        // archive entries resolve to the same name in the ZIP metadata.
        $zip->addFromString('database.sqlite', 'first');
        $zip->addFromString('database.sqlitf', 'second');
        $zip->close();

        $raw = File::get($archive);
        $this->assertGreaterThanOrEqual(2, substr_count($raw, 'database.sqlitf'));
        File::put($archive, str_replace('database.sqlitf', 'database.sqlite', $raw));

        try {
            $this->artisan('zazu:restore', ['archive' => $archive, '--force' => true])
                ->assertExitCode(1);
        } finally {
            File::delete($archive);
        }
    }

}
