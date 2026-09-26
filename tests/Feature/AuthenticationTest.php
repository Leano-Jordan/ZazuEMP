<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_login_and_registration(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk()->assertSee('Username');
        $this->get(route('owner.login'))->assertOk()->assertSee('Owner sign in');
    }

    public function test_registration_creates_user_business_owner_membership_and_session(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Owner Person',
            'username' => 'ownerperson',
            'business_name' => 'Owner Catering',
            'email' => 'owner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'owner@example.com')->firstOrFail();
        $business = Business::where('name', 'Owner Catering')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('ownerperson', $user->username);
        $this->assertDatabaseHas('business_user', [
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_registration_rejects_duplicate_email_and_username(): void
    {
        User::factory()->create([
            'username' => 'existingowner',
            'email' => 'owner@example.com',
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Another Owner',
                'username' => 'existingowner',
                'business_name' => 'Another Catering',
                'email' => 'owner@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors(['email', 'username']);
    }

    public function test_registration_rejects_username_equal_to_an_existing_email(): void
    {
        User::factory()->create([
            'username' => 'existinguser',
            'email' => 'owner@example.com',
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Another Owner',
                'username' => 'owner@example.com',
                'business_name' => 'Another Catering',
                'email' => 'another@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_registration_rejects_email_equal_to_an_existing_username(): void
    {
        User::factory()->create([
            'username' => 'existinguser',
            'email' => 'owner@example.com',
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Another Owner',
                'username' => 'anotherowner',
                'business_name' => 'Another Catering',
                'email' => 'existinguser',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_registration_rejects_invalid_username(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Another Owner',
                'username' => 'Not Allowed!',
                'business_name' => 'Another Catering',
                'email' => 'another@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_username_login_succeeds_and_logout_ends_the_authenticated_session(): void
    {
        $user = User::factory()->create([
            'username' => 'loginowner',
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $business = Business::create([
            'name' => 'Login Business',
            'slug' => 'login-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $business->users()->attach($user->id, ['role' => 'owner']);

        $this->post(route('login.store'), [
            'identifier' => 'LOGINOWNER',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_email_remains_a_valid_login_identifier(): void
    {
        $user = User::factory()->create([
            'username' => 'emailfallback',
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $business = Business::create([
            'name' => 'Email Login Business',
            'slug' => 'email-login-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $business->users()->attach($user->id, ['role' => 'owner']);

        $this->post(route('login.store'), [
            'identifier' => 'LOGIN@EXAMPLE.COM',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_does_not_authenticate_and_preserves_the_identifier_error(): void
    {
        $user = User::factory()->create([
            'username' => 'wrongpassword',
            'password' => 'password123',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'identifier' => $user->username,
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors('identifier');

        $this->assertGuest();
    }

    public function test_password_recovery_accepts_username_and_sends_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'username' => 'recoverowner',
            'email' => 'recover@example.com',
        ]);

        $this->post(route('password.email'), [
            'identifier' => 'RECOVEROWNER',
        ])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_password_recovery_returns_the_same_success_message_for_unknown_accounts(): void
    {
        Notification::fake();

        $this->post(route('password.email'), [
            'identifier' => 'does-not-exist',
        ])->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_password_reset_updates_password_logs_the_user_in_and_consumes_the_token(): void
    {
        $user = User::factory()->create([
            'username' => 'resetowner',
            'email' => 'reset@example.com',
            'password' => 'old-password',
        ]);

        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'RESET@EXAMPLE.COM',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertFalse(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_password_reset_token_cannot_be_reused(): void
    {
        $user = User::factory()->create([
            'username' => 'singleuse',
            'email' => 'singleuse@example.com',
        ]);

        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('dashboard'));

        Auth::logout();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'another-password',
            'password_confirmation' => 'another-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
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

        $owner = User::factory()->create([
            'username' => 'theowner',
            'password' => 'password123',
        ]);
        $staff = User::factory()->create([
            'username' => 'thestaff',
            'password' => 'password123',
        ]);

        $business->users()->attach($owner->id, ['role' => 'owner']);
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $this->get(route('owner.login'))
            ->assertOk()
            ->assertSee('Owner sign in');

        $this->post(route('login.store'), [
            'identifier' => $staff->username,
            'password' => 'password123',
            'owner_access' => 1,
        ])->assertSessionHasErrors('identifier');

        $this->assertGuest();

        $this->post(route('login.store'), [
            'identifier' => 'THEOWNER',
            'password' => 'password123',
            'owner_access' => 1,
        ])->assertRedirect(route('owner.dashboard'));

        $this->assertAuthenticatedAs($owner);
        $this->assertSame($business->id, app(CurrentBusiness::class)->id($owner));
    }

    public function test_owner_login_selects_an_owned_workspace_when_an_account_is_staff_elsewhere(): void
    {
        $owned = Business::create([
            'name' => 'Owned Workspace',
            'slug' => 'owned-workspace',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $staffWorkspace = Business::create([
            'name' => 'Staff Workspace',
            'slug' => 'staff-workspace',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create([
            'username' => 'multiworkowner',
            'password' => 'password123',
        ]);
        $owned->users()->attach($user->id, ['role' => 'owner']);
        $staffWorkspace->users()->attach($user->id, ['role' => 'staff']);

        $this->post(route('login.store'), [
            'identifier' => $user->username,
            'password' => 'password123',
            'owner_access' => 1,
        ])->assertRedirect(route('owner.dashboard'));

        $this->assertSame($owned->id, app(CurrentBusiness::class)->id($user));
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

        $this->assertNull(app(CurrentBusiness::class)->resolve($user));
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

        $this->actingAs($staff)
            ->put(route('settings.update'), [
                'name' => 'Should Not Save',
                'currency' => 'ZAR',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('capabilities.store'), [
                'name' => 'Should Not Exist',
                'category' => 'Catering',
                'capability_type' => 'service',
                'pricing_basis' => 'custom',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($owner)->get(route('settings.index'))->assertOk();
    }
}
