<?php

namespace App\Support;

use SplFileObject;
use Throwable;

final class ZazuIncidentSummary
{
    public function build(int $limit = 25): array
    {
        $groups = [];

        foreach ($this->ledgerFiles() as $path) {
            $this->aggregateFile($path, $groups);
        }

        $summaries = array_values($groups);
        usort($summaries, static fn (array $a, array $b): int =>
            ($b['occurrences'] <=> $a['occurrences']) ?: strcmp($b['last_seen'], $a['last_seen'])
        );

        return array_slice($summaries, 0, max(1, min($limit, 100)));
    }

    private function ledgerFiles(): array
    {
        $directory = storage_path('app/private/zazu-diagnostics');

        return [
            $directory.DIRECTORY_SEPARATOR.'incidents.jsonl',
            $directory.DIRECTORY_SEPARATOR.'incidents.jsonl.1',
        ];
    }

    private function aggregateFile(string $path, array &$groups): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        try {
            $file = new SplFileObject($path, 'r');

            while (!$file->eof()) {
                $record = $this->decode($file->fgets());

                if ($record !== null) {
                    $this->aggregateRecord($record, $groups);
                }
            }
        } catch (Throwable) {
            // A damaged segment must not prevent reading the other segment.
        }
    }

    private function decode(mixed $line): ?array
    {
        if (!is_string($line) || trim($line) === '') {
            return null;
        }

        try {
            $record = json_decode($line, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($record)) {
            return null;
        }

        $fingerprint = $record['failure_fingerprint'] ?? null;
        $requestId = $record['request_id'] ?? null;

        if (
            !is_string($fingerprint)
            || preg_match('/^ZAZU-[A-F0-9]{16}$/', $fingerprint) !== 1
            || !is_string($requestId)
            || !\Illuminate\Support\Str::isUuid($requestId)
        ) {
            return null;
        }

        return $record;
    }

    private function aggregateRecord(array $record, array &$groups): void
    {
        $fingerprint = $record['failure_fingerprint'];
        $timestamp = is_string($record['timestamp'] ?? null) ? $record['timestamp'] : 'unknown';

        if (!isset($groups[$fingerprint])) {
            $groups[$fingerprint] = [
                'fingerprint' => $fingerprint,
                'error_code' => $this->safeText($record['error_code'] ?? null),
                'failure_class' => $this->safeText($record['database_failure_class'] ?? null),
                'exception' => $this->safeText($record['exception_class'] ?? null),
                'source_file' => $this->safeText($record['source_file'] ?? null),
                'occurrences' => 0,
                'first_seen' => $timestamp,
                'last_seen' => $timestamp,
                'latest_reference' => $record['request_id'],
            ];
        }

        $groups[$fingerprint]['occurrences']++;

        if ($timestamp < $groups[$fingerprint]['first_seen']) {
            $groups[$fingerprint]['first_seen'] = $timestamp;
        }

        if ($timestamp >= $groups[$fingerprint]['last_seen']) {
            $groups[$fingerprint]['last_seen'] = $timestamp;
            $groups[$fingerprint]['latest_reference'] = $record['request_id'];
        }
    }

    private function safeText(mixed $value): string
    {
        if (!is_string($value)) {
            return 'unknown';
        }

        return mb_substr(preg_replace('/[^A-Za-z0-9_.@-]/', '', $value) ?? '', 0, 120);
    }

    /**
     * Return safe occurrence details for one validated fingerprint.
     */
    public function timeline(string $fingerprint, int $limit = 100): array
    {
        if (preg_match('/^ZAZU-[A-F0-9]{16}$/', $fingerprint) !== 1) {
            return [];
        }

        $records = [];

        foreach ($this->ledgerFiles() as $path) {
            $this->collectTimeline($path, $fingerprint, $records);
        }

        usort($records, static fn (array $a, array $b): int => strcmp($b['timestamp'], $a['timestamp']));

        return array_slice($records, 0, max(1, min($limit, 500)));
    }

    private function collectTimeline(string $path, string $fingerprint, array &$records): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        try {
            $file = new SplFileObject($path, 'r');

            while (!$file->eof()) {
                $record = $this->decode($file->fgets());

                if ($record === null || $record['failure_fingerprint'] !== $fingerprint) {
                    continue;
                }

                $records[] = [
                    'request_id' => $record['request_id'],
                    'timestamp' => is_string($record['timestamp'] ?? null) ? $record['timestamp'] : 'unknown',
                    'error_code' => $this->safeText($record['error_code'] ?? null),
                    'exception' => $this->safeText($record['exception_class'] ?? null),
                    'source_file' => $this->safeText($record['source_file'] ?? null),
                    'component' => $this->safeText($record['originating_component'] ?? null),
                    'database_driver' => $this->safeText($record['database_driver'] ?? null),
                    'database_sqlstate' => $this->safeText($record['database_sqlstate'] ?? null),
                    'database_operation' => $this->safeText($record['database_operation'] ?? null),
                    'database_failure_class' => $this->safeText($record['database_failure_class'] ?? null),
                ];
            }
        } catch (Throwable) {
            // Continue with the other ledger segment if one is damaged.
        }
    }

}
