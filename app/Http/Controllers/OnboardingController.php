<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Support\Audit;
use App\Support\CurrentBusiness;
use App\Support\ExperienceLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function index(Request $request, ExperienceLevel $levels): View
    {
        $business = $this->currentBusiness($request);
        $business->loadMissing('taxProfile');

        $catalogueStatus = $this->setupStatus(
            $business->catalogue_setup_completed_at,
            $business->catalogue_setup_skipped_at,
            $business->capabilities()->exists()
        );

        $businessStatus = $this->setupStatus(
            $business->business_setup_completed_at,
            $business->business_setup_skipped_at,
            filled($business->email) || filled($business->phone) || filled($business->address)
        );

        $taxStatus = $business->taxProfile ? 'in_progress' : 'not_started';
        $experienceLevel = $levels->selected($request->user(), $business);
        $experienceStatus = $experienceLevel ? 'completed' : 'not_started';

        return view('onboarding.index', compact(
            'business',
            'catalogueStatus',
            'businessStatus',
            'taxStatus',
            'experienceLevel',
            'experienceStatus'
        ));
    }

    public function catalogue(Request $request): View|RedirectResponse
    {
        $business = $this->currentBusiness($request);

        if ($business->catalogue_setup_completed_at) {
            return redirect()->route('onboarding.experience');
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
        $business = $this->currentBusiness($request);

        $categories = array_keys(config('zazu.service_categories'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capability_type' => ['nullable', 'in:service,rental,product,package,other'],
            'pricing_basis' => ['nullable', 'in:custom,fixed,per_unit,per_person,per_hour,per_day'],
            'default_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'default_unit' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100', 'in:' . implode(',', $categories)],
            'description' => ['nullable', 'string'],
        ]);

        $capability = BusinessCapability::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'capability_type' => $validated['capability_type'] ?? 'service',
            'pricing_basis' => $validated['pricing_basis'] ?? 'custom',
            'default_price' => $validated['default_price'] ?? null,
            'default_unit' => $validated['default_unit'] ?? null,
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'] ?? null,
            'currency' => $business->currency ?? 'ZAR',
            'is_active' => true,
        ]);

        Audit::record('onboarding.catalogue.item_added', $capability, ['name' => $capability->name, 'type' => $capability->capability_type], $business->id);

        if ($request->boolean('add_another')) {
            $business->update(['catalogue_setup_skipped_at' => null]);

            return redirect()
                ->route('onboarding.catalogue')
                ->with('success', 'Added to your catalogue. Add another service, product or rental.');
        }

        $business->update([
            'catalogue_setup_completed_at' => now(),
            'catalogue_setup_skipped_at' => null,
        ]);

        return redirect()
            ->route('onboarding.experience')
            ->with('success', 'Services saved. Choose how much of Zazu you want surfaced, then continue setup.');
    }

    public function finishCatalogue(Request $request): RedirectResponse
    {
        $business = $this->currentBusiness($request);
        $business->update([
            'catalogue_setup_completed_at' => now(),
            'catalogue_setup_skipped_at' => null,
        ]);

        Audit::record('onboarding.catalogue.completed', $business, ['capability_count' => $business->capabilities()->count()], $business->id);

        return redirect()
            ->route('onboarding.experience')
            ->with('success', 'Services setup saved. Choose your workspace experience level.');
    }

    public function skipCatalogue(Request $request): RedirectResponse
    {
        $business = $this->currentBusiness($request);
        $business->update([
            'catalogue_setup_completed_at' => null,
            'catalogue_setup_skipped_at' => now(),
        ]);

        return redirect()
            ->route('onboarding.experience')
            ->with('info', 'Services setup was deferred. You can resume it from Setup Centre or Services & prices.');
    }

    public function experience(Request $request, ExperienceLevel $levels): View|RedirectResponse
    {
        $business = $this->currentBusiness($request);

        if ($levels->selected($request->user(), $business)) {
            return redirect()->route('onboarding.business');
        }

        return view('onboarding.experience', [
            'business' => $business,
            'options' => $levels->options(),
        ]);
    }

    public function storeExperience(Request $request, ExperienceLevel $levels): RedirectResponse
    {
        $business = $this->currentBusiness($request);
        $validated = $request->validate([
            'experience_level' => ['required', 'in:basic,intermediate,advanced'],
        ]);

        $levels->selected($request->user(), $business);
        $request->user()->businesses()->updateExistingPivot($business->id, [
            'experience_level' => $validated['experience_level'],
        ]);

        Audit::record('onboarding.experience_level_selected', $business, [
            'experience_level' => $validated['experience_level'],
        ], $business->id);

        return redirect()
            ->route('onboarding.business')
            ->with('success', 'Experience level saved. Now add your business details.');
    }

    public function business(Request $request, ExperienceLevel $levels): View|RedirectResponse
    {
        $business = $this->currentBusiness($request);

        if (!$levels->selected($request->user(), $business)) {
            return redirect()->route('onboarding.experience');
        }

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
        $business = $this->currentBusiness($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'in:' . implode(',', array_keys(config('zazu.currencies')))],
        ]);

        if (($validated['currency'] ?? null) === null) {
            unset($validated['currency']);
        }

        $business->update($validated + [
            'business_setup_completed_at' => now(),
            'business_setup_skipped_at' => null,
        ]);

        Audit::record('onboarding.business.completed', $business, ['business_name' => $business->name], $business->id);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Business profile saved. Your Zazu workspace is ready.');
    }

    public function skipBusiness(Request $request): RedirectResponse
    {
        $business = $this->currentBusiness($request);
        $business->update([
            'business_setup_completed_at' => null,
            'business_setup_skipped_at' => now(),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('info', 'Business details were deferred. You can resume setup from Setup Centre or Settings.');
    }

    private function setupStatus($completedAt, $skippedAt, bool $hasData): string
    {
        if ($completedAt) {
            return 'completed';
        }

        if ($skippedAt) {
            return 'deferred';
        }

        return $hasData ? 'in_progress' : 'not_started';
    }

    private function currentBusiness(Request $request): Business
    {
        return app(CurrentBusiness::class)->model($request->user());
    }
}
