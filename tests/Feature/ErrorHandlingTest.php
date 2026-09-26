<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    public function test_missing_route_uses_branded_error_surface_and_request_reference(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertNotFound();
        $response->assertSee('We could not find that page.');
        $response->assertSee('Reference');
        $this->assertNotEmpty($response->headers->get('X-Zazu-Request-Id'));
    }
}
