<?php

namespace App\Support;

final class ZazuSentinelHealth
{
    public function __construct(
        private readonly ?string $directory = null,
        private readonly ?ZazuSentinelLedgerHealth $ledgerHealth = null,
    ) {
    }

    /**
     * Inspect Sentinel's local evidence path without querying the business database.
     */
    public function inspect(): array
    {
        $directory = $this->directory ?? storage_path('app/private/zazu-diagnostics');
        $ledgerHealth = $this->ledgerHealth ?? new ZazuSentinelLedgerHealth();
        $checks = [
            $this->directoryCheck($directory),
            ...$ledgerHealth->inspect($directory),
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
}
