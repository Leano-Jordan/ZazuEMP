<?php

namespace App\Http\Controllers;

use App\Services\LandingMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformLandingSettingsController extends Controller
{
    public function edit(LandingMediaService $landingMedia): View
    {
        return view('admin.landing-settings', [
            'settings' => $landingMedia->current(),
            'library' => $landingMedia->library(),
        ]);
    }

    public function update(Request $request, LandingMediaService $landingMedia): RedirectResponse
    {
        $library = $landingMedia->library();
        $maxFiles = (int) config('zazu.landing_media_uploads.max_files', 8);
        $maxKilobytes = (int) config('zazu.landing_media_uploads.max_kilobytes', 5120);

        $rules = [];
        foreach ([
            'hero_image',
            'operations_image',
            'resources_image',
            'control_image',
            'venue_image',
            'decor_image',
        ] as $field) {
            $rules[$field] = ['required', 'string', Rule::in(array_keys($library))];
        }

        $rules['uploads'] = ['sometimes', 'array', 'max:'.$maxFiles];
        $rules['uploads.*'] = [
            'file',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'mimetypes:image/jpeg,image/png,image/webp',
            'max:'.$maxKilobytes,
            'dimensions:min_width=600,min_height=400,max_width=6000,max_height=6000',
        ];

        $validated = $request->validate($rules);
        $uploadedCount = $landingMedia->storeUploads($request->file('uploads', []));
        $landingMedia->save($validated);

        $message = 'Landing page imagery saved.';
        if ($uploadedCount > 0) {
            $message .= ' '.$uploadedCount.' image'.($uploadedCount === 1 ? ' was' : 's were').' added to the media library; select it in a landing-page position when ready.';
        }

        return redirect()->route('admin.landing.settings')->with('success', $message);
    }
}
