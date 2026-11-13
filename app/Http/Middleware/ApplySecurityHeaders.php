<?php

namespace App\\Http\\Middleware;

use Closure;
use Illuminate\\Http\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

class ApplySecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('cspNonce', $nonce);

        $response = $next($request);
        $viteOrigin = $this->localViteOrigin();
        $viteScriptStyleSource = $viteOrigin ? " {$viteOrigin}" : '';
        $viteSocketSource = $viteOrigin
            ? ' '.(str_starts_with($viteOrigin, 'https://') ? 'wss://' : 'ws://').substr($viteOrigin, strpos($viteOrigin, '://') + 3)
            : '';

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=()');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; base-uri 'self'; frame-ancestors 'self'; object-src 'none'; form-action 'self'; img-src 'self' data: blob:; font-src 'self' data:; style-src 'self' 'nonce-{$nonce}'{$viteScriptStyleSource}; style-src-elem 'self' 'nonce-{$nonce}'{$viteScriptStyleSource}; style-src-attr 'none'; script-src 'self' 'nonce-{$nonce}'{$viteScriptStyleSource}; connect-src 'self'{$viteScriptStyleSource}{$viteSocketSource}; media-src 'self' blob:; worker-src 'self' blob:",
        );

        // Sync endpoints return bearer credentials and business-scoped records.
        // The owner pairing endpoint also returns a one-time pairing code.
        // These responses must not survive in browser or intermediary HTTP caches.
        if ($request->is('api/*') || $request->is('settings/sync/*')) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        if (app()->environment('production') && ($request->isSecure() || $request->server->get('HTTPS') === 'on')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
            );
        }

        return $response;
    }

    /**
     * Permit only a private-network Vite origin in local development.
     * The hot file is runtime-local and is never trusted in production.
     */
    private function localViteOrigin(): ?string
    {
        if (! app()->environment('local')) {
            return null;
        }

        $hotFile = public_path('hot');
        if (! is_file($hotFile)) {
            return null;
        }

        $url = trim((string) @file_get_contents($hotFile));
        $parts = parse_url($url);
        if (! is_array($parts)
            || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            || empty($parts['host'])
            || empty($parts['port'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
            || ! in_array($parts['path'] ?? '', ['', '/'], true)
        ) {
            return null;
        }

        $host = strtolower(trim($parts['host'], '[]'));
        $isLocalhost = $host === 'localhost';
        $ip = filter_var($host, FILTER_VALIDATE_IP);
        $isPrivateIp = $ip !== false
            && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;

        if (! $isLocalhost && ! $isPrivateIp) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].':'.$parts['port'];
    }
}
