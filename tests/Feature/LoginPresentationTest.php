<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_authentication_modal_is_image_free_and_preserves_the_authentication_surface(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk()
            ->assertSee('data-auth-modal-open="login"', false)
            ->assertSee('data-auth-modal-open="register"', false)
            ->assertSee('data-auth-modal', false)
            ->assertSee('name="identifier"', false)
            ->assertSee('Username or email', false)
            ->assertSee('Forgot your password?', false)
            ->assertSee(route('password.request'), false)
            ->assertDontSee('zazu-public-auth', false)
            ->assertDontSee('zazu-login-visual', false)
            ->assertDontSee('catering.webp', false)
            ->assertDontSee('<img', false);

        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringNotContainsString('catering.webp', $css);
        $this->assertStringNotContainsString('.zazu-login-visual', $css);
    }
}
