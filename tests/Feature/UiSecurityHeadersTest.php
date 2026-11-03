<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiSecurityHeadersTest extends TestCase
{
    public function test_api_responses_include_security_headers_and_request_ids(): void
    {
        $response = $this->postJson('/api/sync/provision', []);

        $response->assertUnprocessable();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=()');
        $response->assertHeader('X-Zazu-Request-Id');
    }

    public function test_web_responses_include_baseline_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=()');
    }
}
