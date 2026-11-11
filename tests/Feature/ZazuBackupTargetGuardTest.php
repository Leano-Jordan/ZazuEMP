<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class ZazuBackupTargetGuardTest extends TestCase
{

    public function test_restore_rejects_a_symbolic_link_as_sqlite_target_before_mutating_data(): void
    {
        if (PHP_OS_FAMILY === 'Windows' || !function_exists('symlink')) {
            $this->markTestSkipped('This regression requires POSIX symbolic-link support.');
        }
        if (!class_exists(ZipArchive::class) || !in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PHP Zip and SQLite extensions are required for restore tests.');
        }

        $root = storage_path('app/zazu-symlink-target-restore-test');
        $candidate = $root.'/candidate.sqlite';
        $archive = $root.'/candidate.zip';
        $protected = $root.'/protected.sqlite';
        $targetLink = $root.'/configured-database.sqlite';
        $privateProbe = storage_path('app/private/zazu-symlink-target-probe.txt');
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');

        File::deleteDirectory($root);
        File::ensureDirectoryExists($root);
        File::ensureDirectoryExists(dirname($privateProbe));

        $protectedPdo = new \PDO('sqlite:'.$protected);
        $protectedPdo->exec('CREATE TABLE protected_probe (id INTEGER PRIMARY KEY, value TEXT)');
        $protectedPdo->exec("INSERT INTO protected_probe (value) VALUES ('must remain untouched')");
        $protectedPdo = null;

        $candidatePdo = new \PDO('sqlite:'.$candidate);
        $candidatePdo->exec('CREATE TABLE candidate_probe (id INTEGER PRIMARY KEY, value TEXT)');
        $candidatePdo->exec("INSERT INTO candidate_probe (value) VALUES ('candidate restore')");
        $candidatePdo = null;

        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'application' => 'Zazu EMP',
            'format_version' => 1,
            'database_driver' => 'sqlite',
            'contents' => ['database'],
        ], JSON_THROW_ON_ERROR));
        $zip->addFile($candidate, 'database.sqlite');
        $zip->close();

        $this->assertTrue(symlink($protected, $targetLink), 'The test must create the configured SQLite symlink.');
        File::put($privateProbe, 'original private data must survive');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $targetLink,
        ]);
        DB::purge('sqlite');

        try {
            $this->artisan('zazu:restore', [
                'archive' => $archive,
                '--force' => true,
            ])->assertExitCode(1);

            $this->assertTrue(is_link($targetLink), 'Restore must not replace or follow the configured symbolic link.');
            $this->assertSame(
                'must remain untouched',
                (new \PDO('sqlite:'.$protected))->query('SELECT value FROM protected_probe')->fetchColumn(),
            );
            $this->assertSame('original private data must survive', File::get($privateProbe));
        } finally {
            DB::purge('sqlite');
            config([
                'database.default' => $originalDefault,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
            DB::purge('sqlite');
            File::delete($privateProbe);
            File::deleteDirectory($root);
        }
    }


    public function test_restore_rejects_a_directory_as_sqlite_target_before_mutating_private_storage(): void
    {
        if (!class_exists(ZipArchive::class) || !in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PHP Zip and SQLite extensions are required for restore tests.');
        }

        $root = storage_path('app/zazu-directory-target-restore-test');
        $database = $root.'/candidate.sqlite';
        $archive = $root.'/candidate.zip';
        $targetDirectory = $root.'/configured-database-target';
        $privateProbe = storage_path('app/private/zazu-directory-target-probe.txt');
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');

        File::deleteDirectory($root);
        File::ensureDirectoryExists($targetDirectory);
        File::ensureDirectoryExists(dirname($privateProbe));
        File::put($privateProbe, 'original private data must survive');

        $pdo = new \PDO('sqlite:'.$database);
        $pdo->exec('CREATE TABLE restore_target_probe (id INTEGER PRIMARY KEY, value TEXT)');
        $pdo->exec("INSERT INTO restore_target_probe (value) VALUES ('candidate')");
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
            'database.connections.sqlite.database' => $targetDirectory,
        ]);
        DB::purge('sqlite');

        try {
            $this->artisan('zazu:restore', [
                'archive' => $archive,
                '--force' => true,
            ])->assertExitCode(1);

            $this->assertDirectoryExists($targetDirectory);
            $this->assertSame([], File::files($targetDirectory), 'Restore must not move the candidate database into a directory configured as the SQLite file.');
            $this->assertSame('original private data must survive', File::get($privateProbe));
        } finally {
            DB::purge('sqlite');
            config([
                'database.default' => $originalDefault,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
            DB::purge('sqlite');
            File::delete($privateProbe);
            File::deleteDirectory($root);
        }
    }
}
