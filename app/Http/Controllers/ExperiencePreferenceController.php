<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Support\CurrentBusiness;
use App\Support\ExperienceLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExperiencePreferenceController extends Controller
{
    public function edit(Request $request, ExperienceLevel $levels): View
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        return view('preferences.experience', [
            'business' => $business,
            'level' => $levels->for($request->user(), $business),
            'options' => $levels->options(),
        ]);
    }

    public function update(Request $request, ExperienceLevel $levels): RedirectResponse
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        $validated = $request->validate([
            'experience_level' => ['required', 'in:basic,intermediate,advanced'],
        ]);

        $membership = $request->user()->businesses()->whereKey($business->id)->firstOrFail();
        $previous = $levels->selected($request->user(), $business);

        $request->user()->businesses()->updateExistingPivot($business->id, [
            'experience_level' => $validated['experience_level'],
        ]);

        Audit::record('workspace.experience_level_changed', $business, [
            'from' => $previous,
            'to' => $validated['experience_level'],
        ], $business->id);

        return redirect()
            ->route('preferences.experience')
            ->with('success', 'Workspace experience updated. Your Zazu presentation will adapt to the selected level.');
    }
}
