<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class BusinessSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $business = app(CurrentBusiness::class)->resolve($request->user());

        if (!$business && $request->user()) {
            $business = $this->business($request);
        }

        $business ??= new Business(['name' => 'Zazu', 'currency' => 'ZAR']);

        return view('settings.index', [
            'business' => $business,
            'currencies' => config('zazu.currencies'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $business = $this->business($request);
        $request->merge(['currency' => strtoupper((string) $request->input('currency'))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'currency' => ['required', Rule::in(array_keys(config('zazu.currencies')))],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'dashboard_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'wallpaper' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_dashboard_image' => ['nullable', 'boolean'],
            'remove_wallpaper' => ['nullable', 'boolean'],
        ]);

        $newPaths = [];
        $oldPaths = [];

        foreach ([
            'logo' => 'logo_path',
            'dashboard_image' => 'dashboard_image_path',
            'wallpaper' => 'wallpaper_path',
        ] as $upload => $column) {
            if ($request->hasFile($upload)) {
                $newPaths[$column] = $request->file($upload)->store('business-branding', 'public');
                $oldPaths[$column] = $business->{$column};
            } elseif ($request->boolean('remove_' . $upload)) {
                $newPaths[$column] = null;
                $oldPaths[$column] = $business->{$column};
            }
        }

        $business->update([
            'name' => trim($validated['name']),
            'currency' => strtoupper($validated['currency']),
            ...$newPaths,
        ]);

        foreach ($oldPaths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Business settings saved.');
    }

    private function business(Request $request): Business
    {
        $user = $request->user();

        if ($user) {
            $business = $user->businesses()->where('businesses.status', 'active')->first();

            if (!$business) {
                $business = Business::create([
                    'name' => trim($user->name) . "'s Business",
                    'slug' => Str::slug($user->name) . '-' . Str::lower(Str::random(6)),
                    'status' => 'active',
                    'currency' => 'ZAR',
                ]);

                $business->users()->attach($user->id, ['role' => 'owner']);
            }

            return $business;
        }

        return app(CurrentBusiness::class)->model();
    }
}
