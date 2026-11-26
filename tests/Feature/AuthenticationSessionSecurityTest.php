<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ])->assertRedirect(route('dashboard'));

        $authenticatedSessionId = app('session')->driver()->getId();
        $this->assertNotSame($preAuthenticationSessionId, $authenticatedSessionId);
        $this->assertDatabaseMissing('sessions', ['id' => $preAuthenticationSessionId]);
        $this->assertDatabaseHas('sessions', [
            'id' => $authenticatedSessionId,
            'user_id' => $user->id,
        ]);
    }
}
