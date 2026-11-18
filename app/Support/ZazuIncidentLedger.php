<?php

namespace App\Support;

use Illuminate\Support\Str;
use SplFileObject;
use Throwable;

final class ZazuIncidentLedger
{
    private const MAX_FILE_BYTES = 5_242_880;

    /**
     * Persist a privacy-filtered incident independently of the business database
     * and the configured Laravel log channel.
     */
    public static function record(array $context): array
    {
        $requestId = $context['request_id'] ?? null;

        if (!is_string($requestId) || !Str::isUuid($requestId)) {
            return [];
        }

        $safe = self::allowlisted($context);
        $safe['failure_fingerprint_version'] = 1;
        $safe['failure_fingerprint'] = self::fingerprint($safe);
        self::persist($safe);

        return $safe;
    }

    public static function find(string $requestId): ?array
    {
        if (!Str::isUuid($requestId)) {
            return null;
        }

        $directory = storage_path('app/private/zazu-diagnostics');

        foreach (self::ledgerPaths($directory) as $path) {
            $record = self::findInFile($path, $requestId);

            if ($record !== null) {
                return self::allowlisted($record);
            }
        }

        return null;
    }

    private static function persist(array $safe): void
    {
        $directory = storage_path('app/private/zazu-diagnostics');
        $ledger = $directory.DIRECTORY_SEPARATOR.'incidents.jsonl';
        $lockPath = $directory.DIRECTORY_SEPARATOR.'incidents.lock';

        try {
            self::ensureDirectory($directory);
            $lock = fopen($lockPath, 'c');

            if ($lock === false) {
                throw new \RuntimeException('Unable to open the incident ledger lock.');
            }

            try {
                self::appendWhileLocked($lock, $ledger, $safe);
            } finally {
                fclose($lock);
            }
        } catch (Throwable $failure) {
            // Never send the original exception or incident payload to another logger.
            error_log('Zazu incident ledger write failed: '.$failure::class);
        }
    }

    private static function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create the private incident ledger directory.');
        }
    }

    /**
     * @param resource $lock
     */
    private static function appendWhileLocked($lock, string $ledger, array $safe): void
    {
        if (!flock($lock, LOCK_EX)) {
            throw new \RuntimeException('Unable to lock the incident ledger.');
        }

        try {
            self::rotateIfNeeded($ledger);
            $line = json_encode($safe, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES).PHP_EOL;
            $written = file_put_contents($ledger, $line, FILE_APPEND | LOCK_EX);

            if ($written === false || $written !== strlen($line)) {
                throw new \RuntimeException('The incident ledger write was incomplete.');
            }
        } finally {
            flock($lock, LOCK_UN);
        }
    }

    private static function rotateIfNeeded(string $ledger): void
    {
        if (!is_file($ledger) || filesize($ledger) < self::MAX_FILE_BYTES) {
            return;
        }

        $previous = $ledger.'.1';

        if (is_file($previous) && !unlink($previous)) {
            throw new \RuntimeException('Unable to rotate the previous incident ledger.');
        }

        if (!rename($ledger, $previous)) {
            throw new \RuntimeException('Unable to rotate the incident ledger.');
        }
    }

    private static function ledgerPaths(string $directory): array
    {
        return [
            $directory.DIRECTORY_SEPARATOR.'incidents.jsonl',
            $directory.DIRECTORY_SEPARATOR.'incidents.jsonl.1',
        ];
    }

    private static function findInFile(string $path, string $requestId): ?array
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        try {
            $file = new SplFileObject($path, 'r');

            while (!$file->eof()) {
                $record = self::decodeMatchingLine($file->fgets(), $requestId);

                if ($record !== null) {
                    return $record;
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private static function decodeMatchingLine(mixed $line, string $requestId): ?array
    {
        if (!is_string($line) || !str_contains($line, $requestId)) {
            return null;
        }

        try {
            $record = json_decode($line, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($record) && ($record['request_id'] ?? null) === $requestId
            ? $record
            : null;
    }

    private static function allowlisted(array $context): array
    {
        $fields = [
            'request_id',
            'failure_fingerprint',
            'failure_fingerprint_version',
            'error_code',
            'category',
            'severity',
            'http_status',
            'exception_class',
            'originating_component',
            'route_name',
            'http_method',
            'timestamp',
            'line',
            'database_connection',
            'database_driver',
            'database_sqlstate',
            'database_driver_code',
            'database_operation',
            'database_failure_class',
        ];

        $safe = [];

        foreach ($fields as $field) {
            $value = $context[$field] ?? null;

            if (is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
                $safe[$field] = $value;
            }
        }

        $sourceFile = $context['source_file'] ?? $context['file'] ?? null;

        if (is_string($sourceFile)) {
            $safe['source_file'] = basename($sourceFile);
        }

        return $safe;
    }

    private static function fingerprint(array $context): string
    {
        $identity = [
            'error_code' => $context['error_code'] ?? 'unknown',
            'exception_class' => $context['exception_class'] ?? 'unknown',
            'originating_component' => $context['originating_component'] ?? 'unknown',
            'source_file' => $context['source_file'] ?? 'unknown',
            'database_driver' => $context['database_driver'] ?? 'none',
            'database_sqlstate' => $context['database_sqlstate'] ?? 'none',
            'database_operation' => $context['database_operation'] ?? 'none',
            'database_failure_class' => $context['database_failure_class'] ?? 'none',
        ];

        return 'ZAZU-'.strtoupper(substr(hash('sha256', json_encode($identity, JSON_THROW_ON_ERROR)), 0, 16));
    }
}
