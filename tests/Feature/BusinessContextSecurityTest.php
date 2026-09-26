<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessContextSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_without_an_active_business_cannot_enter_workspace(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }
    public function test_user_without_a_business_can_still_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

}
