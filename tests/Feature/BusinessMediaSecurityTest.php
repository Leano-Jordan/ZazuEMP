<?php

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessMediaSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_branding_media_is_served_through_authenticated_route(): void
    {
        $this->signInAsOwner();
        Storage::fake('public');

        $business = app(\App\Support\CurrentBusiness::class)->model(auth()->user());
        $path = 'business-branding/test-logo.webp';
        Storage::disk('public')->put($path, 'image-bytes');
        $business->update(['logo_path' => $path]);

        $response = $this->get(route('business.media', ['type' => 'logo']));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Cache-Control', 'private, no-store, max-age=0, must-revalidate');
        $response->assertHeader('Pragma', 'no-cache');
    }

    public function test_business_branding_media_cannot_be_read_from_another_business(): void
    {
        $this->signInAsOwner();
        Storage::fake('public');

        $other = Business::create([
            'name' => 'Other Business',
            'slug' => 'other-business-media',
            'status' => 'active',
            'currency' => 'ZAR',
            'logo_path' => 'business-branding/other.webp',
        ]);
        Storage::disk('public')->put($other->logo_path, 'other-image');

        $this->get(route('business.media', ['type' => 'logo']))->assertNotFound();
    }
}


    public function test_owner_can_replace_all_branding_assets_and_old_files_are_removed(): void
    {
        $this->signInAsOwner();
        Storage::fake('public');

        $business = auth()->user()->businesses()->firstOrFail();
        $oldPaths = [
            'logo_path' => 'business-branding/old-logo.webp',
            'dashboard_image_path' => 'business-branding/old-dashboard.webp',
            'wallpaper_path' => 'business-branding/old-wallpaper.webp',
        ];

        foreach ($oldPaths as $path) {
            Storage::disk('public')->put($path, 'old');
        }

        $business->update($oldPaths);

        $this->put(route('settings.update'), [
            'name' => 'Updated Test Business',
            'currency' => 'ZAR',
            'logo' => UploadedFile::fake()->image('logo.webp'),
            'dashboard_image' => UploadedFile::fake()->image('dashboard.webp'),
            'wallpaper' => UploadedFile::fake()->image('wallpaper.webp'),
        ])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('success', 'Business settings saved.');

        $business->refresh();

        foreach (array_keys($oldPaths) as $column) {
            $this->assertIsString($business->{$column});
            $this->assertNotSame($oldPaths[$column], $business->{$column});
            Storage::disk('public')->assertExists($business->{$column});
            Storage::disk('public')->assertMissing($oldPaths[$column]);
        }
    }

    public function test_settings_page_exposes_versioned_authenticated_branding_preview_contract(): void
    {
        $this->signInAsOwner();
        Storage::fake('public');

        $business = auth()->user()->businesses()->firstOrFail();
        $business->update([
            'logo_path' => 'business-branding/logo.webp',
            'dashboard_image_path' => 'business-branding/dashboard.webp',
            'wallpaper_path' => 'business-branding/wallpaper.webp',
        ]);

        $version = $business->fresh()->updated_at?->timestamp ?? 0;

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee(route('business.media', ['type' => 'logo']).'?v='.$version, false)
            ->assertSee(route('business.media', ['type' => 'dashboard']).'?v='.$version, false)
            ->assertSee(route('business.media', ['type' => 'wallpaper']).'?v='.$version, false)
            ->assertSee('data-branding-upload="logo"', false)
            ->assertSee('data-branding-upload="dashboard_image"', false)
            ->assertSee('data-branding-upload="wallpaper"', false)
            ->assertSee('data-branding-loading="logo"', false)
            ->assertSee('data-branding-loading="dashboard_image"', false)
            ->assertSee('data-branding-loading="wallpaper"', false)
            ->assertSee('data-branding-save', false);
    }
