<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationModalRegressionTest extends TestCase
{
    public function test_registration_errors_override_a_stale_login_query_when_reopening_modal(): void
    {
        $this->withSession(['auth_modal' => 'register'])
            ->withErrors(['username' => 'The username is invalid.'])
            ->get(route('landing', ['auth' => 'login']))
            ->assertOk()
            ->assertSee('The username is invalid.')
            ->assertSee('const initialPanel = validationPanel || requestedAuthPanel;', false);
    }
}
