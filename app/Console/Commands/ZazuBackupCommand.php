<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

class ZazuBackupCommand extends Command
{
    private const FORMAT_VERSION = 1;
    private const MAX_PRIVATE_FILES = 50000;

    protected $signature = 'zazu:backup {--output= : Destination directory for the backup archive}';
    protected $description = 'Create a portable Zazu database and private-storage backup';

    public function handle(): int
    {
        if (!class_exists(ZipArchive::class)) {
            $this->error('The PHP Zip extension is required for Zazu backups.');
            return self::FAILURE;
        }

        $directory = $this->prepareDestination();
        if ($directory === null) {
            return self::FAILURE;
        }

        $stamp = now()->format('Ymd_His').'_' . Str::lower((string) Str::ulid());
        $work = storage_path('app/.zazu-backup-'.$stamp);
        $archive = $directory.DIRECTORY_SEPARATOR.'zazu-backup-'.$stamp.'.zip';
        $temporaryArchive = $archive.'.tmp';

        File::ensureDirectoryExists($work);

        try {
            $driver = config('database.default');
            $manifest = $this->buildManifest($driver, $stamp);

            if (!$this->snapshotDatabase($driver, $work)) {
                return self::FAILURE;
            }

            if (!$this->backupPrivateStorage($work)) {
                return self::FAILURE;
            }

            File::put($work.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            if (!$this->createArchive($work, $temporaryArchive)) {
                return self::FAILURE;
            }

            if (!rename($temporaryArchive, $archive)) {
                $this->error('Could not publish the completed backup archive.');
                return self::FAILURE;
            }

            $this->info('Backup created: '.$archive);
            return self::SUCCESS;
        } finally {
            File::delete($temporaryArchive);
            File::deleteDirectory($work);
        }
    }

    private function prepareDestination(): ?string
    {
        $directory = $this->option('output') ?: storage_path('app/zazu-backups');
        File::ensureDirectoryExists($directory);

        $directory = realpath($directory);
        $privateRoot = realpath(storage_path('app/private')) ?: storage_path('app/private');

        if ($directory === false) {
            $this->error('The backup destination is unavailable.');
            return null;
        }

        if (is_dir($privateRoot) && ($directory === $privateRoot || str_starts_with($directory, $privateRoot.DIRECTORY_SEPARATOR))) {
            $this->error('The backup destination cannot be inside private storage.');
            return null;
        }

        return $directory;
    }

    private function buildManifest(string $driver, string $stamp): array
    {
        return [
            'application' => 'Zazu EMP',
            'format_version' => self::FORMAT_VERSION,
            'backup_id' => $stamp,
            'created_at' => now()->toIso8601String(),
            'database_driver' => $driver,
            'contents' => ['database', 'private_storage'],
        ];
    }

    private function snapshotDatabase(string $driver, string $work): bool
    {
        if ($driver === 'sqlite') {
            return $this->snapshotSqlite($work);
        }

        if ($driver === 'mysql') {
            return $this->snapshotMysql($work);
        }

        $this->error('Backup currently supports SQLite and MySQL installations.');
        return false;
    }

    private function snapshotSqlite(string $work): bool
    {
        $source = config('database.connections.sqlite.database');
        $target = $work.'/database.sqlite';

        if ($source !== ':memory:' && (!$source || !is_file($source))) {
            $this->error('The SQLite database is unavailable.');
            return false;
        }

        $quotedTarget = str_replace("'", "''", $target);

        try {
            DB::connection('sqlite')->statement("VACUUM INTO '".$quotedTarget."'");
        } catch (\Throwable $exception) {
            $this->error('Could not snapshot the SQLite database: '.$exception->getMessage());
            return false;
        }

        if (!is_file($target) || filesize($target) === 0) {
            $this->error('The SQLite database snapshot was not created.');
            return false;
        }

        return true;
    }

    private function snapshotMysql(string $work): bool
    {
        $connection = config('database.connections.mysql');
        $process = new Process([
            'mysqldump',
            '--single-transaction',
            '--routines',
            '--triggers',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            $connection['database'],
        ], null, array_filter(['MYSQL_PWD' => $connection['password'] ?? null]));

        $process->setTimeout(300);
        $result = $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Database dump failed: '.$process->getErrorOutput());
            return false;
        }

        File::put($work.'/database.sql', $result);
        return true;
    }

    private function backupPrivateStorage(string $work): bool
    {
        $privateRoot = realpath(storage_path('app/private')) ?: storage_path('app/private');

        if (!is_dir($privateRoot)) {
            return true;
        }

        $files = File::allFiles($privateRoot);
        if (count($files) > self::MAX_PRIVATE_FILES) {
            $this->error('Private storage contains too many files for one backup.');
            return false;
        }

        foreach ($files as $file) {
            if ($file->isLink()) {
                $this->error('Private storage contains a symbolic link; backup aborted for safety.');
                return false;
            }
        }

        File::copyDirectory($privateRoot, $work.'/storage/private');
        return true;
    }

    private function createArchive(string $work, string $temporaryArchive): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($temporaryArchive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Could not create backup archive.');
            return false;
        }

        foreach (File::allFiles($work) as $file) {
            $entryName = str_replace('\\', '/', $file->getRelativePathname());

            if (!$zip->addFile($file->getPathname(), $entryName)) {
                $zip->close();
                $this->error('Could not add a file to the backup archive.');
                return false;
            }
        }

        if (!$zip->close() || !is_file($temporaryArchive) || filesize($temporaryArchive) === 0) {
            $this->error('Could not finalize the backup archive.');
            return false;
        }

        return true;
    }
}
