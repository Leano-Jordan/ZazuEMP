<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessTaxProfile;
use App\Models\TaxRate;
use App\Support\CurrentBusiness;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        $business->loadMissing(['taxProfile', 'taxRates' => fn ($query) => $query->orderByDesc('effective_from')]);

        return view('settings.index', [
            'business' => $business,
            'currencies' => config('zazu.currencies'),
            'taxRegimes' => config('zazu.tax.regimes'),
            'vatStatuses' => config('zazu.tax.vat_statuses'),
            'taxRates' => $business->taxRates,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $business = app(CurrentBusiness::class)->model($request->user());
        $request->merge(['currency' => strtoupper((string) $request->input('currency'))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'currency' => ['required', Rule::in(array_keys(config('zazu.currencies')))],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'dashboard_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'wallpaper' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_dashboard_image' => ['nullable', 'boolean'],
            'remove_wallpaper' => ['nullable', 'boolean'],

            'legal_name' => ['nullable', 'string', 'max:255'],
            'trading_name' => ['nullable', 'string', 'max:255'],
            'registration_type' => ['nullable', 'string', 'max:50'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'tax_regime' => ['required', Rule::in(array_keys(config('zazu.tax.regimes')))],
            'income_tax_number' => ['nullable', 'string', 'max:100'],
            'vat_status' => ['required', Rule::in(array_keys(config('zazu.tax.vat_statuses')))],
            'vat_number' => ['nullable', 'required_if:vat_status,registered', 'string', 'max:100'],
            'paye_number' => ['nullable', 'string', 'max:100'],
            'uif_number' => ['nullable', 'string', 'max:100'],
            'sdl_number' => ['nullable', 'string', 'max:100'],
            'financial_year_end' => ['nullable', 'date'],
            'representative_name' => ['nullable', 'string', 'max:255'],
            'representative_email' => ['nullable', 'email', 'max:255'],
            'representative_phone' => ['nullable', 'string', 'max:50'],
            'tcs_reference' => ['nullable', 'string', 'max:100'],
            'tcs_pin' => ['nullable', 'string', 'max:100'],
            'tcs_pin_expires_at' => ['nullable', 'date'],
            'default_vat_rate' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'tax_effective_from' => ['required', 'date'],
            'food_handling' => ['nullable', 'boolean'],
            'employees' => ['nullable', 'boolean'],
            'government_supply' => ['nullable', 'boolean'],
            'tendering' => ['nullable', 'boolean'],
            'regulated_activity' => ['nullable', 'boolean'],
            'compliance_notes' => ['nullable', 'string'],
        ]);

        $newPaths = [];
        $oldPaths = [];

        foreach ([
            'logo' => 'logo_path',
            'dashboard_image' => 'dashboard_image_path',
            'wallpaper' => 'wallpaper_path',
        ] as $upload => $column) {
            if ($request->hasFile($upload)) {
                $newPaths[$column] = $request->file($upload)->store('business-branding', 'public');
                $oldPaths[$column] = $business->{$column};
            } elseif ($request->boolean('remove_' . $upload)) {
                $newPaths[$column] = null;
                $oldPaths[$column] = $business->{$column};
            }
        }

        try {
            DB::transaction(function () use ($business, $validated, $request, $newPaths): void {
                $business->update([
                    'name' => trim($validated['name']),
                    'currency' => strtoupper($validated['currency']),
                    'tax_number' => $validated['income_tax_number'] ?? $business->tax_number,
                    ...$newPaths,
                ]);

                $profile = $business->taxProfile()->firstOrNew([
                    'business_id' => $business->id,
                ]);

                $profile->fill([
                    'legal_name' => $validated['legal_name'] ?? null,
                    'trading_name' => $validated['trading_name'] ?? null,
                    'registration_type' => $validated['registration_type'] ?? null,
                    'registration_number' => $validated['registration_number'] ?? null,
                    'tax_regime' => $validated['tax_regime'],
                    'income_tax_number' => $validated['income_tax_number'] ?? null,
                    'vat_status' => $validated['vat_status'],
                    'vat_number' => $validated['vat_number'] ?? null,
                    'paye_number' => $validated['paye_number'] ?? null,
                    'uif_number' => $validated['uif_number'] ?? null,
                    'sdl_number' => $validated['sdl_number'] ?? null,
                    'financial_year_end' => $validated['financial_year_end'] ?? null,
                    'representative_name' => $validated['representative_name'] ?? null,
                    'representative_email' => $validated['representative_email'] ?? null,
                    'representative_phone' => $validated['representative_phone'] ?? null,
                    'tcs_reference' => $validated['tcs_reference'] ?? null,
                    'tcs_pin_expires_at' => $validated['tcs_pin_expires_at'] ?? null,
                    'activity_flags' => [
                        'food_handling' => $request->boolean('food_handling'),
                        'employees' => $request->boolean('employees'),
                        'government_supply' => $request->boolean('government_supply'),
                        'tendering' => $request->boolean('tendering'),
                        'regulated_activity' => $request->boolean('regulated_activity'),
                    ],
                    'compliance_notes' => $validated['compliance_notes'] ?? null,
                ]);

                if (filled($validated['tcs_pin'] ?? null)) {
                    $profile->tcs_pin = $validated['tcs_pin'];
                }

                $profile->save();

                $this->syncTaxDefaults(
                    $business,
                    $profile,
                    (string) $validated['default_vat_rate'],
                    Carbon::parse($validated['tax_effective_from'])
                );
            });
        } catch (\Throwable $e) {
            foreach ($newPaths as $path) {
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }

            throw $e;
        }

        foreach ($oldPaths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Business, tax and compliance settings saved.');
    }

    private function syncTaxDefaults(Business $business, BusinessTaxProfile $profile, string $requestedRate, Carbon $effectiveFrom): void
    {
        $rate = $this->normalizeDecimal($requestedRate);

        TaxRate::query()
            ->where('business_id', $business->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $defaultCode = match ($profile->vat_status) {
            'registered' => 'VAT_STANDARD',
            'exempt' => 'VAT_EXEMPT',
            default => 'NO_VAT',
        };

        $existing = TaxRate::query()
            ->where('business_id', $business->id)
            ->where('code', $defaultCode)
            ->whereNull('effective_to')
            ->latest('effective_from')
            ->first();

        $defaultRate = $profile->vat_status === 'registered'
            ? $rate
            : '0.00';

        if ($existing && (string) $existing->rate === $defaultRate && $existing->effective_from <= $effectiveFrom) {
            $existing->update([
                'name' => $profile->vat_status === 'registered' ? 'Standard VAT' : ($profile->vat_status === 'exempt' ? 'VAT exempt' : 'No VAT'),
                'treatment' => $profile->vat_status === 'registered' ? 'standard' : ($profile->vat_status === 'exempt' ? 'exempt' : 'out_of_scope'),
                'is_default' => true,
                'is_active' => true,
                'source_reference' => $profile->vat_status === 'registered' ? 'SARS VAT guidance; reviewed by Zazu configuration' : 'Business tax profile',
            ]);
        } else {
            if ($existing && $existing->effective_from < $effectiveFrom) {
                $existing->update(['effective_to' => $effectiveFrom->copy()->subDay()->toDateString()]);
            }

            TaxRate::create([
                'business_id' => $business->id,
                'name' => $profile->vat_status === 'registered' ? 'Standard VAT' : ($profile->vat_status === 'exempt' ? 'VAT exempt' : 'No VAT'),
                'code' => $defaultCode,
                'tax_type' => 'vat',
                'treatment' => $profile->vat_status === 'registered' ? 'standard' : ($profile->vat_status === 'exempt' ? 'exempt' : 'out_of_scope'),
                'rate' => $defaultRate,
                'effective_from' => $effectiveFrom->toDateString(),
                'is_default' => true,
                'is_active' => true,
                'source_reference' => $profile->vat_status === 'registered' ? 'SARS VAT guidance; reviewed by Zazu configuration' : 'Business tax profile',
            ]);
        }

        foreach ([
            ['code' => 'VAT_ZERO', 'name' => 'Zero-rated', 'treatment' => 'zero_rated'],
            ['code' => 'VAT_EXEMPT', 'name' => 'Exempt', 'treatment' => 'exempt'],
        ] as $supportRate) {
            TaxRate::query()->firstOrCreate(
                ['business_id' => $business->id, 'code' => $supportRate['code']],
                [
                    'name' => $supportRate['name'],
                    'tax_type' => 'vat',
                    'treatment' => $supportRate['treatment'],
                    'rate' => '0.00',
                    'effective_from' => $effectiveFrom->toDateString(),
                    'is_default' => false,
                    'is_active' => true,
                    'source_reference' => 'SARS VAT guidance; use only where the supply qualifies for the treatment',
                ]
            );
        }
    }

    private function normalizeDecimal(string $value): string
    {
        $value = trim($value);

        if (!str_contains($value, '.')) {
            return $value . '.00';
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');

        return $whole . '.' . str_pad(substr($fraction, 0, 2), 2, '0');
    }
}