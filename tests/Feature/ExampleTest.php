<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_the_public_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewIs('landing')
            ->assertSee('Register')
            ->assertSee('Log in');
    }
}
