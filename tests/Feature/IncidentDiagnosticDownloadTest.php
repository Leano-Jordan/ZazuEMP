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
            'exception_class' => 'Illuminate\Database\QueryException',
            'originating_component' => 'App\Http\Controllers\DashboardController@index',
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

    public function test_db001_reference_download_uses_independent_ledger_and_includes_stable_fingerprint(): void
    {
        config(['cache.default' => 'array']);

        $requestId = 'b17e9a51-891c-48b6-9d71-1f24e6ab4c82';
        $directory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($directory);

        try {
            \App\Support\ZazuIncidentLedger::record([
                'request_id' => $requestId,
                'error_code' => 'DB-001',
                'category' => 'Database',
                'severity' => 'high',
                'http_status' => 500,
                'exception_class' => 'Illuminate\Database\QueryException',
                'originating_component' => 'App\Http\Controllers\DashboardController@index',
                'route_name' => 'dashboard',
                'http_method' => 'GET',
                'timestamp' => '2026-11-18T11:00:00+02:00',
                'file' => '/srv/zazu/app/Http/Controllers/DashboardController.php',
                'line' => 81,
                'database_connection' => 'sqlite',
                'database_driver' => 'sqlite',
                'database_sqlstate' => 'HY000',
                'database_driver_code' => 1,
                'database_operation' => 'write',
                'database_failure_class' => 'sqlite_missing_column',
                'sql' => 'must never be persisted',
                'bindings' => ['private@example.com'],
                'exception_message' => 'private customer details',
            ]);

            $response = $this->get('/api/support/incidents/'.$requestId);

            $response->assertOk()
                ->assertJsonPath('request_id', $requestId)
                ->assertJsonPath('source_file', 'DashboardController.php')
                ->assertJsonPath('database_failure_class', 'sqlite_missing_column')
                ->assertJsonPath('failure_fingerprint_version', 1);

            $fingerprint = $response->json('failure_fingerprint');
            $this->assertIsString($fingerprint);
            $this->assertMatchesRegularExpression('/^ZAZU-[A-F0-9]{16}$/', $fingerprint);
            $this->assertStringNotContainsString('must never be persisted', $response->getContent());
            $this->assertStringNotContainsString('private@example.com', $response->getContent());
            $this->assertStringNotContainsString('private customer details', $response->getContent());
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_sentinel_summary_groups_repeated_fingerprints_without_exposing_private_payloads(): void
    {
        $directory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);

        $fingerprint = 'ZAZU-0123456789ABCDEF';
        $records = [
            [
                'request_id' => 'b17e9a51-891c-48b6-9d71-1f24e6ab4c82',
                'failure_fingerprint' => $fingerprint,
                'error_code' => 'DB-001',
                'database_failure_class' => 'sqlite_missing_column',
                'exception_class' => 'Illuminate\\Database\\QueryException',
                'source_file' => 'DashboardController.php',
                'timestamp' => '2026-11-18T10:00:00+02:00',
                'sql' => 'private SQL must not appear',
            ],
            [
                'request_id' => '895e2c23-bc6b-4919-a891-1e8e5c1e27e4',
                'failure_fingerprint' => $fingerprint,
                'error_code' => 'DB-001',
                'database_failure_class' => 'sqlite_missing_column',
                'exception_class' => 'Illuminate\\Database\\QueryException',
                'source_file' => 'DashboardController.php',
                'timestamp' => '2026-11-18T11:00:00+02:00',
            ],
        ];

        File::put(
            $directory.DIRECTORY_SEPARATOR.'incidents.jsonl',
            implode(PHP_EOL, array_map(
                static fn (array $record): string => json_encode($record, JSON_THROW_ON_ERROR),
                $records,
            )).PHP_EOL,
        );

        try {
            $summary = app(\App\Support\ZazuIncidentSummary::class)->build();

            $this->assertCount(1, $summary);
            $this->assertSame($fingerprint, $summary[0]['fingerprint']);
            $this->assertSame(2, $summary[0]['occurrences']);
            $this->assertSame('2026-11-18T10:00:00+02:00', $summary[0]['first_seen']);
            $this->assertSame('2026-11-18T11:00:00+02:00', $summary[0]['last_seen']);
            $this->assertSame('895e2c23-bc6b-4919-a891-1e8e5c1e27e4', $summary[0]['latest_reference']);
            $this->assertArrayNotHasKey('sql', $summary[0]);
            $this->assertStringNotContainsString('private SQL', json_encode($summary, JSON_THROW_ON_ERROR));

            $timeline = app(\App\Support\ZazuIncidentSummary::class)->timeline($fingerprint);
            $this->assertCount(2, $timeline);
            $this->assertSame('895e2c23-bc6b-4919-a891-1e8e5c1e27e4', $timeline[0]['request_id']);
            $this->assertArrayNotHasKey('sql', $timeline[0]);
            $this->assertStringNotContainsString('private SQL', json_encode($timeline, JSON_THROW_ON_ERROR));
        } finally {
            File::deleteDirectory($directory);
        }
    }

}
