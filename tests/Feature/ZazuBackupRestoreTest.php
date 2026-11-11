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
            $this->assertVersionTwoArchive($zip);

            $legacyDatabasePayload = (string) $zip->getFromName('database.sqlite');
            $legacyPrivatePayload = (string) $zip->getFromName('storage/private/zazu-roundtrip/media-probe.txt');
            $zip->close();

            $legacyArchive = $this->createLegacyV1Archive($output, $legacyDatabasePayload, $legacyPrivatePayload);

            DB::table('backup_probe')->update(['value' => 'changed after backup']);
            File::delete($privateProbe);
            $this->createSqliteSidecarFixtures($database);

            $this->artisan('zazu:restore', [
                'archive' => $archive,
                '--force' => true,
            ])->assertExitCode(0);

            DB::purge('sqlite');

            $this->assertNoSqliteRollbackArtifacts();

            $this->assertSame(
                'before backup',
                DB::connection('sqlite')->table('backup_probe')->value('value')
            );
            $this->assertFileExists($privateProbe);
            $this->assertSame('private media before backup', File::get($privateProbe));

            DB::table('backup_probe')->update(['value' => 'changed before legacy restore']);
            File::delete($privateProbe);

            $this->artisan('zazu:restore', [
                'archive' => $legacyArchive,
                '--force' => true,
            ])->assertExitCode(0);

            DB::purge('sqlite');
            $this->assertSame(
                'before backup',
                DB::connection('sqlite')->table('backup_probe')->value('value')
            );
            $this->assertSame('private media before backup', File::get($privateProbe));
        } finally {
            DB::purge('sqlite');
            $this->cleanupSqliteArtifacts($database);
            File::deleteDirectory($output);
            File::delete($privateProbe);
            config([
                'database.default' => $originalDefault,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
            DB::purge('sqlite');
        }
    }




    public function test_sqlite_backup_includes_committed_wal_records(): void
    {
        if (!class_exists(ZipArchive::class) || !in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PHP Zip and SQLite extensions are required for backup tests.');
        }

        $database = storage_path('app/zazu-wal-backup.sqlite');
        $output = storage_path('app/zazu-wal-backup-archives');
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');

        File::delete($database);
        File::delete($database.'-wal');
        File::delete($database.'-shm');
        File::deleteDirectory($output);
        File::ensureDirectoryExists(dirname($database));
        File::put($database, '');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
        ]);

        try {
            DB::purge('sqlite');
            DB::connection('sqlite')->statement('PRAGMA journal_mode=WAL');
            DB::connection('sqlite')->getSchemaBuilder()->create('wal_backup_probe', function ($table) {
                $table->id();
                $table->string('value');
            });
            DB::table('wal_backup_probe')->insert(['value' => 'committed in WAL']);

            $this->assertFileExists($database.'-wal');
            $this->assertGreaterThan(32, filesize($database.'-wal'), 'The fixture must contain committed WAL frames before backup.');

            $this->artisan('zazu:backup', ['--output' => $output])
                ->assertExitCode(0);

            $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
            $this->assertCount(1, $archives);

            DB::table('wal_backup_probe')->update(['value' => 'changed after backup']);

            $this->artisan('zazu:restore', [
                'archive' => $archives[0],
                '--force' => true,
            ])->assertExitCode(0);

            DB::purge('sqlite');
            $this->assertSame(
                'committed in WAL',
                DB::connection('sqlite')->table('wal_backup_probe')->value('value'),
                'The backup must include committed rows that had not been checkpointed into the main SQLite file.',
            );
        } finally {
            DB::purge('sqlite');
            foreach ([$database, $database.'-wal', $database.'-shm'] as $file) {
                File::delete($file);
            }
            File::deleteDirectory($output);
            config([
                'database.default' => $originalDefault,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
            DB::purge('sqlite');
        }
    }

    public function test_mysql_backup_archives_the_dump_output_not_the_process_exit_code(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup tests.');
        }
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('The fake mysqldump executable fixture requires a POSIX shell.');
        }

        $root = storage_path('app/zazu-mysql-backup-test');
        $bin = $root.'/bin';
        $output = $root.'/backups';
        $dumpScript = $bin.'/mysqldump';
        $expectedDump = "-- Zazu MySQL dump fixture\nCREATE TABLE backup_probe (id INT);";
        $originalDefault = config('database.default');
        $originalMysql = config('database.connections.mysql');
        $originalPath = getenv('PATH') ?: '';
        $originalServerPath = $_SERVER['PATH'] ?? null;
        $originalEnvPath = $_ENV['PATH'] ?? null;

        File::deleteDirectory($root);
        File::ensureDirectoryExists($bin);
        File::put($dumpScript, "#!/bin/sh\nprintf '%b' '".str_replace("\n", "\\n", $expectedDump)."'\n");
        chmod($dumpScript, 0755);

        putenv('PATH='.$bin.PATH_SEPARATOR.$originalPath);
        $_SERVER['PATH'] = $bin.PATH_SEPARATOR.$originalPath;
        $_ENV['PATH'] = $bin.PATH_SEPARATOR.$originalPath;

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.username' => 'zazu-test',
            'database.connections.mysql.password' => 'test-password',
            'database.connections.mysql.database' => 'zazu_test',
        ]);

        try {
            $this->artisan('zazu:backup', ['--output' => $output])
                ->assertExitCode(0);

            $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
            $this->assertCount(1, $archives);

            $zip = new ZipArchive();
            $this->assertSame(true, $zip->open($archives[0]));
            $this->assertSame($expectedDump, $zip->getFromName('database.sql'));
            $this->assertNotSame('0', trim((string) $zip->getFromName('database.sql')));
            $zip->close();

            File::delete($archives[0]);
            File::put($dumpScript, "#!/bin/sh\nexit 0\n");

            $this->artisan('zazu:backup', ['--output' => $output])
                ->assertExitCode(1);
            $this->assertCount(
                0,
                File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip'),
                'A successful mysqldump process with empty stdout must not publish a backup.',
            );
        } finally {
            config([
                'database.default' => $originalDefault,
                'database.connections.mysql' => $originalMysql,
            ]);
            putenv('PATH='.$originalPath);
            if ($originalServerPath === null) {
                unset($_SERVER['PATH']);
            } else {
                $_SERVER['PATH'] = $originalServerPath;
            }
            if ($originalEnvPath === null) {
                unset($_ENV['PATH']);
            } else {
                $_ENV['PATH'] = $originalEnvPath;
            }
            File::deleteDirectory($root);
        }
    }

    public function test_backup_fails_without_publishing_an_archive_when_private_storage_copy_fails(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $database = storage_path('app/zazu-copy-failure.sqlite');
        $output = storage_path('app/zazu-copy-failure-backups');
        $privateProbe = storage_path('app/private/zazu-copy-failure/probe.txt');
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');

        File::ensureDirectoryExists(dirname($database));
        File::delete($database);
        File::put($database, '');
        File::deleteDirectory($output);
        File::ensureDirectoryExists(dirname($privateProbe));
        File::put($privateProbe, 'private media must not be silently omitted');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
        ]);

        try {
            DB::purge('sqlite');
            DB::connection('sqlite')->getSchemaBuilder()->create('backup_failure_probe', function ($table) {
                $table->id();
                $table->string('value');
            });
            DB::table('backup_failure_probe')->insert(['value' => 'backup candidate']);

            File::partialMock()
                ->shouldReceive('copyDirectory')
                ->once()
                ->andReturn(false);

            $this->artisan('zazu:backup', ['--output' => $output])
                ->assertExitCode(1);

            $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
            $this->assertCount(0, $archives);
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

    public function test_restore_rejects_a_version_two_backup_without_its_integrity_manifest(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $archive = storage_path('app/zazu-missing-checksums-test.zip');
        $probe = storage_path('app/private/missing-checksums-probe.txt');
        File::ensureDirectoryExists(dirname($archive));
        File::ensureDirectoryExists(dirname($probe));
        File::put($probe, 'must survive missing manifest');

        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'Zazu EMP',
            'format_version' => 2,
            'database_driver' => 'sqlite',
        ], JSON_THROW_ON_ERROR));
        $zip->addFromString('database.sqlite', 'candidate database bytes');
        $zip->close();

        try {
            $this->artisan('zazu:restore', ['archive' => $archive, '--force' => true])
                ->assertExitCode(1);

            $this->assertSame('must survive missing manifest', File::get($probe));
        } finally {
            File::delete($archive);
            File::delete($probe);
        }
    }

    public function test_restore_rejects_a_private_file_that_fails_the_backup_checksum(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $archive = storage_path('app/zazu-checksum-mismatch-test.zip');
        $probe = storage_path('app/private/checksum-probe.txt');
        $databasePayload = 'candidate database bytes';
        $expectedPrivatePayload = 'expected private media';
        $actualPrivatePayload = 'tampered private media';

        File::ensureDirectoryExists(dirname($archive));
        File::ensureDirectoryExists(dirname($probe));
        File::put($probe, 'must survive checksum rejection');

        $manifest = [
            'application' => 'Zazu EMP',
            'format_version' => 2,
            'database_driver' => 'sqlite',
            'checksums' => [
                'database.sqlite' => hash('sha256', $databasePayload),
                'storage/private/checksum-probe.txt' => hash('sha256', $expectedPrivatePayload),
            ],
        ];

        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        $zip->addFromString('database.sqlite', $databasePayload);
        $zip->addFromString('storage/private/checksum-probe.txt', $actualPrivatePayload);
        $zip->close();

        try {
            $this->artisan('zazu:restore', ['archive' => $archive, '--force' => true])
                ->assertExitCode(1);

            $this->assertSame('must survive checksum rejection', File::get($probe));
        } finally {
            File::delete($archive);
            File::delete($probe);
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
        $originalServerPath = $_SERVER['PATH'] ?? null;
        $originalEnvPath = $_ENV['PATH'] ?? null;
        $originalDefault = config('database.default');
        $originalMysql = config('database.connections.mysql');

        File::ensureDirectoryExists($bin);
        File::ensureDirectoryExists($logDirectory);
        File::ensureDirectoryExists(dirname($privateProbe));
        File::put($privateProbe, 'original private data');

        File::put($bin.'/mysqldump', "#!/bin/sh\nprintf '%s\\n' '-- pre-restore snapshot marker'\n");
        File::put($bin.'/mysql', "#!/bin/sh\n".
            'count=0'."\n".
            '[ -f '.escapeshellarg($countFile).' ] && count=$(cat '.escapeshellarg($countFile).")\n".
            'count=$((count + 1))'."\n".
            'printf "%s" "$count" > '.escapeshellarg($countFile)."\n".
            'cat > '.escapeshellarg($logDirectory).'/import-"$count".sql'."\n".
            'if grep -q "candidate restore SQL" '.escapeshellarg($logDirectory).'/import-"$count".sql; then echo "simulated partial import failure" >&2; exit 1; fi'."\n".
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
        $_SERVER['PATH'] = $bin.PATH_SEPARATOR.$originalPath;
        $_ENV['PATH'] = $bin.PATH_SEPARATOR.$originalPath;

        try {
            $exitCode = Artisan::call('zazu:restore', [
                'archive' => $archive,
                '--force' => true,
            ]);
            $output = Artisan::output();

            $this->assertSame(1, $exitCode, $output);
            $this->assertSame('2', trim(File::get($countFile)), 'MySQL should be invoked once for candidate import and once for rollback.');
            $this->assertStringContainsString('-- candidate restore SQL', File::get($logDirectory.'/import-1.sql'));
            $this->assertStringContainsString('-- pre-restore snapshot marker', File::get($logDirectory.'/import-2.sql'));
            $this->assertSame('original private data', File::get($privateProbe), 'Private storage must roll back when the database import fails.');
            $this->assertFileDoesNotExist(storage_path('app/private/restored-probe.txt'));

            // A failed rollback must retain the only pre-restore SQL snapshot for
            // operator recovery instead of deleting the temporary restore workspace.
            File::delete($countFile);
            File::put($bin.'/mysql', "#!/bin/sh\\ncat >/dev/null\\necho 'simulated rollback failure' >&2\\nexit 1\\n");
            chmod($bin.'/mysql', 0755);

            $exitCode = Artisan::call('zazu:restore', [
                'archive' => $archive,
                '--force' => true,
            ]);
            $rollbackFailureOutput = Artisan::output();

            $this->assertSame(1, $exitCode, $rollbackFailureOutput);
            $this->assertStringContainsString('automatic rollback was incomplete', $rollbackFailureOutput);
            $this->assertStringContainsString('Pre-restore MySQL snapshot preserved at:', $rollbackFailureOutput);
            $this->assertMatchesRegularExpression('/Pre-restore MySQL snapshot preserved at: (.+)/', $rollbackFailureOutput);
            preg_match('/Pre-restore MySQL snapshot preserved at: (.+)/', $rollbackFailureOutput, $snapshotMatch);
            $snapshotPath = trim($snapshotMatch[1] ?? '');
            $this->assertFileExists($snapshotPath);
            $this->assertSame('-- pre-restore snapshot marker', rtrim(File::get($snapshotPath), "\r\n"));
            $this->assertSame('original private data', File::get($privateProbe), 'Private storage must still roll back when the database rollback fails.');
            File::deleteDirectory(dirname($snapshotPath));
        } finally {
            putenv('PATH='.$originalPath);
            if ($originalEnvPath === null) {
                unset($_ENV['PATH']);
            } else {
                $_ENV['PATH'] = $originalEnvPath;
            }
            if ($originalServerPath === null) {
                unset($_SERVER['PATH']);
            } else {
                $_SERVER['PATH'] = $originalServerPath;
            }
            config([
                'database.default' => $originalDefault,
                'database.connections.mysql' => $originalMysql,
            ]);
            File::delete($archive);
            File::delete($privateProbe);
            File::deleteDirectory($root);
        }
    }



    public function test_restore_restores_original_sqlite_memory_configuration_after_completion(): void
    {
        if (!class_exists(ZipArchive::class) || !in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PHP Zip and SQLite extensions are required for restore tests.');
        }

        $root = storage_path('app/zazu-memory-restore-test');
        $database = $root.'/source.sqlite';
        $archive = $root.'/memory-restore.zip';
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');

        File::deleteDirectory($root);
        File::ensureDirectoryExists($root);
        $pdo = new \PDO('sqlite:'.$database);
        $pdo->exec('CREATE TABLE memory_restore_probe (id INTEGER PRIMARY KEY, value TEXT)');
        $pdo->exec("INSERT INTO memory_restore_probe (value) VALUES ('restored')");
        $pdo = null;

        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'Zazu EMP',
            'format_version' => 1,
            'database_driver' => 'sqlite',
            'contents' => ['database'],
        ], JSON_THROW_ON_ERROR));
        $zip->addFile($database, 'database.sqlite');
        $zip->close();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        try {
            $this->artisan('zazu:restore', [
                'archive' => $archive,
                '--force' => true,
            ])->assertExitCode(0);

            $this->assertSame(':memory:', config('database.connections.sqlite.database'));
            $this->assertSame([], File::glob(storage_path('app/.zazu-test-restore-*.sqlite')), 'Temporary file-backed targets used for in-memory restore must not leak into later commands or tests.');
        } finally {
            DB::purge('sqlite');
            config([
                'database.default' => $originalDefault,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
            DB::purge('sqlite');
            File::deleteDirectory($root);
            foreach (File::glob(storage_path('app/.zazu-test-restore-*.sqlite')) as $temporaryDatabase) {
                File::delete($temporaryDatabase);
            }
        }
    }

    private function cleanupSqliteArtifacts(string $database): void
    {
        foreach ([$database, $database.'-wal', $database.'-shm'] as $path) {
            File::delete($path);
        }
        foreach (File::glob(storage_path('app/.zazu-database-rollback-*')) as $rollbackFile) {
            File::delete($rollbackFile);
        }
    }

    private function createSqliteSidecarFixtures(string $database): void
    {
        DB::disconnect('sqlite');
        DB::purge('sqlite');
        File::put($database.'-wal', 'pre-restore WAL sidecar');
        File::put($database.'-shm', 'pre-restore shared-memory sidecar');
    }

    private function assertNoSqliteRollbackArtifacts(): void
    {
        $this->assertSame([], File::glob(storage_path('app/.zazu-database-rollback-*')), 'Successful restore must clean the old database and WAL/SHM rollback artifacts.');
    }

    private function assertVersionTwoArchive(ZipArchive $zip): void
    {
        $this->assertNotFalse($zip->locateName('manifest.json'));
        $this->assertNotFalse($zip->locateName('database.sqlite'));
        $this->assertNotFalse($zip->locateName('storage/private/zazu-roundtrip/media-probe.txt'));

        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Zazu EMP', $manifest['application'] ?? null);
        $this->assertSame(2, $manifest['format_version'] ?? null);
        $this->assertSame('sqlite', $manifest['database_driver'] ?? null);
        $this->assertSame(['database', 'private_storage'], $manifest['contents'] ?? null);
        $this->assertNotEmpty($manifest['backup_id'] ?? null);
        $this->assertNotEmpty($manifest['created_at'] ?? null);
        $this->assertSame(
            hash('sha256', (string) $zip->getFromName('database.sqlite')),
            $manifest['checksums']['database.sqlite'] ?? null
        );
        $this->assertSame(
            hash('sha256', 'private media before backup'),
            $manifest['checksums']['storage/private/zazu-roundtrip/media-probe.txt'] ?? null
        );

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $this->assertStringNotContainsString('\\', (string) $zip->getNameIndex($index));
        }
    }

    private function createLegacyV1Archive(string $output, string $databasePayload, string $privatePayload): string
    {
        $archive = $output.DIRECTORY_SEPARATOR.'zazu-legacy-v1.zip';
        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'Zazu EMP',
            'format_version' => 1,
            'backup_id' => 'legacy-v1-test',
            'created_at' => now()->toIso8601String(),
            'database_driver' => 'sqlite',
            'contents' => ['database', 'private_storage'],
        ], JSON_THROW_ON_ERROR));
        $zip->addFromString('database.sqlite', $databasePayload);
        $zip->addFromString('storage/private/zazu-roundtrip/media-probe.txt', $privatePayload);
        $zip->close();

        return $archive;
    }
}
