<?php

namespace App\\Http\\Controllers;

use App\\Support\\ZazuIncidentSummary;
use Illuminate\\Http\\Request;
use Illuminate\\View\\View;

final class SentinelController extends Controller
{
    public function index(Request $request, ZazuIncidentSummary $incidents): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'fingerprint' => ['nullable', 'regex:/^ZAZU-[A-F0-9]{16}$/'],
        ]);

        $all = $incidents->build(100);
        $query = trim((string) ($filters['q'] ?? ''));
        $summaries = $all;

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $summaries = array_values(array_filter($all, static function (array $item) use ($needle): bool {
                foreach (['fingerprint', 'error_code', 'failure_class', 'exception', 'source_file'] as $field) {
                    if (str_contains(mb_strtolower((string) $item[$field]), $needle)) {
                        return true;
                    }
                }

                return false;
            }));
        }

        $selected = $filters['fingerprint'] ?? null;
        $timeline = $selected ? $incidents->timeline($selected) : [];

        return view('admin.sentinel', [
            'summaries' => $summaries,
            'timeline' => $timeline,
            'selectedFingerprint' => $selected,
            'searchQuery' => $query,
            'totalFingerprints' => count($all),
            'totalOccurrences' => array_sum(array_column($all, 'occurrences')),
            'latestIncident' => $all[0]['last_seen'] ?? null,
        ]);
    }
}
