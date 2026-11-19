<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class IncidentDiagnosticControllerTest extends TestCase
{
    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logPath = storage_path('logs/laravel-db001-diagnostic-test.log');
        File::ensureDirectoryExists(dirname($this->logPath));
        File::delete($this->logPath);
    }

    protected function tearDown(): void
    {
        File::delete($this->logPath);

        parent::tearDown();
    }

    public function test_reference_download_returns_only_allowlisted_database_diagnostics(): void
    {
        $requestId = (string) Str::uuid();
        $secret = 'private-customer-data-'.Str::random(24);

        File::put($this->logPath, $this->logLine([
            'event_type' => 'application_error',
            'error_code' => 'DB-001',
            'category' => 'Database',
            'severity' => 'high',
            'http_status' => 500,
            'exception_class' => 'Illuminate\\Database\\QueryException',
            'originating_component' => 'App\\Http\\Controllers\\FinanceController',
            'route_name' => 'finance.index',
            'http_method' => 'GET',
            'timestamp' => '2026-11-18T08:00:00+02:00',
            'request_id' => $requestId,
            'line' => 123,
            'database_connection' => 'sqlite',
            'database_driver' => 'sqlite',
            'database_sqlstate' => 'HY000',
            'database_driver_code' => 1,
            'database_operation' => 'read',
            'database_failure_class' => 'sqlite_missing_table',
            'file' => '/private/path/FinanceController.php',
            'message' => $secret,
            'sql' => 'select * from customers where email = ?',
            'bindings' => [$secret],
            'password' => $secret,
        ]));

        $response = $this->get(URL::temporarySignedRoute('support.incidents.show', now()->addMinutes(15), ['requestId' => $requestId]));

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Content-Disposition', 'attachment; filename="zazu-db001-'.$requestId.'.json"')
            ->assertJsonPath('request_id', $requestId)
            ->assertJsonPath('error_code', 'DB-001')
            ->assertJsonPath('database_driver_code', 1)
            ->assertJsonPath('database_operation', 'read')
            ->assertJsonPath('database_failure_class', 'sqlite_missing_table')
            ->assertJsonPath('source_file', 'FinanceController.php')
            ->assertJsonMissingPath('sql')
            ->assertJsonMissingPath('bindings')
            ->assertJsonMissingPath('password');

        $this->assertStringNotContainsString($secret, $response->getContent());
        $this->assertStringNotContainsString('select * from customers', $response->getContent());
        $this->assertStringNotContainsString('/private/path/', $response->getContent());
    }

    public function test_reference_download_does_not_return_other_or_non_database_incidents(): void
    {
        $requestId = (string) Str::uuid();

        File::put($this->logPath, $this->logLine([
            'request_id' => $requestId,
            'error_code' => 'APP-001',
            'exception_class' => 'RuntimeException',
        ]));

        $signedUrl = URL::temporarySignedRoute('support.incidents.show', now()->addMinutes(15), ['requestId' => $requestId]);
        $this->get($signedUrl)->assertNotFound();
    }

    public function test_reference_download_returns_not_found_when_no_matching_log_exists(): void
    {
        $this->get(URL::temporarySignedRoute('support.incidents.show', now()->addMinutes(15), ['requestId' => Str::uuid()]))->assertNotFound();
    }

    private function logLine(array $context): string
    {
        return '[2026-11-18 08:00:00] testing.ERROR: Zazu error incident '
            .json_encode($context, JSON_THROW_ON_ERROR)
            .PHP_EOL;
    }

    public function test_reference_download_rejects_unsigned_expired_and_tampered_links(): void
    {
        $requestId = (string) Str::uuid();

        $this->get('/api/support/incidents/'.$requestId)->assertForbidden();

        $expired = URL::temporarySignedRoute(
            'support.incidents.show',
            now()->subMinute(),
            ['requestId' => $requestId],
        );
        $this->get($expired)->assertForbidden();

        $valid = URL::temporarySignedRoute(
            'support.incidents.show',
            now()->addMinutes(15),
            ['requestId' => $requestId],
        );
        $tampered = str_replace($requestId, (string) Str::uuid(), $valid);
        $this->get($tampered)->assertForbidden();
    }

}
