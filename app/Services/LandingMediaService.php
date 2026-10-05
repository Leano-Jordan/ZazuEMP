<?php

namespace App\Services;

use App\Models\PlatformLandingSetting;
use Illuminate\Support\Facades\Schema;

class LandingMediaService
{
    public function current(): PlatformLandingSetting
    {
        return PlatformLandingSetting::current();
    }

    public function images(): array
    {
        $library = config('zazu.landing_image_library', []);

        $images = [
            'hero' => $this->localPath($library['catering_service']),
            'operations' => $this->localPath($library['event_catering']),
            'resources' => $this->localPath($library['sound_stage']),
            'control' => $this->localPath($library['wedding_catering']),
            'venue' => $this->localPath($library['venue']),
            'tent' => $this->localPath($library['tent']),
            'decor' => $this->localPath($library['decor']),
        ];

        // The public landing page must remain available during a deployment
        // where the new platform-media migration has not run yet.
        if (!Schema::hasTable('platform_landing_settings')) {
            return $images;
        }

        $settings = $this->current();

        $images['hero'] = $this->usablePath($settings->hero_image_path, $library['catering_service']);
        $images['operations'] = $this->usablePath($settings->operations_image_path, $library['event_catering']);
        $images['resources'] = $this->usablePath($settings->resources_image_path, $library['sound_stage']);
        $images['control'] = $this->usablePath($settings->control_image_path, $library['wedding_catering']);

        return $images;
    }

    private function usablePath(?string $storedPath, array $libraryEntry): string
    {
        $fallback = $this->localPath($libraryEntry);

        return $storedPath && str_starts_with($storedPath, '/images/landing/stock/')
            ? $storedPath
            : $fallback;
    }

    private function localPath(array $libraryEntry): string
    {
        $candidate = (string) ($libraryEntry['url'] ?? '');
        $fallback = (string) ($libraryEntry['fallback'] ?? '');

        return $candidate !== '' && str_starts_with($candidate, '/images/landing/stock/')
            && is_file(public_path(ltrim($candidate, '/')))
            ? $candidate
            : $fallback;
    }

    public function save(array $selected): void
    {
        $library = config('zazu.landing_image_library', []);
        $settings = PlatformLandingSetting::current();

        $settings->fill([
            'hero_image_path' => $library[$selected['hero_image']]['url'],
            'operations_image_path' => $library[$selected['operations_image']]['url'],
            'resources_image_path' => $library[$selected['resources_image']]['url'],
            'control_image_path' => $library[$selected['control_image']]['url'],
        ])->save();
    }
}
