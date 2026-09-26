<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_login_and_registration(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    public function test_registration_creates_user_business_owner_membership_and_session(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Owner Person',
            'business_name' => 'Owner Catering',
            'email' => 'owner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'owner@example.com')->firstOrFail();
        $business = Business::where('name', 'Owner Catering')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('business_user', [
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'owner@example.com']);

        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Another Owner',
            'business_name' => 'Another Catering',
            'email' => 'owner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_owner_area_rejects_staff_and_allows_owner(): void
    {
        $business = Business::create([
            'name' => 'Role Business',
            'slug' => 'role-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $staff = User::factory()->create();
        $owner = User::factory()->create();

        $business->users()->attach($staff->id, ['role' => 'staff']);
        $business->users()->attach($owner->id, ['role' => 'owner']);

        $this->actingAs($staff)
            ->get(route('owner.dashboard'))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk();
    }

    public function test_owner_login_door_redirects_owner_and_does_not_elevate_staff(): void
    {
        $business = Business::create([
            'name' => 'Owner Door Business',
            'slug' => 'owner-door-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $owner = User::factory()->create(['password' => 'password123']);
        $staff = User::factory()->create(['password' => 'password123']);

        $business->users()->attach($owner->id, ['role' => 'owner']);
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $this->get(route('login', ['owner' => 1]))
            ->assertOk()
            ->assertSee('Owner sign in');

        $this->post(route('login.store'), [
            'email' => $staff->email,
            'password' => 'password123',
            'owner_access' => 1,
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->post(route('login.store'), [
            'email' => $owner->email,
            'password' => 'password123',
            'owner_access' => 1,
        ])->assertRedirect(route('owner.dashboard'));

        $this->assertAuthenticatedAs($owner);
    }

    public function test_business_context_does_not_auto_adopt_an_unassociated_user(): void
    {
        $business = Business::create([
            'name' => 'Existing Business',
            'slug' => 'existing-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->assertNull(app(\App\Support\CurrentBusiness::class)->resolve($user));
        $this->assertDatabaseMissing('business_user', [
            'business_id' => $business->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_settings_are_owner_only(): void
    {
        $business = Business::create([
            'name' => 'Settings Business',
            'slug' => 'settings-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $staff = User::factory()->create();
        $owner = User::factory()->create();

        $business->users()->attach($staff->id, ['role' => 'staff']);
        $business->users()->attach($owner->id, ['role' => 'owner']);

        $this->actingAs($staff)->get(route('settings.index'))->assertForbidden();
        $this->actingAs($owner)->get(route('settings.index'))->assertOk();
    }
}
