<?php

namespace Tests\Feature;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
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

    public function test_unexpected_exception_renders_branded_500_surface_without_leaking_details(): void
    {
        $request = Request::create('/__zazu-test-500', 'GET');
        $request->attributes->set('zazu_request_id', 'test-request-id');

        $response = app(ExceptionHandler::class)->render(
            $request,
            new \RuntimeException('Sensitive database/table details must remain private.')
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('Zazu could not complete that request.', (string) $response->getContent());
        $this->assertStringContainsString('Reference test-request-id', $response->getContent());
        $this->assertStringNotContainsString('Sensitive database/table details', $response->getContent());
        $this->assertSame('test-request-id', $response->headers->get('X-Zazu-Request-Id'));
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
