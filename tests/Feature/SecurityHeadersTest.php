<?php

namespace Tests\Feature;

use App\Http\Middleware\ApplySecurityHeaders;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_include_csp(): void
    {
        $request = Request::create('https://zazu.test/', 'GET');
        $response = (new ApplySecurityHeaders())->handle(
            $request,
            fn () => new Response('ok'),
        );

        $this->assertSame(
            "default-src 'self'; base-uri 'self'; frame-ancestors 'self'; object-src 'none'; form-action 'self'; img-src 'self' data: blob:; font-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; connect-src 'self'; media-src 'self' blob:; worker-src 'self' blob:",
            $response->headers->get('Content-Security-Policy'),
        );
    }

    public function test_secure_production_responses_include_hsts(): void
    {
        $originalEnvironment = $this->app->environment();

        try {
            $this->app->instance('env', 'production');

            $request = Request::create('https://zazu.test/', 'GET');
            $response = (new ApplySecurityHeaders())->handle(
                $request,
                fn () => new Response('ok'),
            );

            $this->assertSame(
                'max-age=31536000; includeSubDomains',
                $response->headers->get('Strict-Transport-Security'),
            );
        } finally {
            $this->app->instance('env', $originalEnvironment);
        }
    }

    public function test_http_production_responses_do_not_include_hsts(): void
    {
        $originalEnvironment = $this->app->environment();

        try {
            $this->app->instance('env', 'production');

            $request = Request::create('http://zazu.test/', 'GET');
            $response = (new ApplySecurityHeaders())->handle(
                $request,
                fn () => new Response('ok'),
            );

            $this->assertNull($response->headers->get('Strict-Transport-Security'));
        } finally {
            $this->app->instance('env', $originalEnvironment);
        }
    }
}
