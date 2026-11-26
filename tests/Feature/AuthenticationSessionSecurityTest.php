<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationSessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_rotates_and_destroys_the_pre_authentication_session(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create([
            'username' => 'sessionrotation',
            'email' => 'sessionrotation@example.com',
            'password' => 'password123',
        ]);
        $business = Business::create([
            'name' => 'Session Rotation Business',
            'slug' => 'session-rotation-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $preAuthenticationResponse = $this->withSession(['pre_authentication_marker' => 'must-not-survive-login'])
            ->get(route('landing'))
            ->assertOk();
        $preAuthenticationCookie = $preAuthenticationResponse->getCookie(config('session.cookie'));
        $this->assertNotNull($preAuthenticationCookie);

        $preAuthenticationSessionId = app('session')->driver()->getId();
        $this->assertNotEmpty($preAuthenticationSessionId);
        $this->assertDatabaseHas('sessions', ['id' => $preAuthenticationSessionId]);

        $this->withCookie(config('session.cookie'), $preAuthenticationCookie->getValue())
            ->post(route('login.store'), [
                'identifier' => 'sessionrotation',
                'password' => 'password123',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('auth_modal', 'login');

        $authenticatedSessionId = app('session')->driver()->getId();
        $this->assertNotSame($preAuthenticationSessionId, $authenticatedSessionId);
        $this->assertDatabaseMissing('sessions', ['id' => $preAuthenticationSessionId]);
        $this->assertDatabaseHas('sessions', [
            'id' => $authenticatedSessionId,
            'user_id' => $user->id,
        ]);
    }

    public function test_registration_rotates_and_destroys_the_pre_authentication_session(): void
    {
        config(['session.driver' => 'database']);

        $preRegistrationResponse = $this->withSession(['pre_registration_marker' => 'must-not-survive-registration'])
            ->get(route('landing'))
            ->assertOk();
        $preRegistrationCookie = $preRegistrationResponse->getCookie(config('session.cookie'));
        $this->assertNotNull($preRegistrationCookie);

        $preRegistrationSessionId = app('session')->driver()->getId();
        $this->assertNotEmpty($preRegistrationSessionId);
        $this->assertDatabaseHas('sessions', ['id' => $preRegistrationSessionId]);

        $this->withCookie(config('session.cookie'), $preRegistrationCookie->getValue())
            ->post(route('register.store'), [
                'name' => 'Session Registration Owner',
                'username' => 'sessionregister',
                'business_name' => 'Session Registration Business',
                'email' => 'sessionregister@example.com',
                'password' => 'registration-password',
                'password_confirmation' => 'registration-password',
            ])
            ->assertRedirect(route('onboarding.catalogue'))
            ->assertSessionHas('auth_modal', 'register');

        $user = User::query()->where('email', 'sessionregister@example.com')->firstOrFail();
        $authenticatedSessionId = app('session')->driver()->getId();

        $this->assertNotSame($preRegistrationSessionId, $authenticatedSessionId);
        $this->assertDatabaseMissing('sessions', ['id' => $preRegistrationSessionId]);
        $this->assertDatabaseHas('sessions', [
            'id' => $authenticatedSessionId,
            'user_id' => $user->id,
        ]);
    }

    public function test_replayed_pre_authentication_cookie_cannot_reuse_the_authenticated_session_after_login(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create([
            'username' => 'sessionreplay',
            'email' => 'sessionreplay@example.com',
            'password' => 'password123',
        ]);
        $business = Business::create([
            'name' => 'Session Replay Business',
            'slug' => 'session-replay-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $preAuthResponse = $this->get(route('landing'))->assertOk();
        $preAuthCookie = $preAuthResponse->getCookie(config('session.cookie'));
        $this->assertNotNull($preAuthCookie);
        $preAuthSessionId = $preAuthCookie->getValue();
        $this->assertDatabaseHas('sessions', ['id' => $preAuthSessionId]);

        $loginResponse = $this->withCookie(config('session.cookie'), $preAuthCookie->getValue())
            ->post(route('login.store'), [
                'identifier' => $user->username,
                'password' => 'password123',
            ])
            ->assertRedirect(route('dashboard'));

        $authenticatedCookie = $loginResponse->getCookie(config('session.cookie'));
        $this->assertNotNull($authenticatedCookie);
        $this->assertNotSame($preAuthCookie->getValue(), $authenticatedCookie->getValue());
        $this->assertDatabaseMissing('sessions', ['id' => $preAuthSessionId]);

        $this->resetHttpSessionState();
        $this->withCookie(config('session.cookie'), $authenticatedCookie->getValue())
            ->get(route('dashboard'))
            ->assertOk();

        $this->resetHttpSessionState();
        $this->withCookie(config('session.cookie'), $preAuthCookie->getValue())
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->resetHttpSessionState();
        $this->withCookie(config('session.cookie'), $authenticatedCookie->getValue())
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_replayed_pre_registration_cookie_cannot_reuse_the_registered_account_session(): void
    {
        config(['session.driver' => 'database']);

        $preRegistrationResponse = $this->get(route('landing'))->assertOk();
        $preRegistrationCookie = $preRegistrationResponse->getCookie(config('session.cookie'));
        $this->assertNotNull($preRegistrationCookie);
        $preRegistrationSessionId = $preRegistrationCookie->getValue();
        $this->assertDatabaseHas('sessions', ['id' => $preRegistrationSessionId]);

        $registrationResponse = $this->withCookie(config('session.cookie'), $preRegistrationCookie->getValue())
            ->post(route('register.store'), [
                'name' => 'Session Replay Registration',
                'username' => 'sessionreplayregistration',
                'business_name' => 'Session Replay Registration Business',
                'email' => 'sessionreplayregistration@example.com',
                'password' => 'registration-password',
                'password_confirmation' => 'registration-password',
            ])
            ->assertRedirect(route('onboarding.catalogue'));

        $registeredCookie = $registrationResponse->getCookie(config('session.cookie'));
        $this->assertNotNull($registeredCookie);
        $this->assertNotSame($preRegistrationCookie->getValue(), $registeredCookie->getValue());
        $this->assertDatabaseMissing('sessions', ['id' => $preRegistrationSessionId]);

        $this->resetHttpSessionState();
        $this->withCookie(config('session.cookie'), $registeredCookie->getValue())
            ->get(route('dashboard'));
        $this->assertAuthenticatedAs(User::query()->where('email', 'sessionreplayregistration@example.com')->firstOrFail());

        $this->resetHttpSessionState();
        $this->withCookie(config('session.cookie'), $preRegistrationCookie->getValue())
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->resetHttpSessionState();
        $this->withCookie(config('session.cookie'), $registeredCookie->getValue())
            ->get(route('dashboard'));
        $this->assertAuthenticatedAs(User::query()->where('email', 'sessionreplayregistration@example.com')->firstOrFail());
    }

    public function test_password_reset_revokes_previously_authenticated_sessions(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create([
            'username' => 'sessionreset',
            'email' => 'sessionreset@example.com',
            'password' => 'old-password',
        ]);

        DB::table('sessions')->insert([
            'id' => 'old-device-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'old-device',
            'payload' => '{}',
            'last_activity' => now()->timestamp,
        ]);

        $token = Password::createToken($user);

        $preResetResponse = $this->withSession(['pre_reset_marker' => 'must-not-survive-password-reset'])
            ->get(route('landing'))
            ->assertOk();
        $preResetCookie = $preResetResponse->getCookie(config('session.cookie'));
        $this->assertNotNull($preResetCookie);
        $preResetSessionId = app('session')->driver()->getId();
        $this->assertNotEmpty($preResetSessionId);
        $this->assertDatabaseHas('sessions', ['id' => $preResetSessionId]);

        $this->withCookie(config('session.cookie'), $preResetCookie->getValue())
            ->post(route('password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('sessions', ['id' => 'old-device-session']);
        $this->assertDatabaseMissing('sessions', ['id' => $preResetSessionId]);
    }

    private function resetHttpSessionState(): void
    {
        Auth::forgetGuards();
        app('session')->forgetDrivers();
    }

    public function test_business_switch_rejects_a_stale_authenticated_password_hash(): void
    {
        $user = User::factory()->create(['password' => 'new-password']);
        $firstBusiness = Business::create([
            'name' => 'Current Session Business',
            'slug' => 'current-session-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $secondBusiness = Business::create([
            'name' => 'Target Session Business',
            'slug' => 'target-session-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $firstBusiness->users()->attach($user->id, ['role' => 'owner']);
        $secondBusiness->users()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession([
                'password_hash_web' => Hash::make('old-password'),
                CurrentBusiness::SESSION_KEY => $firstBusiness->id,
            ])
            ->post(route('business.switch'), ['business_id' => $secondBusiness->id])
            ->assertRedirect();

        $this->assertGuest();
    }
}
