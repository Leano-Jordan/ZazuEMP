<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Support\Audit;
use App\Support\CurrentBusiness;
use App\Support\ExperienceLevel;
use App\Support\NicheFocus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function index(Request $request, ExperienceLevel $levels, NicheFocus $niches): View
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
        $selectedExperienceLevel = $levels->selected($request->user(), $business);
        $experienceLevel = $selectedExperienceLevel
            ? $levels->for($request->user(), $business)
            : null;
        $experienceStatus = $selectedExperienceLevel ? 'completed' : 'not_started';
        $nicheFocus = $niches->selected($request->user(), $business);
        $nicheStatus = $nicheFocus ? 'completed' : 'not_started';
        $nicheDefinition = $niches->definition($nicheFocus ?? NicheFocus::DEFAULT);
        $activeCapabilities = $business->capabilities()->where('is_active', true)->count();
        $hiddenCapabilities = $business->capabilities()->where('is_active', false)->count();
        $activeCapabilityTypes = $business->capabilities()
            ->where('is_active', true)
            ->selectRaw('capability_type, COUNT(*) as total')
            ->groupBy('capability_type')
            ->pluck('total', 'capability_type');
        $capabilityTypeSummary = $activeCapabilityTypes
            ->map(fn ($count, $type) => ucfirst($type).': '.$count)
            ->implode(' · ');

        return view('onboarding.index', compact(
            'business',
            'catalogueStatus',
            'businessStatus',
            'taxStatus',
            'experienceLevel',
            'experienceStatus',
            'nicheFocus',
            'nicheStatus',
            'nicheDefinition',
            'activeCapabilities',
            'hiddenCapabilities',
            'capabilityTypeSummary'
        ));
    }

    public function catalogue(Request $request, ExperienceLevel $levels, NicheFocus $niches): View|RedirectResponse
    {
        $business = $this->currentBusiness($request);

        if (!$levels->selected($request->user(), $business) || !$niches->selected($request->user(), $business)) {
            return redirect()->route('onboarding.experience');
        }

        if ($business->catalogue_setup_completed_at || $business->catalogue_setup_skipped_at) {
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
            ->route('onboarding.business')
            ->with('success', 'Your offerings are saved. Add your business details to finish setup.');
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
            ->route('onboarding.business')
            ->with('success', 'Your offerings setup is saved. Add your business details to finish setup.');
    }

    public function skipCatalogue(Request $request): RedirectResponse
    {
        $business = $this->currentBusiness($request);
        $business->update([
            'catalogue_setup_completed_at' => null,
            'catalogue_setup_skipped_at' => now(),
        ]);

        return redirect()
            ->route('onboarding.business')
            ->with('info', 'Services setup was deferred. You can resume it from Setup Centre or Services & prices.');
    }

    public function experience(Request $request, ExperienceLevel $levels, NicheFocus $niches): View|RedirectResponse
    {
        $business = $this->currentBusiness($request);
        $storedExperienceLevel = $levels->selected($request->user(), $business);
        $experienceLevel = $storedExperienceLevel ? $levels->for($request->user(), $business) : null;
        $primaryNiche = $niches->selected($request->user(), $business);
        $isFounder = $levels->isPlatformAdmin($request->user());
        $maximumLevel = $levels->maximumFor($business, null, $request->user());

        if ($experienceLevel && $primaryNiche) {
            return redirect()->route(
                $business->catalogue_setup_completed_at || $business->catalogue_setup_skipped_at
                    ? 'onboarding.business'
                    : 'onboarding.catalogue'
            );
        }

        return view('onboarding.experience', [
            'business' => $business,
            'options' => $levels->options(),
            'availableLevels' => $levels->availableLevels($business, null, $request->user()),
            'maximumLevel' => $maximumLevel,
            'maximumLevelLabel' => $levels->label($maximumLevel),
            'isFounder' => $isFounder,
            'isTrial' => ! $isFounder && $levels->isTrial($business),
            'trialEndsAt' => $levels->trialEndsAt($business),
            'nicheOptions' => $niches->options(),
            'selectedNiche' => $primaryNiche,
            'selectedFocuses' => $niches->selectedMany($request->user(), $business),
            'selectedLevel' => $experienceLevel,
        ]);
    }

    public function storeExperience(Request $request, ExperienceLevel $levels, NicheFocus $niches): RedirectResponse
    {
        $business = $this->currentBusiness($request);
        $validated = $request->validate([
            'experience_level' => ['required', 'in:basic,intermediate,advanced'],
            'primary_niche' => ['nullable', 'string', 'in:'.implode(',', array_keys(config('zazu.niches', [])))],
            'business_focuses' => ['nullable', 'array', 'max:10'],
            'business_focuses.*' => ['string', 'distinct', 'in:'.implode(',', array_keys(config('zazu.niches', [])))],
        ]);

        if (! $levels->canSelect($business, $validated['experience_level'], null, $request->user())) {
            $maximum = $levels->label($levels->maximumFor($business, null, $request->user()));

            return back()->withErrors([
                'experience_level' => "This workspace is on {$maximum}. An active higher-tier entitlement is required for that experience.",
            ])->withInput();
        }

        $focuses = $request->boolean('business_focuses_present')
            ? ($validated['business_focuses'] ?? [])
            : ($validated['business_focuses'] ?? $niches->selectedMany($request->user(), $business));
        $primaryNiche = $validated['primary_niche']
            ?? $niches->selected($request->user(), $business)
            ?? ($focuses[0] ?? NicheFocus::DEFAULT);
        if (!in_array($primaryNiche, $focuses, true)) {
            array_unshift($focuses, $primaryNiche);
        }
        $focuses = array_values(array_unique($focuses));

        $request->user()->businesses()->updateExistingPivot($business->id, [
            'experience_level' => $validated['experience_level'],
            'primary_niche' => $primaryNiche,
            'business_focuses' => json_encode($focuses, JSON_THROW_ON_ERROR),
        ]);

        Audit::record('onboarding.experience_level_selected', $business, [
            'experience_level' => $validated['experience_level'],
            'primary_niche' => $primaryNiche,
            'business_focuses' => $focuses,
        ], $business->id);

        return redirect()
            ->route('onboarding.catalogue')
            ->with('success', 'Business focus saved. Now add the services, products and rentals you offer.');
    }

    public function business(Request $request, ExperienceLevel $levels): View|RedirectResponse
    {
        $business = $this->currentBusiness($request);

        if (!$levels->selected($request->user(), $business)) {
            return redirect()->route('onboarding.experience');
        }

        if (!$business->catalogue_setup_completed_at && !$business->catalogue_setup_skipped_at) {
            return redirect()->route('onboarding.catalogue');
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
