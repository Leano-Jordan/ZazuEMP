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

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function handle(): int
    {
        if (!class_exists(ZipArchive::class)) {
            $this->error('The PHP Zip extension is required for Zazu backups.');
            return self::FAILURE;
        }

        $directory = $this->option('output') ?: storage_path('app/zazu-backups');
        File::ensureDirectoryExists($directory);

        $directory = realpath($directory);
        $privateRoot = realpath(storage_path('app/private')) ?: storage_path('app/private');

        if ($directory === false) {
            $this->error('The backup destination is unavailable.');
            return self::FAILURE;
        }

        if (is_dir($privateRoot) && ($directory === $privateRoot || str_starts_with($directory, $privateRoot.DIRECTORY_SEPARATOR))) {
            $this->error('The backup destination cannot be inside private storage.');
            return self::FAILURE;
        }

        $stamp = now()->format('Ymd_His').'_' . Str::lower((string) Str::ulid());
        $work = storage_path('app/.zazu-backup-'.$stamp);
        $archive = $directory.DIRECTORY_SEPARATOR.'zazu-backup-'.$stamp.'.zip';
        $temporaryArchive = $archive.'.tmp';

        File::ensureDirectoryExists($work);

        try {
            $driver = config('database.default');
            $manifest = [
                'application' => 'Zazu EMP',
                'format_version' => self::FORMAT_VERSION,
                'backup_id' => $stamp,
                'created_at' => now()->toIso8601String(),
                'database_driver' => $driver,
                'contents' => ['database', 'private_storage'],
            ];

            if ($driver === 'sqlite') {
                $source = config('database.connections.sqlite.database');
                $target = $work.'/database.sqlite';

                // VACUUM INTO creates a transactionally consistent SQLite snapshot.
                // A raw file copy is unsafe when WAL is active because committed data can
                // still live in the -wal sidecar. Keep one backup path for both real and
                // in-memory SQLite databases.
                if ($source !== ':memory:' && (!$source || !is_file($source))) {
                    $this->error('The SQLite database is unavailable.');
                    return self::FAILURE;
                }

                $quotedTarget = str_replace("'", "''", $target);
                try {
                    DB::connection('sqlite')->statement("VACUUM INTO '".$quotedTarget."'");
                } catch (\Throwable $exception) {
                    $this->error('Could not snapshot the SQLite database: '.$exception->getMessage());
                    return self::FAILURE;
                }

                if (!is_file($target) || filesize($target) === 0) {
                    $this->error('The SQLite database snapshot was not created.');
                    return self::FAILURE;
                }
            } elseif ($driver === 'mysql') {
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
                    return self::FAILURE;
                }

                File::put($work.'/database.sql', $result);
            } else {
                $this->error('Backup currently supports SQLite and MySQL installations.');
                return self::FAILURE;
            }

            if (is_dir($privateRoot)) {
                $files = File::allFiles($privateRoot);
                if (count($files) > self::MAX_PRIVATE_FILES) {
                    $this->error('Private storage contains too many files for one backup.');
                    return self::FAILURE;
                }

                foreach ($files as $file) {
                    if ($file->isLink()) {
                        $this->error('Private storage contains a symbolic link; backup aborted for safety.');
                        return self::FAILURE;
                    }
                }

                File::copyDirectory($privateRoot, $work.'/storage/private');
            }

            File::put($work.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            $zip = new ZipArchive();
            if ($zip->open($temporaryArchive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $this->error('Could not create backup archive.');
                return self::FAILURE;
            }

            foreach (File::allFiles($work) as $file) {
                $entryName = str_replace('\\', '/', $file->getRelativePathname());

                if (!$zip->addFile($file->getPathname(), $entryName)) {
                    $zip->close();
                    $this->error('Could not add a file to the backup archive.');
                    return self::FAILURE;
                }
            }

            if (!$zip->close() || !is_file($temporaryArchive) || filesize($temporaryArchive) === 0) {
                $this->error('Could not finalize the backup archive.');
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
}
