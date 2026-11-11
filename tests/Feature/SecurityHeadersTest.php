<?php

namespace Tests\Feature;

use App\Http\Middleware\ApplySecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_include_a_per_response_script_nonce(): void
    {
        [$firstResponse, $firstNonce] = $this->runSecurityMiddleware();
        [$secondResponse, $secondNonce] = $this->runSecurityMiddleware();

        $firstCsp = (string) $firstResponse->headers->get('Content-Security-Policy');
        $secondCsp = (string) $secondResponse->headers->get('Content-Security-Policy');

        $this->assertNotEmpty($firstNonce);
        $this->assertNotEmpty($secondNonce);
        $this->assertNotSame($firstNonce, $secondNonce);
        $this->assertStringContainsString("'nonce-{$firstNonce}'", $firstCsp);
        $this->assertStringContainsString("'nonce-{$secondNonce}'", $secondCsp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $firstCsp);
        $this->assertStringContainsString("style-src 'self' 'nonce-{$firstNonce}'", $firstCsp);
        $this->assertStringContainsString("style-src-elem 'self' 'nonce-{$firstNonce}'", $firstCsp);
        $this->assertStringContainsString("style-src-attr 'none'", $firstCsp);
        $this->assertStringNotContainsString("style-src-attr 'unsafe-inline'", $firstCsp);
        $this->assertStringNotContainsString("'unsafe-inline'", $firstCsp);
    }

    public function test_inline_style_elements_are_nonce_authorized_in_all_standalone_blade_views(): void
    {
        $views = [
            'errors/layout.blade.php',
            'landing.blade.php',
            'purchasing/create.blade.php',
        ];

        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));
        $this->assertIsString($layout);
        $this->assertStringContainsString('<style nonce="{{ request()->attributes->get(\'cspNonce\') }}">', $layout);
        $this->assertStringContainsString('--zazu-dashboard-image: url(', $layout);
        $this->assertStringContainsString('--zazu-wallpaper: url(', $layout);
        preg_match('/<body[^\\n]*/', $layout, $bodyTag);
        $this->assertNotEmpty($bodyTag);
        $this->assertStringNotContainsString(' style=', $bodyTag[0]);

        $landing = file_get_contents(resource_path('views/landing.blade.php'));
        $adminLanding = file_get_contents(resource_path('views/admin/landing-settings.blade.php'));
        $recovery = file_get_contents(resource_path('views/auth/workspace-recovery.blade.php'));
        foreach ([$landing, $adminLanding, $recovery] as $source) {
            $this->assertIsString($source);
            $this->assertDoesNotMatchRegularExpression('/\\sstyle\\s*=/i', $source);
        }
        $this->assertStringContainsString('landing-image--hero', $landing);
        $this->assertStringContainsString('--landing-image-hero: url(', $landing);
        $this->assertStringContainsString('zazu-admin-image-preview-{{ $field }}', $adminLanding);
        $this->assertStringContainsString('class="zazu-auth-inline-form"', $recovery);

        foreach ($views as $view) {
            $source = file_get_contents(resource_path('views/'.$view));

            $this->assertIsString($source);
            $this->assertStringContainsString(
                '<style nonce="{{ request()->attributes->get(\'cspNonce\') }}">',
                $source,
                "Inline style block in {$view} must use the response CSP nonce.",
            );
        }
    }

    public function test_active_blade_views_and_javascript_do_not_require_inline_style_attributes(): void
    {
        foreach (File::allFiles(resource_path('views')) as $view) {
            if (! str_ends_with($view->getFilename(), '.blade.php')) {
                continue;
            }

            $source = file_get_contents($view->getPathname());
            $this->assertIsString($source);
            $this->assertDoesNotMatchRegularExpression(
                '/\\sstyle\\s*=/i',
                $source,
                "Inline style attribute found in {$view->getRelativePathname()}.",
            );

            preg_match_all('/<style\\b([^>]*)>/i', $source, $styleBlocks);
            foreach ($styleBlocks[1] as $attributes) {
                $this->assertMatchesRegularExpression(
                    '/\\bnonce\\s*=\\s*["\\']/',
                    $attributes,
                    "Inline style block in {$view->getRelativePathname()} must carry a CSP nonce.",
                );
            }
        }

        $appJs = file_get_contents(resource_path('js/app.js'));
        $this->assertIsString($appJs);
        $this->assertDoesNotMatchRegularExpression('/\\btoast\\.style\\./', $appJs);
        $this->assertStringContainsString("toast.classList.add('is-closing')", $appJs);
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

    /**
     * @return array{Response, string}
     */
    private function runSecurityMiddleware(): array
    {
        $request = Request::create('https://zazu.test/', 'GET');
        $nonce = '';

        $response = (new ApplySecurityHeaders())->handle(
            $request,
            function (Request $request) use (&$nonce): Response {
                $nonce = (string) $request->attributes->get('cspNonce');

                return new Response('ok');
            },
        );

        return [$response, $nonce];
    }
}
