<?php

namespace App\Http\Middleware;

use App\Support\CurrentBusiness;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveBusinessContext
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user() && app(CurrentBusiness::class)->resolve($request->user()),
            403,
            'An active business workspace is required.'
        );

        return $next($request);
    }
}
