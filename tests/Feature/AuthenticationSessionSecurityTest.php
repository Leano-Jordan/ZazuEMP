<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->withSession(['pre_authentication_marker' => 'must-not-survive-login'])
            ->get(route('landing'))
            ->assertOk();

        $preAuthenticationSessionId = app('session')->driver()->getId();
        $this->assertNotEmpty($preAuthenticationSessionId);
        $this->assertDatabaseHas('sessions', ['id' => $preAuthenticationSessionId]);

        $this->post(route('login.store'), [
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

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('sessions', ['id' => 'old-device-session']);
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
