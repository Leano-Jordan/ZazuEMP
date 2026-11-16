<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreferCompiledViteAssetsForRemoteClients
{
    /**
     * Keep the Vite HMR marker local to loopback browser sessions.
     *
     * LAN clients cannot reach a Vite server bound to the owner's loopback
     * interface. For every non-loopback request, force Laravel to resolve
     * stylesheets and scripts through the compiled, same-origin manifest.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isLoopback = in_array($request->ip(), ['127.0.0.1', '::1'], true);

        app(Vite::class)->useHotFile(
            $isLoopback
                ? public_path('hot')
                : storage_path('framework/zazu/remote-vite-hot-disabled'),
        );

        return $next($request);
    }
}
