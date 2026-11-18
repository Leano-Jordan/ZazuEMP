<?php

namespace App\Support;

final class ZazuSystemHealth
{
    public function __construct(
        private readonly ZazuDatabaseHealth $database,
        private readonly ZazuLocalEnvironmentHealth $local,
        private readonly ZazuSentinelHealth $sentinel,
    ) {
    }

    public function inspect(): array
    {
        $sentinel = $this->sentinel->inspect();
        $checks = [
            ...$this->database->inspect(),
            ...$this->local->inspect(),
            ...$sentinel['checks'],
        ];

        return [
            'status' => $this->overallStatus($checks),
            'checked_at' => now()->toIso8601String(),
            'checks' => $checks,
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
