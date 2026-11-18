<?php

namespace App\Http\Controllers;

use App\Models\PlatformLicenseCustomer;
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
            'business_q' => ['nullable', 'string', 'max:120'],
        ]);

        $health = $healthService->inspect();
        $query = trim((string) ($filters['q'] ?? ''));
        $summaries = $query === '' ? $incidents->build(100) : $incidents->search($query, 100);
        $overview = $incidents->overview();

        $businessQuery = trim((string) ($filters['business_q'] ?? ''));
        $customers = PlatformLicenseCustomer::query()
            ->when($businessQuery !== '', fn ($query) => $query->where(function ($businessesQuery) use ($businessQuery): void {
                $businessesQuery->where('business_name', 'like', '%'.$businessQuery.'%')
                    ->orWhere('contact_email', 'like', '%'.$businessQuery.'%')
                    ->orWhere('business_uuid', 'like', '%'.$businessQuery.'%');
                
            }))
            ->orderBy('business_name')
            ->paginate(25, ['*'], 'business_page')
            ->withQueryString();

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
            'customers' => $customers,
            'businessQuery' => $businessQuery,
            'licenceSigningConfigured' => trim((string) config('zazu.licensing.private_key_file', '')) !== '',
            'issuedLicenseToken' => session('sentinel_issued_license_token'),
            'issuedLicenseBusiness' => session('sentinel_issued_license_business'),
            'issuedLicenseAction' => session('sentinel_issued_license_action'),
        ]);
    }
}
