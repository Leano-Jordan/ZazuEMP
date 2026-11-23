<?php

namespace App\Services;

use App\Models\Business;
use App\Support\CurrentBusiness;
use App\Support\ExperienceLevel;
use App\Support\NicheFocus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OnboardingProgressService
{
    public function __construct(
        private readonly CurrentBusiness $currentBusiness,
        private readonly ExperienceLevel $levels,
        private readonly NicheFocus $niches,
    ) {
    }

    public function currentBusiness(Request $request): Business
    {
        return $this->currentBusiness->model($request->user());
    }

    public function setupStatus(mixed $completedAt, mixed $skippedAt, bool $hasData): string
    {
        if ($completedAt) {
            return 'completed';
        }

        if ($skippedAt) {
            return 'deferred';
        }

        return $hasData ? 'in_progress' : 'not_started';
    }

    public function focusPrerequisiteRedirect(Request $request, Business $business): ?RedirectResponse
    {
        if (! $this->levels->selected($request->user(), $business)
            || ! $this->niches->selected($request->user(), $business)) {
            return redirect()->route('onboarding.experience');
        }

        return null;
    }

    public function businessDetailsPrerequisiteRedirect(Request $request, Business $business): ?RedirectResponse
    {
        if ($redirect = $this->focusPrerequisiteRedirect($request, $business)) {
            return $redirect;
        }

        if (! $business->catalogue_setup_completed_at && ! $business->catalogue_setup_skipped_at) {
            return redirect()->route('onboarding.catalogue');
        }

        return null;
    }
}
