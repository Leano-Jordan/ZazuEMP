<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class ZazuPostgresBackupRestoreTest extends TestCase
{
    public function test_postgres_backup_reads_db_url_and_archives_dump_and_private_files(): void
    {
        if (!class_exists(ZipArchive::class) || PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('ZIP and a POSIX shell are required for PostgreSQL CLI integration fixtures.');
        }

        $root = storage_path('app/zazu-pg-backup-test-'.Str::ulid());
        $bin = $root.'/bin';
        $output = $root.'/backups';
        $envFile = $root.'/pg-env.txt';
        $privateProbe = storage_path('app/private/zazu-pg-backup-probe.txt');
        $originalPath = getenv('PATH') ?: '';
        $originalServerPath = $_SERVER['PATH'] ?? null;
        $originalEnvPath = $_ENV['PATH'] ?? null;
        $originalDefault = config('database.default');
        $originalPgsql = config('database.connections.pgsql');

        File::ensureDirectoryExists($bin);
        File::ensureDirectoryExists(dirname($privateProbe));
        File::put($privateProbe, 'private PostgreSQL backup payload');
        File::put($bin.'/pg_dump', "#!/bin/sh\nprintf '%s\n' '-- Zazu PostgreSQL dump fixture' 'CREATE TABLE backup_probe (id bigint);'\nprintf '%s|%s' \"$PGPASSWORD\" \"$PGSSLMODE\" > ".escapeshellarg($envFile)."\n");
        chmod($bin.'/pg_dump', 0755);

        putenv('PATH='.$bin.PATH_SEPARATOR.$originalPath);
        $_SERVER['PATH'] = $bin.PATH_SEPARATOR.$originalPath;
        $_ENV['PATH'] = $bin.PATH_SEPARATOR.$originalPath;
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql' => array_merge($originalPgsql, [
                'url' => 'postgresql://zazu%40owner:secret%2Fpass@127.0.0.1:5544/zazu_test?sslmode=require',
            ]),
        ]);

        try {
            $this->artisan('zazu:backup', ['--output' => $output])->assertExitCode(0);

            $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
            $this->assertCount(1, $archives);

            $zip = new ZipArchive();
            $this->assertSame(true, $zip->open($archives[0]));
            $this->assertSame(
                "-- Zazu PostgreSQL dump fixture\nCREATE TABLE backup_probe (id bigint);\n",
                $zip->getFromName('database.sql'),
            );
            $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame('pgsql', $manifest['database_driver']);
            $this->assertArrayHasKey('storage/private/zazu-pg-backup-probe.txt', $manifest['checksums']);
            $this->assertSame('secret/pass|require', File::get($envFile));
            $zip->close();
        } finally {
            config([
                'database.default' => $originalDefault,
                'database.connections.pgsql' => $originalPgsql,
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
            File::delete($privateProbe);
            File::deleteDirectory($root);
        }
    }

    public function test_postgres_restore_rolls_back_database_and_private_files_after_import_failure(): void
    {
        if (!class_exists(ZipArchive::class) || PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('ZIP and a POSIX shell are required for PostgreSQL CLI integration fixtures.');
        }

        $root = storage_path('app/zazu-pg-restore-test-'.Str::ulid());
        $bin = $root.'/bin';
        $archive = $root.'/candidate.zip';
        $countFile = $root.'/psql-count.txt';
        $logDirectory = $root.'/imports';
        $privateRoot = storage_path('app/private');
        $privateProbe = $privateRoot.'/zazu-pg-original-probe.txt';
        $originalPath = getenv('PATH') ?: '';
        $originalServerPath = $_SERVER['PATH'] ?? null;
        $originalEnvPath = $_ENV['PATH'] ?? null;
        $originalDefault = config('database.default');
        $originalPgsql = config('database.connections.pgsql');

        File::ensureDirectoryExists($bin);
        File::ensureDirectoryExists($logDirectory);
        File::ensureDirectoryExists($privateRoot);
        File::put($privateProbe, 'original private data');
        File::put($bin.'/pg_dump', "#!/bin/sh\nprintf '%s\n' '-- pre-restore PostgreSQL snapshot marker'\n");
        File::put($bin.'/psql', "#!/bin/sh\n".
            'count=0'."\n".
            '[ -f '.escapeshellarg($countFile).' ] && count=$(cat '.escapeshellarg($countFile).")\n".
            'count=$((count + 1))'."\n".
            'printf "%s" "$count" > '.escapeshellarg($countFile)."\n".
            'cat > '.escapeshellarg($logDirectory).'/import-"$count".sql'."\n".
            'if [ "$count" -eq 1 ] && grep -q "candidate restore SQL" '.escapeshellarg($logDirectory).'/import-"$count".sql; then echo "simulated PostgreSQL import failure" >&2; exit 1; fi'."\n".
            "exit 0\n");
        chmod($bin.'/pg_dump', 0755);
        chmod($bin.'/psql', 0755);

        $databaseDump = '-- candidate restore SQL';
        $privateDump = 'candidate private data';
        $checksums = [
            'database.sql' => hash('sha256', $databaseDump),
            'storage/private/restored-probe.txt' => hash('sha256', $privateDump),
        ];
        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'Zazu EMP',
            'format_version' => 2,
            'database_driver' => 'pgsql',
            'contents' => ['database', 'private_storage'],
            'checksums' => $checksums,
        ], JSON_THROW_ON_ERROR));
        $zip->addFromString('database.sql', $databaseDump);
        $zip->addFromString('storage/private/restored-probe.txt', $privateDump);
        $zip->close();

        putenv('PATH='.$bin.PATH_SEPARATOR.$originalPath);
        $_SERVER['PATH'] = $bin.PATH_SEPARATOR.$originalPath;
        $_ENV['PATH'] = $bin.PATH_SEPARATOR.$originalPath;
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql' => array_merge($originalPgsql, [
                'url' => null,
                'host' => '127.0.0.1',
                'port' => '5432',
                'database' => 'zazu_test',
                'username' => 'zazu',
                'password' => 'test-password',
                'sslmode' => 'disable',
            ]),
        ]);

        try {
            $this->artisan('zazu:restore', [
                'archive' => $archive,
                '--force' => true,
            ])->assertExitCode(1);

            $this->assertSame('2', trim(File::get($countFile)));
            $this->assertSame($databaseDump, File::get($logDirectory.'/import-1.sql'));
            $this->assertSame('-- pre-restore PostgreSQL snapshot marker', trim(File::get($logDirectory.'/import-2.sql')));
            $this->assertSame('original private data', File::get($privateProbe));
            $this->assertFileDoesNotExist($privateRoot.'/restored-probe.txt');
        } finally {
            config([
                'database.default' => $originalDefault,
                'database.connections.pgsql' => $originalPgsql,
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
            File::delete($privateProbe);
            File::deleteDirectory($root);
        }
    }
}
