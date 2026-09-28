<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_public_root_is_always_the_landing_page(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Create workspace')
            ->assertSee('Log in')
            ->assertSee('Precision command for');
    }

    public function test_logout_returns_to_the_public_landing_page(): void
    {
        $this->signInAsOwner();

        $this->post(route('logout'))
            ->assertRedirect(route('landing'));
    }
}
