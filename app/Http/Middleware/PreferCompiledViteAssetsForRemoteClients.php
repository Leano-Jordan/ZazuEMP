<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreferCompiledViteAssetsForRemoteClients
{
    /**
     * Keep HMR enabled only for local browsers that can reach the Vite server.
     *
     * A stale public/hot file otherwise makes Laravel emit links to a dead
     * dev server, leaving the desktop app without its compiled CSS/JS.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isLoopback = in_array($request->ip(), ['127.0.0.1', '::1'], true);
        $useHotServer = $isLoopback && $this->isHotServerReachable();

        app(Vite::class)->useHotFile(
            $useHotServer
                ? public_path('hot')
                : storage_path('framework/zazu/remote-vite-hot-disabled'),
        );

        return $next($request);
    }

    private function isHotServerReachable(): bool
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return false;
        }

        $url = trim((string) file_get_contents($hotFile));
        $parts = parse_url($url);
        $host = $parts['host'] ?? null;

        if (! is_array($parts) || ! is_string($host) || $host === '') {
            return false;
        }

        $scheme = $parts['scheme'] ?? 'http';
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        if (! is_int($port) || $port < 1 || $port > 65535) {
            return false;
        }

        $connection = @fsockopen($host, $port, $errorCode, $errorMessage, 0.2);

        if (! is_resource($connection)) {
            return false;
        }

        fclose($connection);

        return true;
    }
}
