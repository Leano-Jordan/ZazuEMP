<?php

namespace App\Services;

use App\Models\PlatformLandingSetting;

class LandingMediaService
{
    public function images(): array
    {
        $settings = PlatformLandingSetting::current();
        $library = config('zazu.landing_image_library', []);

        return [
            'hero' => $settings->hero_image_path ?: $library['catering_service']['url'],
            'operations' => $settings->operations_image_path ?: $library['event_catering']['url'],
            'resources' => $settings->resources_image_path ?: $library['sound_stage']['url'],
            'control' => $settings->control_image_path ?: $library['wedding_catering']['url'],
        ];
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
