<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_the_public_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewIs('landing')
            ->assertSee('Create workspace')
            ->assertSee('Log in');
    }
}
