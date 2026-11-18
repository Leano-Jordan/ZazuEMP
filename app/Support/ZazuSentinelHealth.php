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
     * Inspect Sentinel's own local evidence path without querying the business database.
     *
     * A short-lived write/read/delete probe verifies that the directory is usable.
     * The probe contains no business or incident data and is removed immediately.
     */
    public function inspect(): array
    {
        $directory = $this->directory ?? storage_path('app/private/zazu-diagnostics');
        $checks = [];
        $directoryExists = is_dir($directory);

        $checks[] = [
            'key' => 'diagnostic_directory',
            'label' => 'Private diagnostics folder',
            'status' => $directoryExists && is_readable($directory) ? 'healthy' : 'failed',
            'message' => ! $directoryExists
                ? 'The private diagnostics folder is missing.'
                : (is_readable($directory)
                    ? 'The private diagnostics folder is present and readable.'
                    : 'The private diagnostics folder cannot be read.'),
        ];

        if ($directoryExists) {
            $checks[] = $this->inspectSegment($directory, self::ACTIVE_LEDGER, 'Current incident ledger');
            $checks[] = $this->inspectSegment($directory, self::PREVIOUS_LEDGER, 'Previous incident ledger');
            $checks[] = $this->writeProbe($directory);
        } else {
            foreach ([
                ['key' => 'current_ledger', 'label' => 'Current incident ledger'],
                ['key' => 'previous_ledger', 'label' => 'Previous incident ledger'],
                ['key' => 'write_probe', 'label' => 'Diagnostic write/read test'],
            ] as $check) {
                $checks[] = [
                    ...$check,
                    'status' => 'not_checked',
                    'message' => 'Not checked because the diagnostics folder is unavailable.',
                ];
            }
        }

        $statuses = array_column($checks, 'status');
        $overall = in_array('failed', $statuses, true)
            ? 'failed'
            : (in_array('attention', $statuses, true)
                ? 'attention'
                : (in_array('unknown', $statuses, true) ? 'unknown' : 'healthy'));

        return [
            'status' => $overall,
            'checked_at' => now()->toIso8601String(),
            'checks' => $checks,
        ];
    }

    private function inspectSegment(string $directory, string $filename, string $label): array
    {
        $path = $directory.DIRECTORY_SEPARATOR.$filename;

        if (! file_exists($path)) {
            return [
                'key' => $filename === self::ACTIVE_LEDGER ? 'current_ledger' : 'previous_ledger',
                'label' => $label,
                'status' => 'healthy',
                'message' => 'No records have been written to this segment yet.',
                'records' => 0,
                'malformed_lines' => 0,
                'size_bytes' => 0,
            ];
        }

        if (! is_file($path) || ! is_readable($path)) {
            return [
                'key' => $filename === self::ACTIVE_LEDGER ? 'current_ledger' : 'previous_ledger',
                'label' => $label,
                'status' => 'failed',
                'message' => 'The incident segment exists but cannot be read as a normal file.',
            ];
        }

        $records = 0;
        $malformed = 0;

        try {
            $file = new SplFileObject($path, 'r');

            while (! $file->eof()) {
                $line = $file->fgets();

                if (! is_string($line) || trim($line) === '') {
                    continue;
                }

                try {
                    $record = json_decode($line, true, 32, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $malformed++;

                    continue;
                }

                if (! is_array($record) || ! is_string($record['request_id'] ?? null)) {
                    $malformed++;

                    continue;
                }

                $records++;
            }
        } catch (Throwable) {
            return [
                'key' => $filename === self::ACTIVE_LEDGER ? 'current_ledger' : 'previous_ledger',
                'label' => $label,
                'status' => 'failed',
                'message' => 'Sentinel could not complete a read of this incident segment.',
            ];
        }

        return [
            'key' => $filename === self::ACTIVE_LEDGER ? 'current_ledger' : 'previous_ledger',
            'label' => $label,
            'status' => $malformed > 0 ? 'attention' : 'healthy',
            'message' => $malformed > 0
                ? 'Some lines cannot be read as incident records. Valid records remain available.'
                : 'The incident segment is readable.',
            'records' => $records,
            'malformed_lines' => $malformed,
            'size_bytes' => filesize($path) ?: 0,
        ];
    }

    private function writeProbe(string $directory): array
    {
        $path = null;
        $handle = false;
        $created = false;
        $writeReadPassed = false;
        $cleanupPassed = true;
        $failureMessage = 'Sentinel could not create and lock a temporary diagnostics file.';

        try {
            $path = $directory.DIRECTORY_SEPARATOR.'sentinel-health-'.bin2hex(random_bytes(8)).'.tmp';
            $handle = @fopen($path, 'x+b');

            if ($handle !== false) {
                $created = true;

                if (flock($handle, LOCK_EX)) {
                    $marker = 'zazu-sentinel-health-probe';
                    $written = fwrite($handle, $marker);
                    $flushed = fflush($handle);
                    rewind($handle);
                    $readBack = stream_get_contents($handle);
                    $writeReadPassed = $written === strlen($marker) && $flushed && $readBack === $marker;
                    $failureMessage = 'Sentinel could not verify a temporary write and read.';
                }
            }
        } catch (Throwable) {
            $failureMessage = 'Sentinel could not complete its temporary write/read test.';
        } finally {
            if (is_resource($handle)) {
                flock($handle, LOCK_UN);
                fclose($handle);
            }

            // Delete only a file this health check successfully created.
            if ($created && is_string($path) && file_exists($path)) {
                $cleanupPassed = @unlink($path);
            }
        }

        $passed = $writeReadPassed && $cleanupPassed;

        return [
            'key' => 'write_probe',
            'label' => 'Diagnostic write/read test',
            'status' => $passed ? 'healthy' : 'failed',
            'message' => ! $writeReadPassed
                ? $failureMessage
                : ($cleanupPassed
                    ? 'Temporary write and read passed; the probe file was removed.'
                    : 'The probe worked but Sentinel could not remove its temporary file.'),
        ];
    }
}
}
