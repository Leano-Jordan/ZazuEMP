<?php

namespace Tests\Feature;

use App\Models\PlatformLandingSetting;
use App\Services\LandingMediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingMediaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_media_library_is_fully_bundled_under_public_images(): void
    {
        $library = config('zazu.landing_image_library', []);

        $this->assertNotEmpty($library);

        $images = app(LandingMediaService::class)->images();

        $this->assertSame(
            ['hero', 'operations', 'resources', 'control', 'venue', 'tent', 'decor'],
            array_keys($images),
        );

        foreach ($library as $image) {
            $path = (string) ($image['url'] ?? '');

            $this->assertStringStartsWith('/images/landing/', $path);
            $this->assertFileExists(public_path(ltrim($path, '/')));
        }
    }

    public function test_legacy_remote_landing_paths_fall_back_to_bundled_assets(): void
    {
        PlatformLandingSetting::current()->update([
            'hero_image_path' => 'https://images.pexels.com/legacy-hero.jpg',
            'operations_image_path' => 'https://images.pexels.com/legacy-operations.jpg',
            'resources_image_path' => 'https://images.pexels.com/legacy-resources.jpg',
            'control_image_path' => 'https://images.pexels.com/legacy-control.jpg',
        ]);

        $images = app(LandingMediaService::class)->images();
        $library = config('zazu.landing_image_library');

        $this->assertSame($library['catering_service']['url'], $images['hero']);
        $this->assertSame($library['event_catering']['url'], $images['operations']);
        $this->assertSame($library['sound_stage']['url'], $images['resources']);
        $this->assertSame($library['wedding_catering']['url'], $images['control']);
    }
}
