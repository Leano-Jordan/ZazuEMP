<?php

namespace App\Support;

use SplFileObject;
use Throwable;

final class ZazuSentinelHealth
{
    private const ACTIVE_LEDGER = 'incidents.jsonl';

    private const PREVIOUS_LEDGER = 'incidents.jsonl.1';

    public function __construct(private readonly ?string $directory = null)
    {
    }

    /**
     * Inspect Sentinel's local evidence path without querying the business database.
     *
     * A disposable write/read/delete probe verifies that the directory is usable.
     */
    public function inspect(): array
    {
        $directory = $this->directory ?? storage_path('app/private/zazu-diagnostics');
        $checks = [
            $this->directoryCheck($directory),
            ...$this->directoryChecks($directory),
        ];

        return [
            'status' => $this->overallStatus($checks),
            'checked_at' => now()->toIso8601String(),
            'checks' => $checks,
        ];
    }

    private function directoryCheck(string $directory): array
    {
        $exists = is_dir($directory);
        $readable = $exists && is_readable($directory);

        return [
            'key' => 'diagnostic_directory',
            'label' => 'Private diagnostics folder',
            'status' => $readable ? 'healthy' : 'failed',
            'message' => ! $exists
                ? 'The private diagnostics folder is missing.'
                : ($readable
                    ? 'The private diagnostics folder is present and readable.'
                    : 'The private diagnostics folder cannot be read.'),
        ];
    }

    private function directoryChecks(string $directory): array
    {
        if (! is_dir($directory)) {
            return $this->notCheckedDirectoryItems();
        }

        return [
            $this->inspectSegment($directory, self::ACTIVE_LEDGER, 'Current incident ledger'),
            $this->inspectSegment($directory, self::PREVIOUS_LEDGER, 'Previous incident ledger'),
            $this->writeProbe($directory),
        ];
    }

    private function notCheckedDirectoryItems(): array
    {
        $items = [
            ['key' => 'current_ledger', 'label' => 'Current incident ledger'],
            ['key' => 'previous_ledger', 'label' => 'Previous incident ledger'],
            ['key' => 'write_probe', 'label' => 'Diagnostic write/read test'],
        ];

        return array_map(static fn (array $item): array => [
            ...$item,
            'status' => 'not_checked',
            'message' => 'Not checked because the diagnostics folder is unavailable.',
        ], $items);
    }

    private function overallStatus(array $checks): string
    {
        $statuses = array_column($checks, 'status');

        if (in_array('failed', $statuses, true)) {
            return 'failed';
        }

        if (in_array('attention', $statuses, true)) {
            return 'attention';
        }

        return in_array('unknown', $statuses, true) ? 'unknown' : 'healthy';
    }

    private function inspectSegment(string $directory, string $filename, string $label): array
    {
        $path = $directory.DIRECTORY_SEPARATOR.$filename;
        $key = $this->segmentKey($filename);

        if (! file_exists($path)) {
            return $this->emptySegment($key, $label);
        }

        if (! is_file($path) || ! is_readable($path)) {
            return $this->unreadableSegment($key, $label);
        }

        try {
            $summary = $this->readSegment($path);
        } catch (Throwable) {
            return $this->unreadableSegment($key, $label);
        }

        return [
            'key' => $key,
            'label' => $label,
            'status' => $summary['malformed_lines'] > 0 ? 'attention' : 'healthy',
            'message' => $summary['malformed_lines'] > 0
                ? 'Some lines cannot be read as incident records. Valid records remain available.'
                : 'The incident segment is readable.',
            ...$summary,
            'size_bytes' => filesize($path) ?: 0,
        ];
    }

    private function segmentKey(string $filename): string
    {
        return $filename === self::ACTIVE_LEDGER ? 'current_ledger' : 'previous_ledger';
    }

    private function emptySegment(string $key, string $label): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => 'healthy',
            'message' => 'No records have been written to this segment yet.',
            'records' => 0,
            'malformed_lines' => 0,
            'size_bytes' => 0,
        ];
    }

    private function unreadableSegment(string $key, string $label): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => 'failed',
            'message' => 'Sentinel could not read this incident segment as a normal file.',
        ];
    }

    private function readSegment(string $path): array
    {
        $records = 0;
        $malformed = 0;
        $file = new SplFileObject($path, 'r');

        while (! $file->eof()) {
            $lineStatus = $this->classifyLine($file->fgets());

            if ($lineStatus === 'valid') {
                $records++;
            } elseif ($lineStatus === 'malformed') {
                $malformed++;
            }
        }

        return [
            'records' => $records,
            'malformed_lines' => $malformed,
        ];
    }

    private function classifyLine(mixed $line): string
    {
        if (! is_string($line) || trim($line) === '') {
            return 'empty';
        }

        try {
            $record = json_decode($line, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return 'malformed';
        }

        return is_array($record) && is_string($record['request_id'] ?? null)
            ? 'valid'
            : 'malformed';
    }

    private function writeProbe(string $directory): array
    {
        $path = null;
        $handle = false;
        $created = false;
        $writeReadPassed = false;
        $cleanupPassed = true;
        $failureMessage = 'Sentinel could not create a temporary diagnostics file.';

        try {
            $path = $directory.DIRECTORY_SEPARATOR.'sentinel-health-'.bin2hex(random_bytes(8)).'.tmp';
            $handle = @fopen($path, 'x+b');
            $created = is_resource($handle);

            if (! $created) {
                $failureMessage = 'Sentinel could not create a temporary diagnostics file.';
            } elseif (! flock($handle, LOCK_EX)) {
                $failureMessage = 'Sentinel could not lock its temporary diagnostics file.';
            } else {
                $writeReadPassed = $this->verifyProbe($handle);
                $failureMessage = 'Sentinel could not verify a temporary write and read.';
            }
        } catch (Throwable) {
            $failureMessage = 'Sentinel could not complete its temporary write/read test.';
        } finally {
            $cleanupPassed = $this->cleanupProbe($handle, $path, $created);
        }

        return [
            'key' => 'write_probe',
            'label' => 'Diagnostic write/read test',
            'status' => $writeReadPassed && $cleanupPassed ? 'healthy' : 'failed',
            'message' => ! $writeReadPassed
                ? $failureMessage
                : ($cleanupPassed
                    ? 'Temporary write and read passed; the probe file was removed.'
                    : 'The probe worked but Sentinel could not remove its temporary file.'),
        ];
    }

    /**
     * @param resource $handle
     */
    private function verifyProbe($handle): bool
    {
        $marker = 'zazu-sentinel-health-probe';
        $written = fwrite($handle, $marker);
        $flushed = fflush($handle);
        $rewound = rewind($handle);
        $readBack = stream_get_contents($handle);

        return $written === strlen($marker) && $flushed && $rewound && $readBack === $marker;
    }

    private function cleanupProbe(mixed $handle, ?string $path, bool $created): bool
    {
        if (is_resource($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        if (! $created || ! is_string($path) || ! file_exists($path)) {
            return true;
        }

        return @unlink($path);
    }
}
