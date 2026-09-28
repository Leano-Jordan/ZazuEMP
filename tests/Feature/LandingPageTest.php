<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;
    public function test_public_root_is_always_the_landing_page(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Register')
            ->assertSee('Log in')
            ->assertSee('Precision command for');
    }

    public function test_authenticated_owner_sees_clear_workspace_state_on_the_public_landing_page(): void
    {
        $this->signInAsOwner();

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee("You’re signed in")
            ->assertSee('Open workspace')
            ->assertSee('Sign out');
    }

    public function test_authenticated_account_without_workspace_can_still_view_the_public_landing_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee("You’re signed in")
            ->assertSee('Sign out')
            ->assertDontSee('Open workspace');
    }

        public function test_logout_returns_to_the_public_landing_page(): void
    {
        $this->signInAsOwner();

        $this->post(route('logout'))
            ->assertRedirect(route('landing'));
    }
}
