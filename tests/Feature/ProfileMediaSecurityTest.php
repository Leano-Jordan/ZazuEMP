<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileMediaSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_profile_media_is_private_and_legacy_public_files_are_promoted(): void
    {
        $this->signInAsOwner();

        Storage::fake('local');
        Storage::fake('public');

        $path = 'profile-photos/customers/legacy.jpg';

        Storage::disk('public')->put($path, 'legacy-image-bytes');

        $customer = Customer::create([
            'name' => 'Legacy Customer',
            'profile_photo_path' => $path,
        ]);

        $response = $this->get(route('profile.media', [
            'type' => 'customer',
            'id' => $customer->id,
        ]));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_customer_profile_media_cannot_cross_business_boundary(): void
    {
        $this->signInAsOwner();

        Storage::fake('local');

        $otherBusiness = Business::create([
            'name' => 'Other Business',
            'slug' => 'other-business-profile-media',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $customer = Customer::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Customer',
            'profile_photo_path' => 'profile-photos/customers/other.jpg',
        ]);

        Storage::disk('local')->put($customer->profile_photo_path, 'other-image');

        $this->get(route('profile.media', [
            'type' => 'customer',
            'id' => $customer->id,
        ]))->assertNotFound();
    }
}
