<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureLocalSentinelRecovery
{
    public function handle(Request $request, Closure $next): Response
    {
        $remoteAddress = (string) $request->server->get('REMOTE_ADDR', '');
        $host = strtolower(trim($request->getHost(), '[]'));

        abort_unless(
            in_array($remoteAddress, ['127.0.0.1', '::1'], true)
                && in_array($host, ['localhost', '127.0.0.1', '::1'], true),
            404
        );

        if ($request->isMethod('post')) {
            $origin = parse_url((string) $request->headers->get('Origin', ''));
            $originHost = is_array($origin)
                ? strtolower(trim((string) ($origin['host'] ?? ''), '[]'))
                : '';
            $originScheme = is_array($origin) ? ($origin['scheme'] ?? null) : null;
            $fetchSite = $request->headers->get('Sec-Fetch-Site');

            abort_unless(
                $request->isJson() === false
                    && in_array($request->getMethod(), ['POST'], true)
                    && $originHost === $host
                    && $originScheme === $request->getScheme()
                    && ($fetchSite === null || $fetchSite === 'same-origin'),
                403
            );
        }

        abort_unless(in_array($request->getMethod(), ['GET', 'POST'], true), 405);

        return $next($request);
    }
}
