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


    public function test_complete_bundled_image_library_exposes_every_unique_image_asset(): void
    {
        $expectedPaths = [];
        foreach (config('zazu.landing_image_library', []) as $entry) {
            foreach (['url', 'fallback'] as $field) {
                $path = (string) ($entry[$field] ?? '');
                if ($path !== '' && is_file(public_path(ltrim($path, '/')))) {
                    $expectedPaths[$path] = true;
                }
            }
        }

        $actualPaths = collect(app(LandingMediaService::class)->library())
            ->pluck('url')
            ->unique()
            ->all();

        foreach (array_keys($expectedPaths) as $path) {
            $this->assertContains($path, $actualPaths);
        }

        $this->assertGreaterThanOrEqual(6, count($actualPaths));
    }

    public function test_venue_and_decor_landing_positions_use_their_saved_assignments(): void
    {
        PlatformLandingSetting::current()->update([
            'venue_image_path' => config('zazu.landing_image_library.venue.url'),
            'decor_image_path' => config('zazu.landing_image_library.luxury_banquet.url'),
        ]);

        $images = app(LandingMediaService::class)->images();

        $this->assertSame(config('zazu.landing_image_library.venue.url'), $images['venue']);
        $this->assertSame(config('zazu.landing_image_library.luxury_banquet.url'), $images['decor']);
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
