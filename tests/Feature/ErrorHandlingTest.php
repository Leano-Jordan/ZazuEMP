<?php

namespace Tests\Feature;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test the same non-debug HTML error contract used in production.
        config()->set('app.debug', false);
    }
    public function test_missing_route_uses_branded_error_surface_and_request_reference(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertNotFound();
        $response->assertSee('We could not find that page.');
        $response->assertSee('Reference');
        $this->assertNotEmpty($response->headers->get('X-Zazu-Request-Id'));
    }

    public function test_exception_handler_renders_the_branded_500_surface_without_leaking_details(): void
    {
        $request = Request::create('/__zazu-test-handler-500', 'GET');
        $request->attributes->set('zazu_request_id', 'test-request-id');
        $this->app->instance('request', $request);

        $response = app(ExceptionHandler::class)->render(
            $request,
            new \RuntimeException('Sensitive database/table details must remain private.')
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('Zazu could not complete that request.', (string) $response->getContent());
        $this->assertStringContainsString('APP-001', (string) $response->getContent());
        $this->assertStringContainsString('Reference test-request-id', (string) $response->getContent());
        $this->assertStringNotContainsString('Sensitive database/table details', (string) $response->getContent());
        $this->assertSame('test-request-id', $response->headers->get('X-Zazu-Request-Id'));
    }

    public function test_production_500_view_is_present_in_the_application_error_views(): void
    {
        $this->assertFileExists(resource_path('views/errors/500.blade.php'));
        $this->assertTrue(File::exists(resource_path('views/errors/layout.blade.php')));
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
    public function test_database_exception_is_classified_without_exposing_technical_details(): void
    {
        $request = Request::create('/customers', 'GET');
        $request->attributes->set('zazu_request_id', 'db-request-id');
        $this->app->instance('request', $request);

        $exception = new \Illuminate\Database\QueryException(
            'sqlite',
            'select * from customers',
            [],
            new \PDOException('SQLSTATE[HY000]: database details are private')
        );

        $response = app(ExceptionHandler::class)->render($request, $exception);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('DB-001', (string) $response->getContent());
        $this->assertStringContainsString('We could not load or save the requested data.', (string) $response->getContent());
        $this->assertStringNotContainsString('select * from customers', (string) $response->getContent());
        $this->assertStringNotContainsString('SQLSTATE[HY000]', (string) $response->getContent());
        $this->assertSame('db-request-id', $response->headers->get('X-Zazu-Request-Id'));
        $this->assertSame('DB-001', $response->headers->get('X-Zazu-Error-Code'));
    }

    public function test_response_only_500_gets_an_application_error_code(): void
    {
        Route::get('/__zazu-test-response-500', fn () => response('failure', 500))->middleware('web');

        $response = $this->get('/__zazu-test-response-500');

        $response->assertStatus(500);
        $response->assertSee('APP-001');
        $response->assertSee('Zazu could not complete that request.');
        $this->assertNotEmpty($response->headers->get('X-Zazu-Request-Id'));
        $this->assertSame('APP-001', $response->headers->get('X-Zazu-Error-Code'));
    }

}
