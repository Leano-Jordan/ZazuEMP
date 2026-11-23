<?php

namespace App\Http\Controllers;

use App\Services\OnboardingProgressService;
use App\Support\Audit;
use App\Support\ExperienceLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OnboardingBusinessController extends Controller
{
    public function __construct(private readonly OnboardingProgressService $progress)
    {
    }

    public function business(Request $request, ExperienceLevel $levels): View|RedirectResponse
    {
        $business = $this->progress->currentBusiness($request);

        if (! $levels->selected($request->user(), $business)) {
            return redirect()->route('onboarding.experience');
        }

        if (! $business->catalogue_setup_completed_at && ! $business->catalogue_setup_skipped_at) {
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
        $business = $this->progress->currentBusiness($request);
        if ($redirect = $this->progress->businessDetailsPrerequisiteRedirect($request, $business)) {
            return $redirect;
        }

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
        $business = $this->progress->currentBusiness($request);
        if ($redirect = $this->progress->businessDetailsPrerequisiteRedirect($request, $business)) {
            return $redirect;
        }

        $business->update([
            'business_setup_completed_at' => null,
            'business_setup_skipped_at' => now(),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('info', 'Business details were deferred. You can resume setup from Setup Centre or Settings.');
    }
}
