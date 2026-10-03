<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Support\CurrentBusiness;
use App\Support\ExperienceLevel;
use App\Support\NicheFocus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExperiencePreferenceController extends Controller
{
    public function edit(Request $request, ExperienceLevel $levels, NicheFocus $niches): View
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        return view('preferences.experience', [
            'business' => $business,
            'level' => $levels->for($request->user(), $business),
            'options' => $levels->options(),
            'primaryNiche' => $niches->for($request->user(), $business),
            'nicheOptions' => $niches->options(),
        ]);
    }

    public function update(Request $request, ExperienceLevel $levels, NicheFocus $niches): RedirectResponse
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        $validated = $request->validate([
            'experience_level' => ['required', 'in:basic,intermediate,advanced'],
            'primary_niche' => ['nullable', 'string', 'in:'.implode(',', array_keys(config('zazu.niches', [])))],
        ]);

        $membership = $request->user()->businesses()->whereKey($business->id)->firstOrFail();
        $previous = $levels->selected($request->user(), $business);
        $previousNiche = $niches->selected($request->user(), $business);
        $primaryNiche = $validated['primary_niche'] ?? $previousNiche ?? NicheFocus::DEFAULT;

        $request->user()->businesses()->updateExistingPivot($business->id, [
            'experience_level' => $validated['experience_level'],
            'primary_niche' => $primaryNiche,
        ]);

        Audit::record('workspace.experience_level_changed', $business, [
            'from' => $previous,
            'to' => $validated['experience_level'],
            'niche_from' => $previousNiche,
            'niche_to' => $primaryNiche,
        ], $business->id);

        return redirect()
            ->route('preferences.experience')
            ->with('success', 'Workspace focus updated. Your Zazu presentation will adapt to the selected focus and level.');
    }
}
