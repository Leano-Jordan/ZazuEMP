<?php

namespace Tests\\Feature;

use App\\Support\\ZazuIncidentRecorder;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\File;
use Illuminate\\Support\\Facades\\Log;
use Tests\\TestCase;

class IncidentRecorderResilienceTest extends TestCase
{
    public function test_incident_capture_survives_a_broken_primary_and_fallback_log_channel(): void
    {
        $requestId = '4b2c8d39-1b17-4b1c-a2f2-4d6e0a7c9f31';
        $request = Request::create('/__zazu-test-log-outage', 'GET');
        $request->attributes->set('zazu_request_id', $requestId);
        $this->app->instance('request', $request);

        $directory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);

        Log::shouldReceive('log')->once()->andThrow(new \RuntimeException('primary log unavailable'));
        Log::shouldReceive('error')->once()->andThrow(new \RuntimeException('fallback log unavailable'));

        try {
            ZazuIncidentRecorder::record(new \RuntimeException('private exception details'));

            $ledger = File::get($directory.DIRECTORY_SEPARATOR.'incidents.jsonl');
            $this->assertStringContainsString($requestId, $ledger);
            $this->assertStringNotContainsString('private exception details', $ledger);
            $this->assertStringNotContainsString('primary log unavailable', $ledger);
        } finally {
            File::deleteDirectory($directory);
        }
    }

}
