<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ZazuIncidentRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_missing_workspace_403_explains_signed_in_account_state(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/dashboard');

        $response->assertForbidden();
        $response->assertSee("You're signed in, but no business is open.");
        $response->assertSee('Home page');
        $response->assertSee('Sign out');
        $this->assertSame('AUTHZ-002', $response->headers->get('X-Zazu-Error-Code'));
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
        $this->assertStringContainsString('Reference number: test-request-id', (string) $response->getContent());
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

        $exception = new QueryException(
            'sqlite',
            'select * from customers',
            [],
            new \PDOException('SQLSTATE[HY000]: database details are private')
        );

        $response = app(ExceptionHandler::class)->render($request, $exception);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('DB-001', (string) $response->getContent());
        $this->assertStringContainsString('We could not open or save your information.', (string) $response->getContent());
        $this->assertStringNotContainsString('select * from customers', (string) $response->getContent());
        $this->assertStringNotContainsString('SQLSTATE[HY000]', (string) $response->getContent());
        $this->assertSame('db-request-id', $response->headers->get('X-Zazu-Request-Id'));
        $this->assertSame('DB-001', $response->headers->get('X-Zazu-Error-Code'));
    }


    public function test_db001_error_surface_offers_reference_scoped_diagnostic_download(): void
    {
        $requestId = '895e2c23-bc6b-4919-a891-1e8e5c1e27e4';
        $request = Request::create('/dashboard', 'GET');
        $request->attributes->set('zazu_request_id', $requestId);
        $this->app->instance('request', $request);

        $response = app(ExceptionHandler::class)->render(
            $request,
            new QueryException(
                'sqlite',
                'select * from customers',
                [],
                new \PDOException('SQLSTATE[HY000]: private database detail')
            )
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('/api/support/incidents/'.$requestId, (string) $response->getContent());
        $this->assertStringContainsString('Download safe diagnostic report', (string) $response->getContent());
        $this->assertStringNotContainsString('private database detail', (string) $response->getContent());
    }

    public function test_incident_log_omits_exception_messages_sql_and_stack_traces(): void
    {
        $marker = 'private-customer-secret-'.bin2hex(random_bytes(8));
        $request = Request::create('/customers', 'GET');
        $request->attributes->set('zazu_request_id', 'redaction-request-id');
        $this->app->instance('request', $request);

        Log::shouldReceive('log')
            ->once()
            ->withArgs(function (string $level, string $message, array $context) use ($marker): bool {
                $serialized = json_encode($context);

                $expectedDiagnostics = [
                    'database_connection' => 'sqlite',
                    'database_driver' => 'sqlite',
                    'database_sqlstate' => 'HY000',
                    'database_driver_code' => 1,
                    'database_operation' => null,
                    'database_failure_class' => 'sqlite_missing_table',
                ];

                return $level === 'error'
                    && $message === 'Zazu error incident'
                    && $context['request_id'] === 'redaction-request-id'
                    && $context['exception_class'] === QueryException::class
                    && array_intersect_key($context, $expectedDiagnostics) === $expectedDiagnostics
                    && ! str_contains((string) $serialized, $marker)
                    && ! str_contains((string) $serialized, 'select * from private_customers');
            });

        $pdoException = new \PDOException('SQLSTATE[HY000]: General error: 1 no such table: private_customers');
        $pdoException->errorInfo = ['HY000', 1, $marker];



        ZazuIncidentRecorder::record(new QueryException(
            'sqlite',
            'select * from private_customers where email = ?',
            [$marker],
            $pdoException
        ));
    }

    public function test_direct_pdo_connection_exception_keeps_safe_driver_context(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.driver', 'sqlite');

        $requestId = '6d8c6a3a-fbae-4d15-bc0a-9bd9905c2e9c';
        $request = Request::create('/sentinel/rescue', 'GET');
        $request->attributes->set('zazu_request_id', $requestId);
        $this->app->instance('request', $request);

        $directory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);

        $privateMarker = 'private-pdo-detail-'.bin2hex(random_bytes(8));
        Log::shouldReceive('log')
            ->once()
            ->withArgs(function (string $level, string $message, array $context) use ($requestId, $privateMarker): bool {
                $expected = [
                    'database_connection' => 'sqlite',
                    'database_driver' => 'sqlite',
                    'database_sqlstate' => 'HY000',
                    'database_driver_code' => 5,
                    'database_failure_class' => 'sqlite_locked',
                ];

                return $level === 'error'
                    && $message === 'Zazu error incident'
                    && $context['request_id'] === $requestId
                    && array_intersect_key($context, $expected) === $expected
                    && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), $privateMarker);
            });

        try {
            $exception = new \PDOException('SQLSTATE[HY000]: database is locked '.$privateMarker);
            $exception->errorInfo = ['HY000', 5, $privateMarker];

            ZazuIncidentRecorder::record($exception);
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_incident_capture_survives_a_broken_primary_and_fallback_log_channel(): void
    {
        $requestId = '4b2c8d39-1b17-4b1c-a2f2-4d6e0a7c9f31';
        $request = Request::create('/__zazu-test-log-outage', 'GET');
        $request->attributes->set('zazu_request_id', $requestId);
        $this->app->instance('request', $request);

        $directory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);

        Log::shouldReceive('log')->once()->andThrow(new \\RuntimeException('primary log unavailable'));
        Log::shouldReceive('error')->once()->andThrow(new \\RuntimeException('fallback log unavailable'));

        try {
            ZazuIncidentRecorder::record(new \\RuntimeException('private exception details'));

            $ledger = File::get($directory.DIRECTORY_SEPARATOR.'incidents.jsonl');
            $this->assertStringContainsString($requestId, $ledger);
            $this->assertStringNotContainsString('private exception details', $ledger);
            $this->assertStringNotContainsString('primary log unavailable', $ledger);
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_http_500_exception_gets_an_application_error_code(): void
    {
        Route::get('/__zazu-test-response-500', fn () => abort(500))->middleware('web');

        $response = $this->get('/__zazu-test-response-500');

        $response->assertStatus(500);
        $response->assertSee('APP-001');
        $response->assertSee('Zazu could not complete that request.');
        $this->assertNotEmpty($response->headers->get('X-Zazu-Request-Id'));
        $this->assertSame('APP-001', $response->headers->get('X-Zazu-Error-Code'));
    }
}
