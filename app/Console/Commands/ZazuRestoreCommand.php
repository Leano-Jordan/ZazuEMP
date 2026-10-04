<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * Restore deliberately keeps archive validation and rollback safety close to the command
 * boundary so a restore cannot accidentally bypass the safety checks.
 */
class ZazuRestoreCommand extends Command
{
    private const FORMAT_VERSION = 1;
    private const MAX_ARCHIVE_ENTRIES = 50000;
    private const MAX_UNCOMPRESSED_BYTES = 2147483648;

    protected $signature = 'zazu:restore {archive : Path to a Zazu backup ZIP} {--force : Skip the interactive confirmation}';
    protected $description = 'Restore a Zazu database and private storage from a verified backup archive';

    public function handle(): int
    {
        if (!$this->validateRestoreRequest()) {
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('This replaces the current Zazu database and private files. Continue?')) {
            return self::SUCCESS;
        }

        $archive = $this->argument('archive');
        $work = storage_path(
            'app/.zazu-restore-'.now()->format('Ymd_His').'_' . Str::lower((string) Str::ulid())
        );
        File::ensureDirectoryExists($work);

        try {
            return $this->executeRestore($archive, $work);
        } catch (\Throwable $exception) {
            $this->error('Zazu restore failed safely: '.$exception->getMessage());
            return self::FAILURE;
        } finally {
            $this->cleanupRestoreWorkspace($work);
        }
    }

    private function validateRestoreRequest(): bool
    {
        if (!class_exists(ZipArchive::class)) {
            $this->error('The PHP Zip extension is required for Zazu restores.');
            return false;
        }

        $archive = $this->argument('archive');

        if (!is_file($archive) || !is_readable($archive)) {
            $this->error('Backup archive was not found or cannot be read.');
            return false;
        }

        return true;
    }

    private function executeRestore(string $archive, string $work): int
    {
        $prepared = $this->prepareArchive($archive, $work);
        if ($prepared === null) {
            return self::FAILURE;
        }

        [$driver, $hasPrivateStorage] = $prepared;

        if (!$this->prepareDatabaseTarget($driver, $work) || !$this->validateStagedDatabase($driver, $work)) {
            return self::FAILURE;
        }

        $databaseRollback = $driver === 'mysql' ? $this->stageMysqlRollback() : null;
        $privateRollback = null;
        $privateActivated = false;

        try {
            $privateRollback = $this->activatePrivateStorage($work, $hasPrivateStorage);
            $privateActivated = $hasPrivateStorage;

            $databaseRollback = $this->restoreDatabase($driver, $work, $databaseRollback);

            $this->completeRestore($databaseRollback, $privateRollback);
            $databaseRollback = null;
            $privateRollback = null;

            $this->info('Zazu restore completed from '.$archive);
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->rollbackRestore($driver, $databaseRollback, $privateRollback, $privateActivated);
            throw $exception;
        } finally {
            $this->cleanupRestoreArtifacts($databaseRollback, $privateRollback);
        }
    }

    private function restoreDatabase(string $driver, string $work, ?string $databaseRollback): ?string
    {
        if ($driver === 'sqlite') {
            return $this->restoreSqlite($work);
        }

        if ($driver === 'mysql') {
            $this->restoreMysql($work);
            return $databaseRollback;
        }

        throw new \LogicException('Unsupported restore database driver.');
    }

    private function completeRestore(?string $databaseRollback, ?string $privateRollback): void
    {
        if ($databaseRollback !== null) {
            File::delete($databaseRollback);
        }

        if ($privateRollback !== null) {
            File::deleteDirectory($privateRollback);
        }
    }

    private function cleanupRestoreArtifacts(?string $databaseRollback, ?string $privateRollback): void
    {
        if ($databaseRollback !== null) {
            File::delete($databaseRollback);
        }

        if ($privateRollback !== null) {
            File::deleteDirectory($privateRollback);
        }
    }

    private function cleanupRestoreWorkspace(string $work): void
    {
        File::deleteDirectory($work);
    }

    private function prepareArchive(string $archive, string $work): ?array
    {
        $zip = new ZipArchive();

        if ($zip->open($archive, ZipArchive::RDONLY | ZipArchive::CHECKCONS) !== true) {
            $this->error('The backup archive could not be opened or verified.');
            return null;
        }

        try {
            return $this->prepareOpenedArchive($zip, $work);
        } finally {
            $zip->close();
        }
    }

