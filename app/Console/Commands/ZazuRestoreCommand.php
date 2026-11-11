<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

/** @SuppressWarnings(PHPMD.ExcessiveClassComplexity) */
class ZazuRestoreCommand extends Command
{
    private const FORMAT_VERSION = 2;
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
        $preservePrivateRollback = false;
        $databaseRollback = null;
        $preserveDatabaseRollback = false;
        $sqliteTarget = null;
        $sqliteDatabaseStaged = false;
        $sqliteWalStaged = false;
        $sqliteShmStaged = false;
        $sqliteDatabaseActivated = false;
        $mysqlRollbackPath = null;
        $mysqlRestoreStarted = false;
        $preserveWork = false;
        $originalSqliteDatabase = config('database.connections.sqlite.database');
        $temporaryMemoryTarget = false;
        $temporaryMemoryDatabasePath = null;

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

            $formatVersion = (int) ($manifest['format_version'] ?? 1);
            if (!in_array($formatVersion, [1, self::FORMAT_VERSION], true)) {
                $zip->close();
                $this->error('The backup format version is not supported by this Zazu installation.');
                return self::FAILURE;
            }

            if ($formatVersion >= 2 && !array_key_exists('checksums', $manifest)) {
                $zip->close();
                $this->error('The backup integrity manifest is missing.');
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
            $archiveFiles = [];
            $uncompressedBytes = 0;

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);
                $stat = $zip->statIndex($index);

                if ($name === false || $stat === false || str_contains($name, "\0")) {
                    $zip->close();
                    $this->error('The backup archive contains an invalid entry.');
                    return self::FAILURE;
                }

