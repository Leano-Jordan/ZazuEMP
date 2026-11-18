<?php

namespace App\Http\Controllers;

use App\Support\ZazuIncidentSummary;
use App\Support\ZazuSentinelHealth;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SentinelController extends Controller
{
    public function index(Request $request, ZazuIncidentSummary $incidents, ZazuSentinelHealth $healthService): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'fingerprint' => ['nullable', 'regex:/^ZAZU-[A-F0-9]{16}$/'],
        ]);

        $health = $healthService->inspect();
        $query = trim((string) ($filters['q'] ?? ''));
        $summaries = $query === '' ? $incidents->build(100) : $incidents->search($query, 100);
        $overview = $incidents->overview();

        $selected = $filters['fingerprint'] ?? null;
        $timeline = $selected ? $incidents->timeline($selected) : [];

        return view('admin.sentinel', [
            'health' => $health,
            'summaries' => $summaries,
            'timeline' => $timeline,
            'selectedFingerprint' => $selected,
            'searchQuery' => $query,
            'totalFingerprints' => $overview['fingerprints'],
            'totalOccurrences' => $overview['occurrences'],
            'latestIncident' => $overview['latest_incident'],
        ]);
    }
}
