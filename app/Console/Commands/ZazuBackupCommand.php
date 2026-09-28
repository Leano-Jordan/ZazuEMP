<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

class ZazuBackupCommand extends Command
{
    protected $signature = 'zazu:backup {--output= : Destination directory for the backup archive}';
    protected $description = 'Create a portable Zazu database and private-storage backup';

    public function handle(): int
    {
        if (!class_exists(ZipArchive::class)) {
            $this->error('The PHP Zip extension is required for Zazu backups.');
            return self::FAILURE;
        }

        $directory = $this->option('output') ?: storage_path('app/zazu-backups');
        File::ensureDirectoryExists($directory);

        $stamp = now()->format('Ymd_His').'_'.Str::lower((string) Str::ulid());
        $work = storage_path('app/.zazu-backup-'.$stamp);
        File::ensureDirectoryExists($work);

        try {
            $driver = config('database.default');
            $manifest = [
                'application' => 'Zazu EMP',
                'created_at' => now()->toIso8601String(),
                'database_driver' => $driver,
            ];

            if ($driver === 'sqlite') {
                $source = config('database.connections.sqlite.database');
                if (!$source || $source === ':memory:' || !is_file($source)) {
                    $this->error('SQLite database file is not available for backup.');
                    return self::FAILURE;
                }

                if (!copy($source, $work.'/database.sqlite')) {
                    $this->error('Could not copy the SQLite database.');
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

            $privateRoot = storage_path('app/private');
            // Backup archives live outside the private storage tree so the backup cannot copy itself recursively.
            if (is_dir($privateRoot)) {
                File::copyDirectory($privateRoot, $work.'/storage/private');
            }

            File::put($work.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            $archive = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'zazu-backup-'.$stamp.'.zip';
            $zip = new ZipArchive();
            if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $this->error('Could not create backup archive.');
                return self::FAILURE;
            }

            foreach (File::allFiles($work) as $file) {
                $zip->addFile($file->getPathname(), $file->getRelativePathname());
            }

            $zip->close();
            $this->info('Backup created: '.$archive);

            return self::SUCCESS;
        } finally {
            File::deleteDirectory($work);
        }
    }
}
