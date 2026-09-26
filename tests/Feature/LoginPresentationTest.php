<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_uses_the_current_commercial_split_screen_structure(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('zazu-login-frame', false)
            ->assertSee('zazu-login-visual', false)
            ->assertSee('data-password-toggle', false)
            ->assertSee('name="identifier"', false)
            ->assertSee('Username or email', false)
            ->assertSee('Forgot password?', false)
            ->assertSee(route('password.request'), false)
            ->assertSee('zazu-auth-subtitle', false)
            ->assertSee('catering', false);
    }
}
