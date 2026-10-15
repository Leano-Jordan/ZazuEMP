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
            $this->assertNotFalse($zip->locateName('storage/private/zazu-roundtrip/media-probe.txt'));

            $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame('Zazu EMP', $manifest['application'] ?? null);
            $this->assertSame(1, $manifest['format_version'] ?? null);
            $this->assertSame('sqlite', $manifest['database_driver'] ?? null);
            $this->assertSame(['database', 'private_storage'], $manifest['contents'] ?? null);
            $this->assertNotEmpty($manifest['backup_id'] ?? null);
            $this->assertNotEmpty($manifest['created_at'] ?? null);

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


    public function test_mysql_restore_attempts_pre_restore_rollback_after_partial_import_failure(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Fake MySQL command integration uses POSIX shell scripts.');
        }

        $root = storage_path('app/zazu-mysql-rollback-test-'.\Illuminate\Support\Str::ulid());
        $bin = $root.'/bin';
        $archive = $root.'/candidate.zip';
        $countFile = $root.'/mysql-count.txt';
        $logDirectory = $root.'/imports';
        $privateProbe = storage_path('app/private/zazu-mysql-rollback-probe.txt');
        $originalPath = getenv('PATH') ?: '';
        $originalDefault = config('database.default');
        $originalMysql = config('database.connections.mysql');

        File::ensureDirectoryExists($bin);
        File::ensureDirectoryExists($logDirectory);
        File::ensureDirectoryExists(dirname($privateProbe));
        File::put($privateProbe, 'original private data');

        File::put($bin.'/mysqldump', "#!/bin/sh\nprintf '%s\\n' '-- pre-restore snapshot marker'\n");
        File::put($bin.'/mysql', "#!/bin/sh\n".
            '$count=0'."\n".
            '[ -f '.escapeshellarg($countFile).' ] && count=$(cat '.escapeshellarg($countFile).")\n".
            '$count=$((count + 1))'."\n".
            'printf "%s" "$count" > '.escapeshellarg($countFile)."\n".
            'cat > '.escapeshellarg($logDirectory).'/import-"$count".sql'."\n".
            'if [ "$count" -eq 1 ]; then echo "simulated partial import failure" >&2; exit 1; fi'."\n".
            "exit 0\n");
        chmod($bin.'/mysqldump', 0755);
        chmod($bin.'/mysql', 0755);

        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'Zazu EMP',
            'format_version' => 1,
            'database_driver' => 'mysql',
            'contents' => ['database', 'private_storage'],
        ], JSON_THROW_ON_ERROR));
        $zip->addFromString('database.sql', '-- candidate restore SQL');
        $zip->addFromString('storage/private/restored-probe.txt', 'candidate private data');
        $zip->close();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql' => array_merge($originalMysql, [
                'host' => '127.0.0.1',
                'port' => 3306,
                'username' => 'zazu-test',
                'password' => 'not-used',
                'database' => 'zazu_test',
            ]),
        ]);
        putenv('PATH='.$bin.PATH_SEPARATOR.$originalPath);

        try {
            $command = $this->artisan('zazu:restore', [
                'archive' => $archive,
                '--force' => true,
            ]);
            $exitCode = $command->run();

            $this->assertSame(1, $exitCode);
            $this->assertSame('2', trim(File::get($countFile)), 'MySQL should be invoked once for candidate import and once for rollback.');
            $this->assertStringContainsString('-- candidate restore SQL', File::get($logDirectory.'/import-1.sql'));
            $this->assertStringContainsString('-- pre-restore snapshot marker', File::get($logDirectory.'/import-2.sql'));
            $this->assertSame('original private data', File::get($privateProbe), 'Private storage must roll back when the database import fails.');
            $this->assertFileDoesNotExist(storage_path('app/private/restored-probe.txt'));
        } finally {
            putenv('PATH='.$originalPath);
            config([
                'database.default' => $originalDefault,
                'database.connections.mysql' => $originalMysql,
            ]);
            File::delete($archive);
            File::delete($privateProbe);
            File::deleteDirectory($root);
        }
    }


}
