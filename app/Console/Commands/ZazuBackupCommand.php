<?php

namespace App\Console\Commands;

use App\Support\PostgresBackupSupport;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

/** @SuppressWarnings(PHPMD.ExcessiveClassComplexity) */
class ZazuBackupCommand extends Command
{
    private const FORMAT_VERSION = 2;
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

        return $this->runBackup($directory);
    }

    private function runBackup(string $directory): int
    {
        $stamp = now()->format('Ymd_His').'_' . Str::lower((string) Str::ulid());
        $work = storage_path('app/.zazu-backup-'.$stamp);
        $archive = $directory.DIRECTORY_SEPARATOR.'zazu-backup-'.$stamp.'.zip';
        $temporaryArchive = $archive.'.tmp';

        File::ensureDirectoryExists($work);

        try {
            $driver = config('database.default');
            if (!$this->buildBackupArchive($driver, $stamp, $work, $temporaryArchive)) {
                return self::FAILURE;
            }

            if (!$this->verifyArchive($temporaryArchive, $driver)) {
                $this->error('The completed backup archive failed its integrity verification; no backup was published.');
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

    private function buildBackupArchive(string $driver, string $stamp, string $work, string $temporaryArchive): bool
    {
        $manifest = $this->buildManifest($driver, $stamp);

        if (!$this->snapshotDatabase($driver, $work) || !$this->backupPrivateStorage($work)) {
            return false;
        }

        $checksums = $this->buildChecksums($work);
        if ($checksums === null) {
            return false;
        }

        $manifest['checksums'] = $checksums;
        File::put($work.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return $this->createArchive($work, $temporaryArchive);
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

        if ($driver === 'pgsql') {
            return $this->snapshotPostgres($work);
        }

        $this->error('Backup currently supports SQLite, MySQL and PostgreSQL installations.');
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
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Database dump failed: '.$process->getErrorOutput());
            return false;
        }

        $dump = $process->getOutput();
        if (trim($dump) === '') {
            $this->error('Database dump completed without producing SQL output.');
            return false;
        }

        if (File::put($work.'/database.sql', $dump) === false
            || !is_file($work.'/database.sql')
            || filesize($work.'/database.sql') === 0) {
            $this->error('Could not write the MySQL database snapshot.');
            return false;
        }

        return true;
    }

    private function snapshotPostgres(string $work): bool
    {
        try {
            (new PostgresBackupSupport())->dumpToFile($work.'/database.sql');
            return true;
        } catch (\Throwable $exception) {
            $this->error('PostgreSQL backup failed: '.$exception->getMessage());
            return false;
        }
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

        if (!File::copyDirectory($privateRoot, $work.'/storage/private')) {
            $this->error('Could not copy private storage into the backup; no archive was published.');
            return false;
        }

        return true;
    }


    /**
     * @return array<string, string>|null
     */
    private function buildChecksums(string $work): ?array
    {
        $checksums = [];

        foreach (File::allFiles($work) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $checksum = hash_file('sha256', $file->getPathname());

            if ($checksum === false) {
                $this->error('Could not calculate a backup file integrity checksum.');
                return null;
            }

            $checksums[$relative] = $checksum;
        }

        ksort($checksums);

        return $checksums;
    }


    /**
     * Reopen the completed ZIP and verify its structure and every payload hash
     * before publishing it as a usable backup.
     */
    private function verifyArchive(string $archive, string $driver): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($archive, ZipArchive::RDONLY | ZipArchive::CHECKCONS) !== true) {
            return false;
        }

        try {
            if ($zip->numFiles < 2 || $zip->numFiles > self::MAX_PRIVATE_FILES + 2) {
                return false;
            }

            $manifest = $this->readVerifiedManifest($zip, $driver);
            if ($manifest === null) {
                return false;
            }

            $databaseEntry = $driver === 'sqlite' ? 'database.sqlite' : 'database.sql';
            $actualFiles = $this->archivePayloadNames($zip, $databaseEntry);

            if ($actualFiles === null) {
                return false;
            }

            return $this->verifyPayloadManifest($zip, $actualFiles, $manifest['checksums'], $databaseEntry);
        } finally {
            $zip->close();
        }
    }

    private function readVerifiedManifest(ZipArchive $zip, string $driver): ?array
    {
        $contents = $zip->getFromName('manifest.json');
        if ($contents === false) {
            return null;
        }

        try {
            $manifest = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\\JsonException) {
            return null;
        }

        if (!is_array($manifest)
            || ($manifest['application'] ?? null) !== 'Zazu EMP'
            || ($manifest['format_version'] ?? null) !== self::FORMAT_VERSION
            || ($manifest['database_driver'] ?? null) !== $driver
            || !is_array($manifest['checksums'] ?? null)) {
            return null;
        }

        return $manifest;
    }

    private function archivePayloadNames(ZipArchive $zip, string $databaseEntry): ?array
    {
        $actualFiles = [];
        $seen = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (!is_string($name) || !$this->isAllowedArchiveEntry($name, $databaseEntry)) {
                return null;
            }

            if (isset($seen[$name])) {
                return null;
            }
            $seen[$name] = true;

            if ($name !== 'manifest.json') {
                $actualFiles[] = $name;
            }
        }

        return $actualFiles;
    }

    private function isAllowedArchiveEntry(string $name, string $databaseEntry): bool
    {
        if ($name === '' || str_contains($name, '\\\\') || str_contains($name, "\\0")
            || str_starts_with($name, '/') || in_array('..', explode('/', $name), true)) {
            return false;
        }

        return $name === 'manifest.json'
            || $name === $databaseEntry
            || str_starts_with($name, 'storage/private/');
    }

    private function verifyPayloadManifest(ZipArchive $zip, array $actualFiles, array $checksums, string $databaseEntry): bool
    {
        if (!in_array($databaseEntry, $actualFiles, true)) {
            return false;
        }

        foreach ($checksums as $name => $expected) {
            if (!is_string($name) || !is_string($expected)
                || !preg_match('/^[a-f0-9]{64}$/i', $expected)
                || !$this->isAllowedArchiveEntry($name, $databaseEntry)
                || $name === 'manifest.json') {
                return false;
            }
        }

        $checksumFiles = array_keys($checksums);
        sort($actualFiles, SORT_STRING);
        sort($checksumFiles, SORT_STRING);

        if ($actualFiles !== $checksumFiles) {
            return false;
        }

        foreach ($checksums as $name => $expected) {
            if (!$this->verifyPayloadChecksum($zip, $name, $expected)) {
                return false;
            }
        }

        return true;
    }

    private function verifyPayloadChecksum(ZipArchive $zip, string $name, string $expected): bool
    {
        $stream = $zip->getStream($name);
        if ($stream === false) {
            return false;
        }

        $hash = hash_init('sha256');
        $readFailed = false;

        while (!feof($stream)) {
            $chunk = fread($stream, 1024 * 1024);
            if ($chunk === false) {
                $readFailed = true;
                break;
            }

            if ($chunk !== '') {
                hash_update($hash, $chunk);
            }
        }

        fclose($stream);

        return !$readFailed && hash_equals(strtolower($expected), hash_final($hash));
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
