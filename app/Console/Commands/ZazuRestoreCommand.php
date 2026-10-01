<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

class ZazuRestoreCommand extends Command
{
    private const FORMAT_VERSION = 1;
    private const MAX_ARCHIVE_ENTRIES = 50000;
    private const MAX_UNCOMPRESSED_BYTES = 2147483648;

    protected $signature = 'zazu:restore {archive : Path to a Zazu backup ZIP} {--force : Skip the interactive confirmation}';
    protected $description = 'Restore a Zazu database and private storage from a verified backup archive';

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function handle(): int
    {
        if (!class_exists(ZipArchive::class)) {
            $this->error('The PHP Zip extension is required for Zazu restores.');
            return self::FAILURE;
        }

        $archive = $this->argument('archive');

        if (!is_file($archive) || !is_readable($archive)) {
            $this->error('Backup archive was not found or cannot be read.');
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('This replaces the current Zazu database and private files. Continue?')) {
            return self::SUCCESS;
        }

        $work = storage_path('app/.zazu-restore-'.now()->format('Ymd_His').'_' . Str::lower((string) Str::ulid()));
        File::ensureDirectoryExists($work);

        $privateRollback = null;
        $privateActivated = false;
        $databaseRollback = null;

        try {
            $zip = new ZipArchive();
            if ($zip->open($archive, ZipArchive::RDONLY | ZipArchive::CHECKCONS) !== true) {
                $this->error('The backup archive could not be opened or verified.');
                return self::FAILURE;
            }

            if ($zip->numFiles < 1 || $zip->numFiles > self::MAX_ARCHIVE_ENTRIES) {
                $zip->close();
                $this->error('The backup archive contains an invalid number of entries.');
                return self::FAILURE;
            }

            $manifestIndex = $zip->locateName('manifest.json');
            if ($manifestIndex === false) {
                $zip->close();
                $this->error('The archive is not a valid Zazu backup.');
                return self::FAILURE;
            }

            $manifestContents = $zip->getFromIndex($manifestIndex);
            if ($manifestContents === false) {
                $zip->close();
                $this->error('The backup manifest could not be read.');
                return self::FAILURE;
            }

            try {
                $manifest = json_decode($manifestContents, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $zip->close();
                $this->error('The backup manifest is invalid.');
                return self::FAILURE;
            }

            if (($manifest['application'] ?? null) !== 'Zazu EMP') {
                $zip->close();
                $this->error('The backup manifest is not for Zazu EMP.');
                return self::FAILURE;
            }

            if (isset($manifest['format_version']) && (int) $manifest['format_version'] !== self::FORMAT_VERSION) {
                $zip->close();
                $this->error('The backup format version is not supported by this Zazu installation.');
                return self::FAILURE;
            }

            $driver = config('database.default');
            if (($manifest['database_driver'] ?? null) !== $driver) {
                $zip->close();
                $this->error('The backup database driver does not match the current Zazu database configuration.');
                return self::FAILURE;
            }

            $databaseEntry = $driver === 'sqlite' ? 'database.sqlite' : 'database.sql';
            $privateEntryPrefix = 'storage/private/';
            $hasPrivateStorage = false;
            $seen = [];
            $uncompressedBytes = 0;

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);
                $stat = $zip->statIndex($index);

                if ($name === false || $stat === false || str_contains($name, "\0")) {
                    $zip->close();
                    $this->error('The backup archive contains an invalid entry.');
                    return self::FAILURE;
                }

                if (str_contains($name, '\')) {
                    $zip->close();
                    $this->error('The backup archive contains an ambiguous path.');
                    return self::FAILURE;
                }

                $normalized = ltrim($name, '/');
                $parts = explode('/', $normalized);

                if (
                    $normalized !== $name
                    || $name === ''
                    || str_starts_with($name, '/')
                    || preg_match('/^[A-Za-z]:[\\\/]/', $name)
                    || in_array('..', $parts, true)
                    || (in_array('', $parts, true) && !str_ends_with($name, '/'))
                ) {
                    $zip->close();
                    $this->error('The backup archive contains an unsafe path.');
                    return self::FAILURE;
                }

                if (isset($seen[$normalized])) {
                    $zip->close();
                    $this->error('The backup archive contains duplicate entries.');
                    return self::FAILURE;
                }
                $seen[$normalized] = true;

                $size = (int) ($stat['size'] ?? 0);
                if ($size < 0 || $size > self::MAX_UNCOMPRESSED_BYTES || $uncompressedBytes > self::MAX_UNCOMPRESSED_BYTES - $size) {
                    $zip->close();
                    $this->error('The backup archive is too large to restore safely.');
                    return self::FAILURE;
                }
                $uncompressedBytes += $size;

                if ($name === 'database.sqlite' || $name === 'database.sql') {
                    if ($name !== $databaseEntry) {
                        $zip->close();
                        $this->error('The backup contains a database for the wrong driver.');
                        return self::FAILURE;
                    }
                } elseif ($name !== 'manifest.json' && !str_starts_with($name, $privateEntryPrefix)) {
                    $zip->close();
                    $this->error('The backup contains an unsupported file.');
                    return self::FAILURE;
                }

                if (str_starts_with($name, $privateEntryPrefix) && !str_ends_with($name, '/')) {
                    $hasPrivateStorage = true;
                }

                $opsys = 0;
                $attributes = 0;
                if (method_exists($zip, 'getExternalAttributesIndex') && $zip->getExternalAttributesIndex($index, $opsys, $attributes)) {
                    if ($opsys === ZipArchive::OPSYS_UNIX && (($attributes >> 16) & 0170000) === 0120000) {
                        $zip->close();
                        $this->error('The backup contains a symbolic link and cannot be restored safely.');
                        return self::FAILURE;
                    }
                }
            }

            if ($zip->locateName($databaseEntry) === false) {
                $zip->close();
                $this->error('The backup database is missing.');
                return self::FAILURE;
            }

            if (!$zip->extractTo($work)) {
                $zip->close();
                $this->error('Could not extract the verified backup archive.');
                return self::FAILURE;
            }

            $zip->close();

            $manifestPath = $work.'/manifest.json';
            if (!is_file($manifestPath)) {
                $this->error('The extracted backup manifest is missing.');
                return self::FAILURE;
            }

            $storedPrivate = $work.'/storage/private';
            $privateTarget = storage_path('app/private');

            // Preflight all target-specific requirements before mutating the installation.
            if ($driver === 'sqlite') {
                $source = $work.'/database.sqlite';
                $target = config('database.connections.sqlite.database');

                if (!$target || $target === ':memory:' || !is_file($source)) {
                    $this->error('SQLite restore target or backup database is unavailable.');
                    return self::FAILURE;
                }
            } elseif ($driver === 'mysql') {
                $sql = $work.'/database.sql';

                if (!is_file($sql)) {
                    $this->error('MySQL dump is missing from the backup.');
                    return self::FAILURE;
                }
            } else {
                $this->error('Restore currently supports SQLite and MySQL installations.');
                return self::FAILURE;
            }

            // Stage private storage before changing the database. Every mutation after this
            // point either completes or throws so the catch block can restore the originals.
            if ($hasPrivateStorage && is_dir($storedPrivate)) {
                $privateRollback = storage_path('app/.zazu-private-rollback-'.Str::ulid());

                if (is_dir($privateTarget) && !rename($privateTarget, $privateRollback)) {
                    throw new \RuntimeException('Could not stage the current private storage for safe replacement.');
                }

                if (!rename($storedPrivate, $privateTarget)) {
                    if (is_dir($privateRollback)) {
                        rename($privateRollback, $privateTarget);
                    }
                    throw new \RuntimeException('Could not activate the restored private storage.');
                }

                $privateActivated = true;
            }

            if ($driver === 'sqlite') {
                $source = $work.'/database.sqlite';
                $target = config('database.connections.sqlite.database');

                DB::disconnect();

                $databaseStage = storage_path('app/.zazu-database-restore-'.Str::ulid().'.sqlite');
                if (!copy($source, $databaseStage)) {
                    throw new \RuntimeException('Could not stage the SQLite database for restore.');
                }

                $databaseRollback = storage_path('app/.zazu-database-rollback-'.Str::ulid().'.sqlite');

                if (is_file($target) && !rename($target, $databaseRollback)) {
                    File::delete($databaseStage);
                    throw new \RuntimeException('Could not stage the current SQLite database for safe replacement.');
                }

                if (!rename($databaseStage, $target)) {
                    File::delete($databaseStage);
                    if (is_file($databaseRollback)) {
                        rename($databaseRollback, $target);
                    }
                    throw new \RuntimeException('Could not activate the restored SQLite database.');
                }
            } elseif ($driver === 'mysql') {
                $connection = config('database.connections.mysql');
                $sql = $work.'/database.sql';

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
                    throw new \RuntimeException('Database restore failed: '.$process->getErrorOutput());
                }
            }

            if ($databaseRollback !== null) {
                File::delete($databaseRollback);
                $databaseRollback = null;
            }

            if ($privateRollback !== null) {
                File::deleteDirectory($privateRollback);
                $privateRollback = null;
            }

            $this->info('Zazu restore completed from '.$archive);
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            if ($databaseRollback !== null && is_file($databaseRollback)) {
                $target = config('database.connections.sqlite.database');
                DB::disconnect();
                File::delete($target);
                rename($databaseRollback, $target);
            }

            if ($privateActivated) {
                File::deleteDirectory(storage_path('app/private'));
            }
            if ($privateRollback !== null && is_dir($privateRollback)) {
                rename($privateRollback, storage_path('app/private'));
            }

            $this->error('Zazu restore failed safely: '.$exception->getMessage());
            return self::FAILURE;
        } finally {
            File::deleteDirectory($work);
            if ($databaseRollback !== null) {
                File::delete($databaseRollback);
            }
            if ($privateRollback !== null) {
                File::deleteDirectory($privateRollback);
            }
        }
    }
}
