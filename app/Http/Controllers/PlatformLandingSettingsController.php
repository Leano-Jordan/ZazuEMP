<?php

namespace App\Http\Controllers;

use App\Services\LandingMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformLandingSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.landing-settings', [
            'settings' => app(LandingMediaService::class)->current(),
            'library' => config('zazu.landing_image_library', []),
        ]);
    }

    public function update(Request $request, LandingMediaService $landingMedia): RedirectResponse
    {
        $library = config('zazu.landing_image_library', []);

        $validated = $request->validate([
            'hero_image' => ['required', 'string', 'in:'.implode(',', array_keys($library))],
            'operations_image' => ['required', 'string', 'in:'.implode(',', array_keys($library))],
            'resources_image' => ['required', 'string', 'in:'.implode(',', array_keys($library))],
            'control_image' => ['required', 'string', 'in:'.implode(',', array_keys($library))],
        ]);

        $landingMedia->save($validated);

        return redirect()->route('admin.landing.settings')->with('success', 'Landing page media settings saved.');
    }
}
