<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusinessSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $business = $request->user()?->businesses()->first() ?? Business::first() ?? new Business(['name' => 'Zazu']);

        return view('settings.index', compact('business'));
    }

    public function update(Request $request): RedirectResponse
    {
        $business = $this->business($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
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
            'name' => $validated['name'],
            ...$newPaths,
        ]);

        foreach ($oldPaths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Business appearance updated.');
    }

    private function business(Request $request): Business
    {
        $user = $request->user();
        abort_unless($user, 401);

        $business = $user->businesses()->first();

        if (!$business) {
            $business = Business::create([
                'name' => trim($user->name) . "'s Business",
                'slug' => Str::slug($user->name) . '-' . Str::lower(Str::random(6)),
                'status' => 'active',
            ]);

            $business->users()->attach($user->id, ['role' => 'owner']);
        }

        return $business;
    }
}
