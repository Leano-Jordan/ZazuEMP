<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationModalRegressionTest extends TestCase
{
    public function test_registration_errors_override_a_stale_login_query_when_reopening_modal(): void
    {
        $response = $this->from(route('landing', ['auth' => 'login']))
            ->post(route('register.store'), [
                'name' => 'Modal Owner',
                'username' => 'bad username',
                'business_name' => 'Modal Catering',
                'email' => 'modal-register@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('landing', ['auth' => 'login']))
            ->assertSessionHas('auth_modal', 'register')
            ->assertSessionHasErrors('username');

        $this->get(route('landing', ['auth' => 'login']))
            ->assertOk()
            ->assertSee('The username format is invalid.')
            ->assertSee('const initialPanel = validationPanel || requestedAuthPanel;', false);
    }
}
