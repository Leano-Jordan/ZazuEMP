<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Support\Str;
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

    public function test_customer_profile_media_requires_customer_view_permission(): void
    {
        Storage::fake('local');

        $business = Business::create([
            'name' => 'Restricted Media Business',
            'slug' => 'restricted-media-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'limited']);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Restricted Customer',
            'profile_photo_path' => 'profile-photos/customers/restricted.jpg',
        ]);
        Storage::disk('local')->put($customer->profile_photo_path, 'private-customer-image');

        $this->actingAs($user)
            ->withSession([CurrentBusiness::SESSION_KEY => $business->id])
            ->get(route('profile.media', ['type' => 'customer', 'id' => $customer->id]))
            ->assertForbidden();
    }

    public function test_customer_profile_media_cannot_cross_business_boundary(): void
    {
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

        $this->signInAsOwner();

        Storage::disk('local')->put($customer->profile_photo_path, 'other-image');

        $this->get(route('profile.media', [
            'type' => 'customer',
            'id' => $customer->id,
        ]))->assertNotFound();
    }
}
