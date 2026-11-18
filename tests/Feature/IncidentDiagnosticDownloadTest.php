<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class IncidentDiagnosticDownloadTest extends TestCase
{
    public function test_db001_reference_download_returns_only_allowlisted_fields(): void
    {
        config(['cache.default' => 'array']);

        $requestId = '895e2c23-bc6b-4919-a891-1e8e5c1e27e4';
        $path = storage_path('logs/laravel-db001-download-test.log');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, '[2026-11-18 01:00:00] local.ERROR: Zazu error incident '.json_encode([
            'request_id' => $requestId,
            'error_code' => 'DB-001',
            'category' => 'Database',
            'severity' => 'high',
            'http_status' => 500,
            'exception_class' => 'Illuminate\\Database\\QueryException',
            'originating_component' => 'App\\Http\\Controllers\\DashboardController@index',
            'route_name' => 'dashboard',
            'http_method' => 'GET',
            'http_path' => 'dashboard/private-customer-123',
            'timestamp' => '2026-11-18T01:00:00+02:00',
            'file' => '/srv/zazu/app/Http/Controllers/DashboardController.php',
            'line' => 81,
            'database_connection' => 'sqlite',
            'database_driver' => 'sqlite',
            'database_sqlstate' => 'HY000',
            'database_driver_code' => 1,
            'database_operation' => 'read',
            'sql' => 'select * from customers where email = secret@example.com',
            'bindings' => ['secret@example.com'],
            'exception_message' => 'private customer details',
        ], JSON_THROW_ON_ERROR).PHP_EOL);

        try {
            $response = $this->get('/api/support/incidents/'.$requestId);

            $response->assertOk()
                ->assertHeader('Cache-Control', 'no-store, private')
                ->assertHeader('Content-Disposition', 'attachment; filename="zazu-db001-'.$requestId.'.json"');

            $response->assertJsonPath('request_id', $requestId);
            $response->assertJsonPath('database_sqlstate', 'HY000');
            $response->assertJsonPath('database_driver_code', 1);
            $response->assertJsonPath('source_file', 'DashboardController.php');
            $response->assertJsonMissingPath('sql');
            $response->assertJsonMissingPath('bindings');
            $response->assertJsonMissingPath('http_path');
            $this->assertStringNotContainsString('private-customer-123', $response->getContent());
            $this->assertStringNotContainsString('secret@example.com', $response->getContent());
        } finally {
            File::delete($path);
        }
    }

    public function test_db001_reference_download_does_not_disclose_non_database_or_unknown_incidents(): void
    {
        config(['cache.default' => 'array']);

        $requestId = '895e2c23-bc6b-4919-a891-1e8e5c1e27e4';
        $path = storage_path('logs/laravel-db001-download-test.log');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, '[2026-11-18 01:00:00] local.ERROR: Zazu error incident '.json_encode([
            'request_id' => $requestId,
            'error_code' => 'APP-001',
            'exception_class' => 'RuntimeException',
        ], JSON_THROW_ON_ERROR).PHP_EOL);

        try {
            $this->get('/api/support/incidents/'.$requestId)->assertNotFound();
            $this->get('/api/support/incidents/'.'not-a-uuid')->assertNotFound();
        } finally {
            File::delete($path);
        }
    }
}
