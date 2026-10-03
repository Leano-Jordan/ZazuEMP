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

        // The public landing page must remain available during a deployment
        // where the new platform-media migration has not run yet.
        if (!Schema::hasTable('platform_landing_settings')) {
            return [
                'hero' => $library['catering_service']['url'],
                'operations' => $library['event_catering']['url'],
                'resources' => $library['sound_stage']['url'],
                'control' => $library['wedding_catering']['url'],
            ];
        }

        $settings = $this->current();

        return [
            'hero' => $this->usablePath($settings->hero_image_path, $library['catering_service']['url']),
            'operations' => $this->usablePath($settings->operations_image_path, $library['event_catering']['url']),
            'resources' => $this->usablePath($settings->resources_image_path, $library['sound_stage']['url']),
            'control' => $this->usablePath($settings->control_image_path, $library['wedding_catering']['url']),
        ];
    }

    private function usablePath(?string $storedPath, string $fallback): string
    {
        return $storedPath && str_starts_with($storedPath, '/images/landing/')
            ? $storedPath
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
