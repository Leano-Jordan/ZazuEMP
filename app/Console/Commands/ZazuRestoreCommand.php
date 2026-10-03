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

        $work = storage_path('app/.zazu-restore-'.now()->format('Ymd_His').'_' . Str::lower((string) Str::ulid());
        File::ensureDirectoryExists($work);

        $privateRollback = null;
        $privateActivated = false;
        $databaseRollback = null;

        try {
            $prepared = $this->prepareArchive($archive, $work);
            if ($prepared === null) {
                return self::FAILURE;
            }

            [$driver, $hasPrivateStorage] = $prepared;

            if (!$this->prepareDatabaseTarget($driver, $work)) {
                return self::FAILURE;
            }

            $privateRollback = $this->activatePrivateStorage($work, $hasPrivateStorage);
            $privateActivated = $hasPrivateStorage;

            if ($driver === 'sqlite') {
                $databaseRollback = $this->restoreSqlite($work);
            } elseif ($driver === 'mysql') {
                $this->restoreMysql($work);
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
            $this->rollbackRestore($databaseRollback, $privateRollback, $privateActivated);
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

    private function prepareArchive(string $archive, string $work): ?array
    {
        $zip = new ZipArchive();

        if ($zip->open($archive, ZipArchive::RDONLY | ZipArchive::CHECKCONS) !== true) {
            $this->error('The backup archive could not be opened or verified.');
            return null;
        }

        try {
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

            if (!$zip->extractTo($work)) {
                $this->error('Could not extract the verified backup archive.');
                return null;
            }

            if (!is_file($work.'/manifest.json')) {
                $this->error('The extracted backup manifest is missing.');
                return null;
            }

            return [$driver, $hasPrivateStorage];
        } finally {
            $zip->close();
        }
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
        $privateEntryPrefix = 'storage/private/';
        $hasPrivateStorage = false;
        $seen = [];
        $uncompressedBytes = 0;

        for ($index = 0; $index < $zip->numFiles; $index++) {
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
            if ($size < 0 || $size > self::MAX_UNCOMPRESSED_BYTES || $uncompressedBytes > self::MAX_UNCOMPRESSED_BYTES - $size) {
                $this->error('The backup archive is too large to restore safely.');
                return null;
            }
            $uncompressedBytes += $size;

            if (!$this->validateEntryType($name, $databaseEntry, $privateEntryPrefix)) {
                return null;
            }

            if (str_starts_with($name, $privateEntryPrefix) && !str_ends_with($name, '/')) {
                $hasPrivateStorage = true;
            }

            if ($this->containsSymbolicLink($zip, $index)) {
                $this->error('The backup contains a symbolic link and cannot be restored safely.');
                return null;
            }
        }

        return $hasPrivateStorage;
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
        $parts = explode('/', $normalized);

        if (
            $normalized !== $name
            || $name === ''
            || str_starts_with($name, '/')
            || (strlen($name) >= 3
                && ctype_alpha($name[0])
                && $name[1] === ':'
                && ($name[2] === '/' || $name[2] === '\\'))
            || in_array('..', $parts, true)
            || (in_array('', $parts, true) && !str_ends_with($name, '/'))
        ) {
            $this->error('The backup archive contains an unsafe path.');
            return false;
        }

        return true;
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

    private function restoreMysql(string $work): void
    {
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

    private function rollbackRestore(?string $databaseRollback, ?string $privateRollback, bool $privateActivated): void
    {
        if ($databaseRollback !== null && is_file($databaseRollback)) {
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
