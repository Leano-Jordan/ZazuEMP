<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureLocalSentinelRecovery
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->ensureLoopbackRequest($request);

        if ($request->isMethod('post')) {
            $this->ensureSameOriginPost($request);
        }

        abort_unless(in_array($request->getMethod(), ['GET', 'POST'], true), 405);

        return $next($request);
    }

    private function ensureLoopbackRequest(Request $request): void
    {
        $remoteAddress = (string) $request->server->get('REMOTE_ADDR', '');
        $host = $this->normaliseHost($request->getHost());

        abort_unless(
            in_array($remoteAddress, ['127.0.0.1', '::1'], true)
                && in_array($host, ['localhost', '127.0.0.1', '::1'], true),
            404
        );
    }

    private function ensureSameOriginPost(Request $request): void
    {
        $origin = parse_url((string) $request->headers->get('Origin', ''));
        $originHost = is_array($origin)
            ? $this->normaliseHost((string) ($origin['host'] ?? ''))
            : '';
        $originScheme = is_array($origin) ? ($origin['scheme'] ?? null) : null;
        $originPort = is_array($origin)
            ? (int) ($origin['port'] ?? ($originScheme === 'https' ? 443 : 80))
            : null;
        $contentType = strtolower((string) $request->headers->get('Content-Type', ''));

        abort_unless(
            str_starts_with($contentType, 'application/x-www-form-urlencoded')
                && $originHost === $this->normaliseHost($request->getHost())
                && $originScheme === $request->getScheme()
                && $originPort === $request->getPort()
                && $this->isSameOriginFetch($request),
            403
        );
    }

    private function isSameOriginFetch(Request $request): bool
    {
        $fetchSite = $request->headers->get('Sec-Fetch-Site');

        return $fetchSite === null || $fetchSite === 'same-origin';
    }

    private function normaliseHost(string $host): string
    {
        return strtolower(trim($host, '[]'));
    }
}
