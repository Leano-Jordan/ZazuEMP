<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Support\CurrentBusiness;
use App\Support\ExperienceLevel;
use App\Support\NicheFocus;
use App\Support\SouthAfricaHolidayCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExperiencePreferenceController extends Controller
{
    public function edit(Request $request, ExperienceLevel $levels, NicheFocus $niches, SouthAfricaHolidayCalendar $holidays): View
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        return view('preferences.experience', [
            'business' => $business,
            'level' => $levels->for($request->user(), $business),
            'options' => $levels->options(),
            'primaryNiche' => $niches->for($request->user(), $business),
            'nicheOptions' => $niches->options(),
            'holidayCategories' => SouthAfricaHolidayCalendar::CATEGORIES,
            'holidayPreferences' => $this->holidayPreferences($request, $business),
        ]);
    }

    public function update(Request $request, ExperienceLevel $levels, NicheFocus $niches): RedirectResponse
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        $validated = $request->validate([
            'experience_level' => ['required', 'in:basic,intermediate,advanced'],
            'primary_niche' => ['nullable', 'string', 'in:'.implode(',', array_keys(config('zazu.niches', [])))],
            'holiday_categories' => ['nullable', 'array'],
            'holiday_categories.*' => ['string', 'in:public,muslim,hindu,christian,jewish,cultural'],
        ]);

        $membership = $request->user()->businesses()->whereKey($business->id)->firstOrFail();
        $previous = $levels->selected($request->user(), $business);
        $previousNiche = $niches->selected($request->user(), $business);
        $primaryNiche = $validated['primary_niche'] ?? $previousNiche ?? NicheFocus::DEFAULT;
        $holidayPreferences = array_fill_keys(array_keys(SouthAfricaHolidayCalendar::CATEGORIES), false);
        foreach ($validated['holiday_categories'] ?? [] as $category) {
            $holidayPreferences[$category] = true;
        }
        $holidayPreferences['public'] = true;

        $request->user()->businesses()->updateExistingPivot($business->id, [
            'experience_level' => $validated['experience_level'],
            'primary_niche' => $primaryNiche,
            'calendar_holiday_preferences' => json_encode($holidayPreferences),
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

    private function holidayPreferences(Request $request, $business): array
    {
        $membership = $request->user()->businesses()->whereKey($business->id)->firstOrFail();
        $stored = $membership->pivot->calendar_holiday_preferences ?? [];
        if (is_string($stored)) {
            $stored = json_decode($stored, true) ?: [];
        }

        return array_merge(
            array_fill_keys(array_keys(SouthAfricaHolidayCalendar::CATEGORIES), true),
            $stored,
            ['public' => true],
        );
    }
}
