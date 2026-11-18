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
            'fingerprint' => ['nullable', 'regex:/^ZAZU-[A-F0-9]{16}$/'],\n            'business_q' => ['nullable', 'string', 'max:120'],
        ]);

        $health = $healthService->inspect();
        $query = trim((string) ($filters['q'] ?? ''));
        $summaries = $query === '' ? $incidents->build(100) : $incidents->search($query, 100);
        $overview = $incidents->overview();

        $businessQuery = trim((string) ($filters['business_q'] ?? ''));\n        $businesses = Business::query()\n            ->with('license')\n            ->when($businessQuery !== '', fn ($query) => $query->where(function ($businessesQuery) use ($businessQuery): void {\n                $businessesQuery->where('name', 'like', '%'.$businessQuery.'%');\n                if (ctype_digit($businessQuery)) {\n                    $businessesQuery->orWhere('id', (int) $businessQuery);\n                }\n            }))\n            ->orderBy('name')\n            ->paginate(25, ['*'], 'business_page')\n            ->withQueryString();\n\n        $selected = $filters['fingerprint'] ?? null;
        $timeline = $selected ? $incidents->timeline($selected) : [];

        return view('admin.sentinel', [
            'health' => $health,
            'summaries' => $summaries,
            'timeline' => $timeline,
            'selectedFingerprint' => $selected,
            'searchQuery' => $query,
            'totalFingerprints' => $overview['fingerprints'],
            'totalOccurrences' => $overview['occurrences'],
            'latestIncident' => $overview['latest_incident'],\n            'businesses' => $businesses,\n            'businessQuery' => $businessQuery,\n            'licenceSigningConfigured' => trim((string) config('zazu.licensing.private_key_file', '')) !== '',\n            'issuedLicenseToken' => session('sentinel_issued_license_token'),\n            'issuedLicenseBusiness' => session('sentinel_issued_license_business'),\n            'issuedLicenseAction' => session('sentinel_issued_license_action'),
        ]);
    }
}
