<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('cspNonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=()');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; base-uri 'self'; frame-ancestors 'self'; object-src 'none'; form-action 'self'; img-src 'self' data: blob:; font-src 'self' data:; style-src 'self' 'nonce-{$nonce}'; style-src-elem 'self' 'nonce-{$nonce}'; style-src-attr 'none'; script-src 'self' 'nonce-{$nonce}'; connect-src 'self'; media-src 'self' blob:; worker-src 'self' blob:",
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
}
