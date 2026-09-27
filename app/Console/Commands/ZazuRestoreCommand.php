<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class ZazuRestoreCommand extends Command
{
    protected $signature = 'zazu:restore {archive : Path to a Zazu backup ZIP} {--force : Skip the interactive confirmation}';
    protected $description = 'Restore a Zazu database and private storage from a verified backup archive';

    public function handle(): int
    {
        if (!class_exists(ZipArchive::class)) {
            $this->error('The PHP Zip extension is required for Zazu restores.');
            return self::FAILURE;
        }

        $archive = $this->argument('archive');

        if (!is_file($archive)) {
            $this->error('Backup archive was not found.');
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('This replaces the current Zazu database and private files. Continue?')) {
            return self::SUCCESS;
        }

        $work = storage_path('app/.zazu-restore-'.now()->format('Ymd_His'));
        File::ensureDirectoryExists($work);

        try {
            $zip = new ZipArchive();
            if ($zip->open($archive) !== true || $zip->locateName('manifest.json') === false) {
                $this->error('The archive is not a valid Zazu backup.');
                return self::FAILURE;
            }

            // Validate every archive member before extraction to prevent path traversal.
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);

                if ($name === false || str_contains($name, '\\0')) {
                    $zip->close();
                    $this->error('The backup archive contains an invalid entry.');
                    return self::FAILURE;
                }

                $normalized = str_replace('\\', '/', $name);

                if (
                    str_starts_with($normalized, '/')
                    || preg_match('/^[A-Za-z]:\\//', $normalized)
                    || in_array('..', explode('/', trim($normalized, '/')), true)
                ) {
                    $zip->close();
                    $this->error('The backup archive contains an unsafe path.');
                    return self::FAILURE;
                }
            }

            if (!$zip->extractTo($work)) {
                $zip->close();
                $this->error('Could not extract the backup archive.');
                return self::FAILURE;
            }

            $zip->close();

            $manifestPath = $work.'/manifest.json';
            $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);

            if (($manifest['application'] ?? null) !== 'Zazu EMP') {
                $this->error('The backup manifest is not for Zazu EMP.');
                return self::FAILURE;
            }

            $driver = config('database.default');

            if ($driver === 'sqlite') {
                $source = $work.'/database.sqlite';
                $target = config('database.connections.sqlite.database');

                if (!$target || $target === ':memory:' || !is_file($source)) {
                    $this->error('SQLite restore target or backup database is unavailable.');
                    return self::FAILURE;
                }

                DB::disconnect();
                if (!copy($source, $target)) {
                    $this->error('Could not restore the SQLite database.');
                    return self::FAILURE;
                }
            } elseif ($driver === 'mysql') {
                $connection = config('database.connections.mysql');
                $sql = $work.'/database.sql';

                if (!is_file($sql)) {
                    $this->error('MySQL dump is missing from the backup.');
                    return self::FAILURE;
                }

                $process = new Process([
                    'mysql',
                    '--host='.$connection['host'],
                    '--port='.$connection['port'],
                    '--user='.$connection['username'],
                    $connection['database'],
                ], null, array_filter(['MYSQL_PWD' => $connection['password'] ?? null]));
                $process->setTimeout(300);
                $process->setInput(File::get($sql));
                $process->run();

                if (!$process->isSuccessful()) {
                    $this->error('Database restore failed: '.$process->getErrorOutput());
                    return self::FAILURE;
                }
            } else {
                $this->error('Restore currently supports SQLite and MySQL installations.');
                return self::FAILURE;
            }

            $storedPrivate = $work.'/storage/private';
            if (is_dir($storedPrivate)) {
                File::deleteDirectory(storage_path('app/private'));
                File::copyDirectory($storedPrivate, storage_path('app/private'));
            }

            $this->info('Zazu restore completed from '.$archive);
            return self::SUCCESS;
        } finally {
            File::deleteDirectory($work);
        }
    }
}
