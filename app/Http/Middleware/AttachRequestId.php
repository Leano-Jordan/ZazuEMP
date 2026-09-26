<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AttachRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();

        $request->attributes->set('zazu_request_id', $requestId);

        Log::withContext([
            'zazu_request_id' => $requestId,
            'http_method' => $request->method(),
            'http_path' => $request->path(),
        ]);

        $response = $next($request);

        $response->headers->set('X-Zazu-Request-Id', $requestId);

        return $response;
    }
}