                if (str_contains($name, '\\')) {
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
                    || (strlen($name) >= 3 && ctype_alpha($name[0]) && $name[1] === ':' && ($name[2] === '/' || $name[2] === '\\'))
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

                if (!str_ends_with($name, '/')) {
                    $archiveFiles[$name] = true;
                }

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

            // New backups include a SHA-256 map for every payload file. Older
            // format-v1 archives without this optional map remain restorable.
            if (array_key_exists('checksums', $manifest)) {
                $checksums = $manifest['checksums'];
                unset($archiveFiles['manifest.json']);
                $actualFiles = array_keys($archiveFiles);
                $checksumFiles = is_array($checksums) ? array_keys($checksums) : [];

                sort($actualFiles, SORT_STRING);
                sort($checksumFiles, SORT_STRING);

                if (!is_array($checksums) || $actualFiles !== $checksumFiles) {
                    $zip->close();
                    $this->error('The backup integrity manifest does not match its archive contents.');
                    return self::FAILURE;
                }

                foreach ($checksums as $name => $expectedChecksum) {
                    if (!is_string($name) || !is_string($expectedChecksum) || !preg_match('/^[a-f0-9]{64}$/i', $expectedChecksum)) {
                        $zip->close();
                        $this->error('The backup integrity manifest contains an invalid checksum.');
                        return self::FAILURE;
                    }

                    $stream = $zip->getStream($name);
                    if ($stream === false) {
                        $zip->close();
                        $this->error('A backup file could not be read for integrity verification.');
                        return self::FAILURE;
                    }

                    $context = hash_init('sha256');
                    $readFailed = false;

                    while (!feof($stream)) {
                        $chunk = fread($stream, 1024 * 1024);
                        if ($chunk === false) {
                            $readFailed = true;
                            break;
                        }
                        if ($chunk !== '') {
                            hash_update($context, $chunk);
                        }
                    }

                    fclose($stream);

                    if ($readFailed || !hash_equals(strtolower($expectedChecksum), hash_final($context))) {
                        $zip->close();
                        $this->error('A backup file failed its SHA-256 integrity check.');
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

                if (!is_file($source)) {
                    $this->error('SQLite restore database is unavailable.');
                    return self::FAILURE;
                }

                // PHPUnit intentionally uses :memory:. Give that connection a temporary
                // file target so the real restore path can still be exercised without
                // weakening the production file-replacement safeguards.
                if ($target === ':memory:') {
                    $target = storage_path('app/.zazu-test-restore-'.Str::ulid().'.sqlite');
                    config(['database.connections.sqlite.database' => $target]);
                    $temporaryMemoryTarget = true;
                    $temporaryMemoryDatabasePath = $target;
                }

                if (!$target) {
                    $this->error('SQLite restore target is unavailable.');
                    return self::FAILURE;
                }

                $targetParent = dirname($target);
                if (is_link($target) || is_dir($target) || (file_exists($target) && !is_file($target))) {
                    $this->error('SQLite restore target must be a regular database file, not a symbolic link, directory or special file.');
                    return self::FAILURE;
                }

                if (!is_dir($targetParent) || !is_writable($targetParent)) {
                    $this->error('SQLite restore target directory must exist and be writable before restore can begin.');
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

            if (!$this->validateStagedDatabase($driver, $work)) {
                return self::FAILURE;
            }

            // MySQL imports are not atomic. Capture a verified pre-restore dump before
            // touching private storage or importing any candidate SQL.
            if ($driver === 'mysql') {
                $mysqlRollbackPath = $work.'/pre-restore-database.sql';
                $this->snapshotMysqlForRollback($mysqlRollbackPath);
            }

            // Stage private storage only after every required database rollback point exists.
            // Every mutation after this point either completes or throws so catch can recover.
            if ($hasPrivateStorage && is_dir($storedPrivate)) {
                $privateRollback = storage_path('app/.zazu-private-rollback-'.Str::ulid());

                if (is_dir($privateTarget) && !rename($privateTarget, $privateRollback)) {
                    throw new \RuntimeException('Could not stage the current private storage for safe replacement.');
                }

                if (!rename($storedPrivate, $privateTarget)) {
                    // Leave the original private directory staged. The catch path
                    // performs and verifies rollback; finally must never delete it
                    // if reactivation fails.
                    throw new \RuntimeException('Could not activate the restored private storage.');
                }

                $privateActivated = true;
            }

            if ($driver === 'sqlite') {
                $source = $work.'/database.sqlite';
                $target = config('database.connections.sqlite.database');

                // The existing database is being replaced, not preserved in-place.
                // Do not checkpoint it: checkpointing can legitimately report SQLITE_BUSY
                // while another reader is active and would make a valid restore fail.
                // Instead, close the application connection and move the database plus its
                // WAL/SHM sidecars together into the rollback set.
                DB::disconnect('sqlite');
                DB::purge('sqlite');
                gc_collect_cycles();

                $databaseStage = storage_path('app/.zazu-database-restore-'.Str::ulid().'.sqlite');
                if (!copy($source, $databaseStage)) {
                    throw new \RuntimeException('Could not stage the SQLite database for restore.');
                }

                $sqliteTarget = $target;
                $databaseRollback = storage_path('app/.zazu-database-rollback-'.Str::ulid().'.sqlite');
                $wal = $target.'-wal';
                $shm = $target.'-shm';
                $rollbackWal = $databaseRollback.'-wal';
                $rollbackShm = $databaseRollback.'-shm';

                if (is_file($target)) {
                    if (! $this->renameWithRetry($target, $databaseRollback)) {
                        File::delete($databaseStage);
                        throw new \RuntimeException('Could not stage the current SQLite database for safe replacement.');
                    }
                    $sqliteDatabaseStaged = true;
                }

                if (is_file($wal)) {
                    if (! $this->renameWithRetry($wal, $rollbackWal)) {
                        File::delete($databaseStage);
                        throw new \RuntimeException('Could not stage the current SQLite WAL file for safe replacement.');
                    }
                    $sqliteWalStaged = true;
                }

                if (is_file($shm)) {
                    if (! $this->renameWithRetry($shm, $rollbackShm)) {
                        File::delete($databaseStage);
                        throw new \RuntimeException('Could not stage the current SQLite shared-memory file for safe replacement.');
                    }
                    $sqliteShmStaged = true;
                }

                if (! $this->renameWithRetry($databaseStage, $target)) {
                    File::delete($databaseStage);
                    throw new \RuntimeException('Could not activate the restored SQLite database.');
                }

                $sqliteDatabaseActivated = true;
            } elseif ($driver === 'mysql') {
                $mysqlRestoreStarted = true;
                $this->importMysqlDump($work.'/database.sql');
            }

            $this->info('Zazu restore completed from '.$archive);
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $rollbackFailure = null;

            if ($mysqlRestoreStarted && $mysqlRollbackPath !== null && is_file($mysqlRollbackPath)) {
                try {
                    $this->importMysqlDump($mysqlRollbackPath);
                } catch (\Throwable $rollbackException) {
                    $rollbackFailure = $rollbackException->getMessage();
                    // Keep the only pre-restore database snapshot available for manual recovery.
                    $preserveWork = true;
                }
            }

            if ($sqliteTarget !== null && ($sqliteDatabaseStaged || $sqliteWalStaged || $sqliteShmStaged || $sqliteDatabaseActivated)) {
                DB::purge('sqlite');
                gc_collect_cycles();
                $sqliteRollbackErrors = [];

                if ($sqliteDatabaseActivated && is_file($sqliteTarget) && !File::delete($sqliteTarget)) {
                    $sqliteRollbackErrors[] = 'Could not remove the candidate SQLite database before rollback.';
                }

                if (($sqliteDatabaseActivated || $sqliteWalStaged) && is_file($sqliteTarget.'-wal') && !File::delete($sqliteTarget.'-wal')) {
                    $sqliteRollbackErrors[] = 'Could not remove the candidate SQLite WAL file before rollback.';
                }

                if (($sqliteDatabaseActivated || $sqliteShmStaged) && is_file($sqliteTarget.'-shm') && !File::delete($sqliteTarget.'-shm')) {
                    $sqliteRollbackErrors[] = 'Could not remove the candidate SQLite shared-memory file before rollback.';
                }

                if ($sqliteRollbackErrors === []) {
                    foreach ([
                        [$sqliteShmStaged, $databaseRollback.'-shm', $sqliteTarget.'-shm', 'SQLite shared-memory rollback file'],
                        [$sqliteWalStaged, $databaseRollback.'-wal', $sqliteTarget.'-wal', 'SQLite WAL rollback file'],
                        [$sqliteDatabaseStaged, $databaseRollback, $sqliteTarget, 'SQLite database rollback file'],
                    ] as [$wasStaged, $source, $destination, $label]) {
                        if ($wasStaged && (!is_file($source) || !$this->renameWithRetry($source, $destination))) {
                            $sqliteRollbackErrors[] = 'Could not restore the '.$label.'.';
                        }
                    }
                }

                if ($sqliteRollbackErrors !== []) {
                    $preserveDatabaseRollback = true;
                    $sqliteFailure = implode(' ', $sqliteRollbackErrors);
                    $rollbackFailure = trim(($rollbackFailure ? $rollbackFailure.' ' : '').$sqliteFailure);
                } else {
                    $databaseRollback = null;
                    $sqliteDatabaseStaged = false;
                    $sqliteWalStaged = false;
                    $sqliteShmStaged = false;
                    $sqliteDatabaseActivated = false;
                }
            }

            $privateTarget = storage_path('app/private');
            $privateRollbackErrors = [];

            if ($privateActivated && is_dir($privateTarget) && !File::deleteDirectory($privateTarget)) {
                $privateRollbackErrors[] = 'Could not remove the candidate private storage before rollback.';
            }

            if ($privateRollbackErrors === [] && $privateRollback !== null && is_dir($privateRollback)) {
                if (file_exists($privateTarget) || !rename($privateRollback, $privateTarget)) {
                    $privateRollbackErrors[] = 'Could not reactivate the original private storage.';
                } else {
                    $privateRollback = null;
                }
            }

            if ($privateRollbackErrors !== []) {
                $preservePrivateRollback = true;
                $privateFailure = implode(' ', $privateRollbackErrors);
                $rollbackFailure = trim(($rollbackFailure ? $rollbackFailure.' ' : '').$privateFailure);
            } else {
                $privateActivated = false;
            }

            if ($rollbackFailure !== null) {
                $this->error('Zazu restore failed and automatic rollback was incomplete: '.$rollbackFailure);
                if ($mysqlRestoreStarted && $mysqlRollbackPath !== null && $preserveWork) {
                    $this->error('Pre-restore MySQL snapshot preserved at: '.$mysqlRollbackPath);
                }
                if ($preserveDatabaseRollback && $databaseRollback !== null) {
                    $this->error('Pre-restore SQLite snapshot preserved at: '.$databaseRollback);
                }
                if ($preservePrivateRollback && $privateRollback !== null) {
                    $this->error('Original private storage preserved at: '.$privateRollback);
                }
                $this->error('The installation may require operator recovery; do not retry against it.');
            } else {
                $this->error('Zazu restore failed; rollback was attempted where required: '.$exception->getMessage());
            }

            return self::FAILURE;
        } finally {
            if ($temporaryMemoryTarget) {
                DB::purge('sqlite');
                config(['database.connections.sqlite.database' => $originalSqliteDatabase]);
                foreach ([$temporaryMemoryDatabasePath, $temporaryMemoryDatabasePath.'-wal', $temporaryMemoryDatabasePath.'-shm'] as $temporaryFile) {
                    if (is_string($temporaryFile) && is_file($temporaryFile) && !File::delete($temporaryFile)) {
                        $this->warn('Could not remove temporary SQLite restore file: '.$temporaryFile);
                    }
                }
            }
            if (!$preserveWork) {
                File::deleteDirectory($work);
            }
            if (!$preserveDatabaseRollback && $databaseRollback !== null) {
                foreach ([$databaseRollback.'-shm', $databaseRollback.'-wal', $databaseRollback] as $rollbackFile) {
                    if (is_file($rollbackFile) && !File::delete($rollbackFile)) {
                        $this->warn('Could not remove a temporary SQLite rollback artifact: '.$rollbackFile);
                    }
                }
            }
            if (!$preservePrivateRollback && $privateRollback !== null && is_dir($privateRollback) && !File::deleteDirectory($privateRollback)) {
                $this->warn('Could not remove the temporary private-storage rollback directory: '.$privateRollback);
            }
        }
    }

    private function snapshotMysqlForRollback(string $path): void
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
        $process->run();

        if (!$process->isSuccessful() || trim($process->getOutput()) === '') {
            throw new \RuntimeException('Could not create the pre-restore MySQL rollback snapshot: '.$process->getErrorOutput());
        }

        File::put($path, $process->getOutput());

        if (!is_file($path) || filesize($path) === 0) {
            throw new \RuntimeException('The pre-restore MySQL rollback snapshot is empty.');
        }
    }

    private function importMysqlDump(string $path): void
    {
        if (!is_file($path) || filesize($path) === 0) {
            throw new \RuntimeException('MySQL import dump is empty or unavailable.');
        }

        $connection = config('database.connections.mysql');
        $process = new Process([
            'mysql',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            $connection['database'],
        ], null, array_filter(['MYSQL_PWD' => $connection['password'] ?? null]));

        $process->setTimeout(300);
        $process->setInput(File::get($path));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('MySQL import failed: '.$process->getErrorOutput());
        }
    }

    private function validateStagedDatabase(string $driver, string $work): bool
    {
        if ($driver === 'sqlite') {
            $path = $work.'/database.sqlite';

            try {
                $pdo = new \PDO('sqlite:'.$path);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $result = $pdo->query('PRAGMA integrity_check')->fetchColumn();

                if ($result !== 'ok') {
                    $this->error('The backup SQLite database failed its integrity check.');
                    return false;
                }

                return true;
            } catch (\Throwable $exception) {
                $this->error('The backup SQLite database could not be validated: '.$exception->getMessage());
                return false;
            }
        }

        if ($driver === 'mysql') {
            $path = $work.'/database.sql';

            if (!is_file($path) || filesize($path) === 0) {
                $this->error('The MySQL backup dump is empty or unavailable.');
                return false;
            }

            return true;
        }

        return false;
    }

    private function renameWithRetry(string $from, string $to): bool
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            if (rename($from, $to)) {
                return true;
            }

            gc_collect_cycles();
            usleep(100000);
        }

        return false;
    }
}


