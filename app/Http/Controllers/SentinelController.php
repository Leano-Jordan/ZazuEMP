<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\FounderSupportSession;
use App\Models\PlatformLicenseCustomer;
use App\Support\ZazuIncidentSummary;
use App\Support\ZazuSentinelHealth;
use App\Support\ZazuBackupStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SentinelController extends Controller
{
    public function index(Request $request, ZazuIncidentSummary $incidents, ZazuSentinelHealth $healthService, ZazuBackupStatus $backupStatus): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'fingerprint' => ['nullable', 'regex:/^ZAZU-[A-F0-9]{16}$/'],
            'business_q' => ['nullable', 'string', 'max:120'],
            'account_q' => ['nullable', 'string', 'max:120'],
        ]);

        $health = $healthService->inspect();
        $query = trim((string) ($filters['q'] ?? ''));
        $summaries = $query === '' ? $incidents->build(100) : $incidents->search($query, 100);
        $overview = $incidents->overview();

        $businessQuery = trim((string) ($filters['business_q'] ?? ''));
        $customers = $this->licenseCustomers($businessQuery);

        $accountQuery = trim((string) ($filters['account_q'] ?? ''));
        $platformBusinesses = $this->platformBusinesses($accountQuery);

        $selected = $filters['fingerprint'] ?? null;
        $timeline = $selected ? $incidents->timeline($selected) : [];

        return view('admin.sentinel', [
            'health' => $health,
            'backupStatus' => $backupStatus->inspect(),
            'summaries' => $summaries,
            'timeline' => $timeline,
            'selectedFingerprint' => $selected,
            'searchQuery' => $query,
            'totalFingerprints' => $overview['fingerprints'],
            'totalOccurrences' => $overview['occurrences'],
            'latestIncident' => $overview['latest_incident'],
            'customers' => $customers,
            'businessQuery' => $businessQuery,
            'platformBusinesses' => $platformBusinesses,
            'accountQuery' => $accountQuery,
            'licenceSigningConfigured' => trim((string) config('zazu.licensing.private_key_file', '')) !== '',
            'issuedLicenseToken' => session('sentinel_issued_license_token'),
            'issuedLicenseBusiness' => session('sentinel_issued_license_business'),
            'issuedLicenseAction' => session('sentinel_issued_license_action'),
        ]);
    }


    private function licenseCustomers(string $businessQuery)
    {
        return PlatformLicenseCustomer::query()
            ->when($businessQuery !== '', fn ($query) => $query->where(function ($businessesQuery) use ($businessQuery): void {
                $businessesQuery->where('business_name', 'like', '%'.$businessQuery.'%')
                    ->orWhere('contact_email', 'like', '%'.$businessQuery.'%')
                    ->orWhere('business_uuid', 'like', '%'.$businessQuery.'%');
            }))
            ->orderBy('business_name')
            ->paginate(25, ['*'], 'business_page')
            ->withQueryString();
    }

    private function platformBusinesses(string $accountQuery)
    {
        return Business::query()
            ->with(['users' => fn ($users) => $users->orderBy('name')])
            ->withCount(['users', 'customers', 'events'])
            ->when($accountQuery !== '', fn ($businesses) => $businesses->where(function ($builder) use ($accountQuery): void {
                $builder->where('name', 'like', '%'.$accountQuery.'%')
                    ->orWhere('email', 'like', '%'.$accountQuery.'%')
                    ->orWhereHas('users', fn ($users) => $users
                        ->where('name', 'like', '%'.$accountQuery.'%')
                        ->orWhere('email', 'like', '%'.$accountQuery.'%'));
            }))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'account_page')
            ->withQueryString();
    }

    public function showBusiness(Business $business): View
    {
        $business->load(['users' => fn ($users) => $users->orderBy('name')]);
        $business->loadCount(['users', 'customers', 'events']);
        $supportSessions = FounderSupportSession::query()
            ->with('actor:id,name,email')
            ->where('business_id', $business->id)
            ->latest('started_at')
            ->limit(10)
            ->get();

        return view('admin.sentinel-business', [
            'business' => $business,
            'supportSessions' => $supportSessions,
        ]);
    }
}
