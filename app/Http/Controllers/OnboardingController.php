<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function catalogue(Request $request): View|RedirectResponse
    {
        $business = $this->business($request);

        if ($business->catalogue_setup_completed_at) {
            return redirect()->route('onboarding.business');
        }

        return view('onboarding.catalogue', [
            'business' => $business,
            'capabilities' => $business->capabilities()->latest()->get(),
            'serviceCategories' => array_keys(config('zazu.service_categories')),
            'currencies' => config('zazu.currencies'),
            'defaultCurrency' => $business->currency ?? 'ZAR',
        ]);
    }

    public function storeCatalogue(Request $request): RedirectResponse
    {
        $business = $this->business($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capability_type' => ['required', 'in:service,rental,product,package,other'],
            'pricing_basis' => ['required', 'in:custom,fixed,per_unit,per_person,per_hour,per_day'],
            'default_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'currency' => ['required', 'in:' . implode(',', array_keys(config('zazu.currencies')))],
            'default_unit' => ['nullable', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        BusinessCapability::create([
            ...$validated,
            'business_id' => $business->id,
            'is_active' => true,
        ]);

        return redirect()
            ->route('onboarding.catalogue')
            ->with('success', 'Added to your catalogue. Add another item or continue to business details.');
    }

    public function finishCatalogue(Request $request): RedirectResponse
    {
        $business = $this->business($request);
        $business->update(['catalogue_setup_completed_at' => now()]);

        return redirect()
            ->route('onboarding.business')
            ->with('success', 'Catalogue setup saved. Now add your business details.');
    }

    public function skipCatalogue(Request $request): RedirectResponse
    {
        $business = $this->business($request);
        $business->update(['catalogue_setup_completed_at' => now()]);

        return redirect()
            ->route('onboarding.business')
            ->with('info', 'You can add products and services later from Services & prices.');
    }

    public function business(Request $request): View|RedirectResponse
    {
        $business = $this->business($request);

        if ($business->business_setup_completed_at) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.business', [
            'business' => $business,
            'currencies' => config('zazu.currencies'),
        ]);
    }

    public function storeBusiness(Request $request): RedirectResponse
    {
        $business = $this->business($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'currency' => ['required', 'in:' . implode(',', array_keys(config('zazu.currencies')))],
        ]);

        $business->update($validated + ['business_setup_completed_at' => now()]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Business profile saved. Your Zazu workspace is ready.');
    }

    public function skipBusiness(Request $request): RedirectResponse
    {
        $business = $this->business($request);
        $business->update(['business_setup_completed_at' => now()]);

        return redirect()
            ->route('dashboard')
            ->with('info', 'Business details can be completed later from Settings.');
    }

    private function business(Request $request): Business
    {
        return app(CurrentBusiness::class)->model($request->user());
    }
}