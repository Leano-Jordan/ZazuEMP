<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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

    public function test_late_database_session_write_cannot_resurrect_a_session_destroyed_after_read(): void
    {
        config(['session.driver' => 'database']);

        $sessionId = 'in-flight-authenticated-session';
        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => User::factory()->create()->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'concurrent-request-a',
            'payload' => base64_encode(json_encode([
                'login_web_' . sha1('App\\Models\\User') => 42,
                '_token' => Str::random(40),
            ], JSON_THROW_ON_ERROR)),
            'last_activity' => now()->timestamp,
        ]);

        // Request A reads the authenticated session before its controller runs.
        $inFlightHandler = new DatabaseSessionHandler(
            DB::connection(),
            config('session.table', 'sessions'),
            (int) config('session.lifetime', 120),
        );
        $this->assertStringContainsString('login_web_', $inFlightHandler->read($sessionId));

        // Request B logs out / resets the account and destroys that session.
        $this->assertTrue($inFlightHandler->destroy($sessionId));
        $this->assertDatabaseMissing('sessions', ['id' => $sessionId]);

        // Request A finishes later. Laravel's database handler must UPDATE the
        // row it previously read, never INSERT a deleted authenticated session.
        $this->assertTrue($inFlightHandler->write($sessionId, '{"late":"request-save"}'));
        $this->assertDatabaseMissing('sessions', ['id' => $sessionId]);
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