    private function prepareOpenedArchive(ZipArchive $zip, string $work): ?array
    {
        $manifest = $this->readManifest($zip);
        if ($manifest === null || !$this->validateManifest($manifest)) {
            return null;
        }

        $driver = config('database.default');
        if (($manifest['database_driver'] ?? null) !== $driver) {
            $this->error('The backup database driver does not match the current Zazu database configuration.');
            return null;
        }

        $databaseEntry = $driver === 'sqlite' ? 'database.sqlite' : 'database.sql';
        $hasPrivateStorage = $this->validateEntries($zip, $databaseEntry);

        if ($hasPrivateStorage === null) {
            return null;
        }

        if ($zip->locateName($databaseEntry) === false) {
            $this->error('The backup database is missing.');
            return null;
        }

        if (!$this->extractVerifiedArchive($zip, $work)) {
            return null;
        }

        return [$driver, $hasPrivateStorage];
    }

    private function extractVerifiedArchive(ZipArchive $zip, string $work): bool
    {
        if (!$zip->extractTo($work)) {
            $this->error('Could not extract the verified backup archive.');
            return false;
        }

        if (!is_file($work.'/manifest.json')) {
            $this->error('The extracted backup manifest is missing.');
            return false;
        }

        return true;
    }

    private function readManifest(ZipArchive $zip): ?array
    {
        if ($zip->numFiles < 1 || $zip->numFiles > self::MAX_ARCHIVE_ENTRIES) {
            $this->error('The backup archive contains an invalid number of entries.');
            return null;
        }

        $manifestIndex = $zip->locateName('manifest.json');
        if ($manifestIndex === false) {
            $this->error('The archive is not a valid Zazu backup.');
            return null;
        }

        $contents = $zip->getFromIndex($manifestIndex);
        if ($contents === false) {
            $this->error('The backup manifest could not be read.');
            return null;
        }

        try {
            return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->error('The backup manifest is invalid.');
            return null;
        }
    }

    private function validateManifest(array $manifest): bool
    {
        if (($manifest['application'] ?? null) !== 'Zazu EMP') {
            $this->error('The backup manifest is not for Zazu EMP.');
            return false;
        }

        if (isset($manifest['format_version']) && (int) $manifest['format_version'] !== self::FORMAT_VERSION) {
            $this->error('The backup format version is not supported by this Zazu installation.');
            return false;
        }

        return true;
    }

    private function validateEntries(ZipArchive $zip, string $databaseEntry): ?bool
    {
        $hasPrivateStorage = false;
        $seen = [];
        $uncompressedBytes = 0;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $result = $this->validateEntry($zip, $index, $databaseEntry, $seen, $uncompressedBytes);

            if ($result === null) {
                return null;
            }

            $hasPrivateStorage = $hasPrivateStorage || $result;
        }

