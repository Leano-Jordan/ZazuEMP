<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
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

    public function test_unexpected_exception_uses_branded_500_surface_without_leaking_details(): void
    {
        $this->handleExceptions([\RuntimeException::class]);

        Route::middleware('web')->get('/__zazu-test-runtime-500', function () {
            throw new \RuntimeException('Sensitive database/table details must remain private.');
        });

        $response = $this->get('/__zazu-test-runtime-500');

        $response->assertStatus(500);
        $response->assertSee('Zazu could not complete that request.');
        $response->assertSee('Reference');
        $response->assertDontSee('Sensitive database/table details');
        $this->assertNotEmpty($response->headers->get('X-Zazu-Request-Id'));
    }

    public function test_http_500_response_uses_the_branded_error_surface(): void
    {
        Route::middleware('web')->get('/__zazu-test-http-500', fn () => abort(500));

        $response = $this->get('/__zazu-test-http-500');

        $response->assertStatus(500);
        $response->assertSee('Zazu could not complete that request.');
        $response->assertSee('Reference');
        $this->assertNotEmpty($response->headers->get('X-Zazu-Request-Id'));
    }

    public function test_validation_failure_keeps_standard_redirect_and_field_errors(): void
    {
        Route::post('/__zazu-test-validation', fn () => request()->validate([
            'name' => ['required', 'string'],
        ]))->middleware('web');

        $response = $this->from('/dashboard')->post('/__zazu-test-validation', []);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('name');
    }
}
