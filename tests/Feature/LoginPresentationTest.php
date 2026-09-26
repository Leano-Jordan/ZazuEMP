<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_image_free_and_preserves_the_authentication_surface(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee('zazu-login-frame-single', false)
            ->assertSee('data-password-toggle', false)
            ->assertSee('name="identifier"', false)
            ->assertSee('Username or email', false)
            ->assertSee('Forgot password?', false)
            ->assertSee(route('password.request'), false)
            ->assertSee('zazu-auth-subtitle', false)
            ->assertDontSee('zazu-login-visual', false)
            ->assertDontSee('catering.webp', false)
            ->assertDontSee('<img', false);

        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringNotContainsString('catering.webp', $css);
        $this->assertStringNotContainsString('.zazu-login-visual', $css);
    }
}
