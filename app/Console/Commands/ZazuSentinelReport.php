<?php

namespace App\Console\Commands;

use App\Support\ZazuIncidentSummary;
use Illuminate\Console\Command;

final class ZazuSentinelReport extends Command
{
    protected $signature = 'zazu:sentinel {--limit=25 : Number of failure fingerprints to show} {--json : Output machine-readable JSON}';

    protected $description = 'Summarize recurring Zazu Sentinel incidents from the private incident ledger';

    public function handle(ZazuIncidentSummary $summary): int
    {
        $limit = (int) $this->option('limit');
        $incidents = $summary->build($limit);

        if ($this->option('json')) {
            $this->line(json_encode($incidents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($incidents === []) {
            $this->info('Sentinel has no recorded incidents in its local ledger.');

            return self::SUCCESS;
        }

        $this->table(
            ['Fingerprint', 'Code', 'Failure class', 'Count', 'First seen', 'Last seen', 'Latest reference'],
            array_map(static fn (array $incident): array => [
                $incident['fingerprint'],
                $incident['error_code'],
                $incident['failure_class'],
                $incident['occurrences'],
                $incident['first_seen'],
                $incident['last_seen'],
                $incident['latest_reference'],
            ], $incidents),
        );

        return self::SUCCESS;
    }
}
