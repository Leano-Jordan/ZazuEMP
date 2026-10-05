<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_secure_production_responses_include_hsts(): void
    {
        $originalEnvironment = $this->app->environment();

        try {
            $this->app->instance('env', 'production');

            $response = $this->withServerVariables([
                'HTTPS' => 'on',
            ])->get('/');

            $response->assertHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
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

            $response = $this->get('/');

            $response->assertHeaderMissing('Strict-Transport-Security');
        } finally {
            $this->app->instance('env', $originalEnvironment);
        }
    }
}
