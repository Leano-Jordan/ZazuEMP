<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessMediaSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function signInAsOwner(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $user->businesses()->attach($business->id, ['role' => 'owner']);
        $this->actingAs($user);
        session(['zazu_business_id' => $business->id]);
    }

    public function test_business_media_route_requires_authentication(): void
    {
        $response = $this->get(route('business.media', ['type' => 'logo']));

        $response->assertRedirect(route('login'));
    }

    public function test_business_media_route_rejects_invalid_type(): void
    {
        $this->signInAsOwner();

        $response = $this->get(route('business.media', ['type' => 'invalid']));

        $response->assertNotFound();
    }

    public function test_business_media_route_returns_placeholder_when_media_missing(): void
    {
        $this->signInAsOwner();

        $response = $this->get(route('business.media', ['type' => 'logo']));

        $response->assertOk();
    }

    public function test_business_media_urls_are_versioned_in_rendered_surfaces(): void
    {
        $this->signInAsOwner();

        $business = auth()->user()->businesses()->firstOrFail();
        $version = $business->media_updated_at?->timestamp ?? 0;

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('business.media', ['type' => 'logo']).'?v='.$version, false);
        $response->assertSee('class="zazu-dash-hero-image"', false);
        $response->assertSee(route('business.media', ['type' => 'dashboard']).'?v='.$version, false);
        $response->assertSee('class="zazu-has-wallpaper"', false);
        $response->assertSee('--zazu-wallpaper:', false);
        $response->assertSee(route('business.media', ['type' => 'wallpaper']).'?v='.$version, false);
    }

    public function test_wallpaper_visibility_rule_is_final_and_not_overridden_by_global_body_backgrounds(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $marker = strpos($css, 'body.zazu-has-wallpaper {');

        $this->assertNotFalse($marker);
        $this->assertStringContainsString(
            'body.zazu-has-wallpaper {',
            substr($css, (int) $marker)
        );
        $this->assertStringContainsString(
            'var(--zazu-wallpaper)',
            substr($css, (int) $marker)
        );
    }

    public function test_settings_page_does_not_decrypt_legacy_tcs_pin_just_to_render(): void
    {
        $this->signInAsOwner();
        $business = auth()->user()->businesses()->firstOrFail();

        $profile = $business->taxProfile()->create([
            'vat_status' => 'not_registered',
        ]);

        DB::statement(
            'UPDATE business_tax_profiles SET legacy_tcs_pin = ? WHERE id = ?',
            ['encrypted:legacy', $profile->id]
        );

        $response = $this->get(route('settings'));

        $response->assertOk();
    }

    public function test_business_media_download_does_not_allow_path_traversal(): void
    {
        $this->signInAsOwner();

        Storage::fake('public');

        $response = $this->get(route('business.media', ['type' => '../config/app.php']));

        $response->assertNotFound();
    }
}
