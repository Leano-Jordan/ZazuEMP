<?php

namespace Tests\Feature;

use App\Support\ZazuIncidentRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class IncidentRecorderResilienceTest extends TestCase
{
    public function test_incident_ledger_paths_receive_private_posix_permissions(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('POSIX mode bits are not authoritative on Windows; verify NTFS ACLs in package acceptance.');
        }

        $request = Request::create('/__zazu-test-ledger-permissions', 'GET');
        $request->attributes->set('zazu_request_id', 'd4e85f8e-b2c4-4c63-9d9d-25f47f4e1a30');
        $this->app->instance('request', $request);

        $directory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($directory);

        try {
            ZazuIncidentRecorder::record(new \\RuntimeException('permission verification'));

            clearstatcache(true, $directory);
            clearstatcache(true, $directory.DIRECTORY_SEPARATOR.'incidents.lock');
            clearstatcache(true, $directory.DIRECTORY_SEPARATOR.'incidents.jsonl');

            $this->assertSame(0700, fileperms($directory) & 0777);
            $this->assertSame(0600, fileperms($directory.DIRECTORY_SEPARATOR.'incidents.lock') & 0777);
            $this->assertSame(0600, fileperms($directory.DIRECTORY_SEPARATOR.'incidents.jsonl') & 0777);
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
