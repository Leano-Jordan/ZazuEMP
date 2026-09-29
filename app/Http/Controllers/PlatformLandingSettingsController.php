<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlatformLandingSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = DB::table('platform_landing_settings')->first();

        return view('admin.landing-settings', [
            'settings' => $settings,
            'library' => config('zazu.landing_image_library', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $library = config('zazu.landing_image_library', []);

        $validated = $request->validate([
            'hero_image' => ['required', 'string', 'in:'.implode(',', array_keys($library))],
            'operations_image' => ['required', 'string', 'in:'.implode(',', array_keys($library))],
            'resources_image' => ['required', 'string', 'in:'.implode(',', array_keys($library))],
            'control_image' => ['required', 'string', 'in:'.implode(',', array_keys($library))],
        ]);

        $values = [
            'hero_image_path' => $library[$validated['hero_image']]['url'],
            'operations_image_path' => $library[$validated['operations_image']]['url'],
            'resources_image_path' => $library[$validated['resources_image']]['url'],
            'control_image_path' => $library[$validated['control_image']]['url'],
            'updated_at' => now(),
        ];

        DB::table('platform_landing_settings')->updateOrInsert(
            ['id' => 1],
            ['created_at' => now(), ...$values],
        );

        return redirect()->route('admin.landing.settings')->with('success', 'Landing page media settings saved.');
    }
}