        return $hasPrivateStorage;
    }

    private function validateEntry(
        ZipArchive $zip,
        int $index,
        string $databaseEntry,
        array &$seen,
        int &$uncompressedBytes
    ): ?bool {
        $name = $zip->getNameIndex($index);
        $stat = $zip->statIndex($index);

        if (!$this->validateEntryMetadata($name, $stat)) {
            return null;
        }

        $normalized = ltrim($name, '/');
        if (!$this->validateEntryPath($name, $normalized)) {
            return null;
        }

        if (isset($seen[$normalized])) {
            $this->error('The backup archive contains duplicate entries.');
            return null;
        }
        $seen[$normalized] = true;

        $size = (int) ($stat['size'] ?? 0);
        if (!$this->validateEntrySize($size, $uncompressedBytes)) {
            return null;
        }
        $uncompressedBytes += $size;

        if (!$this->validateEntryType($name, $databaseEntry, 'storage/private/')) {
            return null;
        }

        if ($this->containsSymbolicLink($zip, $index)) {
            $this->error('The backup contains a symbolic link and cannot be restored safely.');
            return null;
        }

        return str_starts_with($name, 'storage/private/') && !str_ends_with($name, '/');
    }

    private function validateEntrySize(int $size, int $uncompressedBytes): bool
    {
        if ($size < 0 || $size > self::MAX_UNCOMPRESSED_BYTES || $uncompressedBytes > self::MAX_UNCOMPRESSED_BYTES - $size) {
            $this->error('The backup archive is too large to restore safely.');
            return false;
        }

        return true;
    }

    private function validateEntryMetadata(string|false $name, array|false $stat): bool
    {
        if ($name === false || $stat === false || str_contains($name, " ")) {
            $this->error('The backup archive contains an invalid entry.');
            return false;
        }

        if (str_contains($name, '\\')) {
            $this->error('The backup archive contains an ambiguous path.');
            return false;
        }

        return true;
    }

    private function validateEntryPath(string $name, string $normalized): bool
    {
        if ($this->hasUnsafeEntryPathShape($name, $normalized)) {
            $this->error('The backup archive contains an unsafe path.');
            return false;
        }

        return true;
    }

    private function hasUnsafeEntryPathShape(string $name, string $normalized): bool
    {
        if ($normalized !== $name || $name === '' || str_starts_with($name, '/')) {
            return true;
        }

        if ($this->isWindowsAbsolutePath($name)) {
            return true;
        }

        $parts = explode('/', $normalized);

        return in_array('..', $parts, true)
            || (in_array('', $parts, true) && !str_ends_with($name, '/'));
    }

    private function isWindowsAbsolutePath(string $name): bool
    {
        return strlen($name) >= 3
            && ctype_alpha($name[0])
            && $name[1] === ':'
            && ($name[2] === '/' || $name[2] === '\\');
    }

    private function validateEntryType(string $name, string $databaseEntry, string $privateEntryPrefix): bool
    {
        if ($name === 'database.sqlite' || $name === 'database.sql') {
            if ($name !== $databaseEntry) {
                $this->error('The backup contains a database for the wrong driver.');
                return false;
            }

            return true;
        }

        if ($name !== 'manifest.json' && !str_starts_with($name, $privateEntryPrefix)) {
            $this->error('The backup contains an unsupported file.');
            return false;
        }

        return true;
    }

    private function containsSymbolicLink(ZipArchive $zip, int $index): bool
    {
        $opsys = 0;
        $attributes = 0;

        if (!method_exists($zip, 'getExternalAttributesIndex') || !$zip->getExternalAttributesIndex($index, $opsys, $attributes)) {
            return false;
        }

        return $opsys === ZipArchive::OPSYS_UNIX && (($attributes >> 16) & 0170000) === 0120000;
    }

    private function prepareDatabaseTarget(string $driver, string $work): bool
    {
        if ($driver === 'sqlite') {
            $source = $work.'/database.sqlite';
            $target = config('database.connections.sqlite.database');

            if (!is_file($source)) {
                $this->error('SQLite restore database is unavailable.');
                return false;
            }

            if ($target === ':memory:') {
                $target = storage_path('app/.zazu-test-restore-'.Str::ulid().'.sqlite');
                config(['database.connections.sqlite.database' => $target]);
            }

            if (!$target) {
                $this->error('SQLite restore target is unavailable.');
                return false;
            }

            return true;
        }

        if ($driver === 'mysql') {
            if (!is_file($work.'/database.sql')) {
                $this->error('MySQL dump is missing from the backup.');
                return false;
            }

            return true;
        }

        $this->error('Restore currently supports SQLite and MySQL installations.');
        return false;
    }

    private function activatePrivateStorage(string $work, bool $hasPrivateStorage): ?string
    {
        if (!$hasPrivateStorage) {
            return null;
        }

        $storedPrivate = $work.'/storage/private';
        $privateTarget = storage_path('app/private');
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

        return $privateRollback;
    }

    private function restoreSqlite(string $work): string
    {
        $source = $work.'/database.sqlite';
        $target = config('database.connections.sqlite.database');

        DB::disconnect('sqlite');
        DB::purge('sqlite');
        gc_collect_cycles();

        $databaseStage = storage_path('app/.zazu-database-restore-'.Str::ulid().'.sqlite');
        if (!copy($source, $databaseStage)) {
            throw new \RuntimeException('Could not stage the SQLite database for restore.');
        }

        $databaseRollback = storage_path('app/.zazu-database-rollback-'.Str::ulid().'.sqlite');
        $wal = $target.'-wal';
        $shm = $target.'-shm';
        $rollbackWal = $databaseRollback.'-wal';
        $rollbackShm = $databaseRollback.'-shm';

        if (is_file($target) && !$this->renameWithRetry($target, $databaseRollback)) {
            File::delete($databaseStage);
            throw new \RuntimeException('Could not stage the current SQLite database for safe replacement.');
        }

        if (is_file($wal) && !$this->renameWithRetry($wal, $rollbackWal)) {
            $this->restoreDatabaseStage($databaseStage, $databaseRollback, $rollbackWal, $rollbackShm, $target);
            throw new \RuntimeException('Could not stage the current SQLite WAL file for safe replacement.');
        }

        if (is_file($shm) && !$this->renameWithRetry($shm, $rollbackShm)) {
            $this->renameWithRetry($rollbackWal, $wal);
            $this->renameWithRetry($databaseRollback, $target);
            File::delete($databaseStage);
            throw new \RuntimeException('Could not stage the current SQLite shared-memory file for safe replacement.');
        }

        if (!$this->renameWithRetry($databaseStage, $target)) {
            $this->renameWithRetry($rollbackShm, $shm);
            $this->renameWithRetry($rollbackWal, $wal);
            $this->renameWithRetry($databaseRollback, $target);
            File::delete($databaseStage);
            throw new \RuntimeException('Could not activate the restored SQLite database.');
        }

        return $databaseRollback;
    }

    private function restoreDatabaseStage(string $databaseStage, string $databaseRollback, string $rollbackWal, string $rollbackShm, string $target): void
    {
        File::delete($databaseStage);
        $this->renameWithRetry($rollbackShm, $target.'-shm');
        $this->renameWithRetry($rollbackWal, $target.'-wal');
        $this->renameWithRetry($databaseRollback, $target);
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

    private function stageMysqlRollback(): string
    {
        $connection = config('database.connections.mysql');
        $rollback = storage_path('app/.zazu-mysql-rollback-'.Str::ulid().'.sql');

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

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Could not create the pre-restore MySQL rollback snapshot: '.$process->getErrorOutput());
        }

        if (File::put($rollback, $process->getOutput()) === false || !is_file($rollback) || filesize($rollback) === 0) {
            File::delete($rollback);
            throw new \RuntimeException('The pre-restore MySQL rollback snapshot was not created.');
        }

        return $rollback;
    }

    private function restoreMysql(string $work): void
    {
        $this->restoreMysqlDump(File::get($work.'/database.sql'), 'Database restore failed');
    }

    private function restoreMysqlDump(string $sql, string $failureMessage): void
    {
        $connection = config('database.connections.mysql');

        $process = new Process([
            'mysql',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            $connection['database'],
        ], null, array_filter(['MYSQL_PWD' => $connection['password'] ?? null]));

        $process->setTimeout(300);
        $process->setInput($sql);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($failureMessage.': '.$process->getErrorOutput());
        }
    }

    private function rollbackRestore(?string $driver, ?string $databaseRollback, ?string $privateRollback, bool $privateActivated): void
    {
        if ($databaseRollback !== null && is_file($databaseRollback)) {
            if ($driver === 'mysql') {
                try {
                    $this->restoreMysqlDump(File::get($databaseRollback), 'MySQL rollback restore failed');
                } catch (\Throwable $exception) {
                    $this->error('MySQL rollback could not be completed: '.$exception->getMessage());
                }
            } elseif ($driver === 'sqlite') {
                $target = config('database.connections.sqlite.database');
                DB::purge('sqlite');
                gc_collect_cycles();
                File::delete($target);
                File::delete($target.'-wal');
                File::delete($target.'-shm');
                $this->renameWithRetry($databaseRollback.'-shm', $target.'-shm');
                $this->renameWithRetry($databaseRollback.'-wal', $target.'-wal');
                $this->renameWithRetry($databaseRollback, $target);
            }
        }

        if ($privateActivated) {
            File::deleteDirectory(storage_path('app/private'));
        }

        if ($privateRollback !== null && is_dir($privateRollback)) {
            rename($privateRollback, storage_path('app/private'));
        }
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
