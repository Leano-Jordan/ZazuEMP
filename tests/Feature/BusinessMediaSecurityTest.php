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
        Storage::fake('local');

        $business = app(\App\Support\CurrentBusiness::class)->model(auth()->user());
        $path = 'business-branding/test-logo.webp';
        Storage::disk('local')->put($path, 'image-bytes');
        $business->update(['logo_path' => $path]);

        $response = $this->get(route('business.media', ['type' => 'logo']));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');

        $cacheControl = $response->headers->get('Cache-Control');

        $this->assertIsString($cacheControl);
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $response->assertHeader('Pragma', 'no-cache');
    }

    public function test_business_branding_media_cannot_be_read_from_another_business(): void
    {
        $this->signInAsOwner();
        Storage::fake('local');

        $other = Business::create([
            'name' => 'Other Business',
            'slug' => 'other-business-media',
            'status' => 'active',
            'currency' => 'ZAR',
            'logo_path' => 'business-branding/other.webp',
        ]);
        Storage::disk('local')->put($other->logo_path, 'other-image');

        $this->get(route('business.media', ['type' => 'logo']))->assertNotFound();
    }

    public function test_owner_can_replace_all_branding_assets_and_old_files_are_removed(): void
    {
        $this->signInAsOwner();
        Storage::fake('local');

        $business = auth()->user()->businesses()->firstOrFail();
        $oldPaths = [
            'logo_path' => 'business-branding/old-logo.webp',
            'dashboard_image_path' => 'business-branding/old-dashboard.webp',
            'wallpaper_path' => 'business-branding/old-wallpaper.webp',
        ];

        foreach ($oldPaths as $path) {
            Storage::disk('local')->put($path, 'old');
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
            Storage::disk('local')->assertExists($business->{$column});
            Storage::disk('local')->assertMissing($oldPaths[$column]);
        }
    }

    public function test_settings_page_exposes_versioned_authenticated_branding_preview_contract(): void
    {
        $this->signInAsOwner();
        Storage::fake('local');

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
    public function test_saved_branding_assets_are_rendered_in_shell_and_dashboard(): void
    {
        $this->signInAsOwner();
        Storage::fake('local');

        $business = auth()->user()->businesses()->firstOrFail();
        $paths = [
            'logo_path' => 'business-branding/logo.webp',
            'dashboard_image_path' => 'business-branding/dashboard.webp',
            'wallpaper_path' => 'business-branding/wallpaper.webp',
        ];

        foreach ($paths as $path) {
            Storage::disk('local')->put($path, 'image-bytes');
        }

        $business->update($paths);
        $version = $business->fresh()->updated_at?->timestamp ?? 0;

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('class="zazu-brand-logo"', false);
        $response->assertSee(route('business.media', ['type' => 'logo']).'?v='.$version, false);
        $response->assertSee('class="zazu-dash-hero-image"', false);
        $response->assertSee(route('business.media', ['type' => 'dashboard']).'?v='.$version, false);
        $response->assertSee('class="zazu-has-wallpaper"', false);
        $response->assertSee('--zazu-wallpaper:', false);
        $response->assertSee(route('business.media', ['type' => 'wallpaper']).'?v='.$version, false);
    }

    public function test_settings_page_does_not_decrypt_legacy_tcs_pin_just_to_render(): void
    {
        $this->signInAsOwner();
        $business = auth()->user()->businesses()->firstOrFail();

        $profile = $business->taxProfile()->create([
            'vat_status' => 'not_registered',
        ]);

        \Illuminate\Support\Facades\DB::statement(
            'UPDATE business_tax_profiles SET tcs_pin = ? WHERE id = ?',
            ['legacy-plain-text-value', $profile->id]
        );

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertDontSee('legacy-plain-text-value', false);

        $this->assertSame(
            'legacy-plain-text-value',
            \Illuminate\Support\Facades\DB::table('business_tax_profiles')->where('id', $profile->id)->value('tcs_pin')
        );
    }


    public function test_owner_can_save_business_settings_without_tax_profile_values(): void
    {
        $this->signInAsOwner();

        $business = auth()->user()->businesses()->firstOrFail();

        $this->put(route('settings.update'), [
            'name' => 'Saved Zazu Business',
            'currency' => 'ZAR',
        ])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('success', 'Business settings saved.');

        $business->refresh();

        $this->assertSame('Saved Zazu Business', $business->name);
        $this->assertSame('ZAR', $business->currency);
        $this->assertNotNull($business->taxProfile);
        $this->assertDatabaseHas('tax_rates', [
            'business_id' => $business->id,
            'code' => 'NO_VAT',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'business.settings.updated',
        ]);
    }


}

