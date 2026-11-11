<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use ZipArchive;

class ZazuBackupRecoveryTest extends TestCase
{
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

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
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
            File::put($bin.'/mysql', "#!/bin/sh\n".
                'printf "%s\\n" called >> '.escapeshellarg($countFile)."\n".
                "cat >/dev/null\n".
                "echo 'simulated rollback failure' >&2\n".
                "exit 1\n");
            chmod($bin.'/mysql', 0755);

            $exitCode = Artisan::call('zazu:restore', [
                'archive' => $archive,
                '--force' => true,
            ]);
            $rollbackFailureOutput = Artisan::output();

            $this->assertSame(1, $exitCode, $rollbackFailureOutput);
            $this->assertStringContainsString('automatic rollback was incomplete', $rollbackFailureOutput);
            $this->assertSame(2, substr_count(File::get($countFile), 'called'), 'The fake MySQL executable must run for both the failed candidate import and rollback attempt.');
            $this->assertStringContainsString('simulated rollback failure', $rollbackFailureOutput);
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
}
