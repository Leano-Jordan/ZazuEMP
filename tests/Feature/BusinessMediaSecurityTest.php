<?php

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
