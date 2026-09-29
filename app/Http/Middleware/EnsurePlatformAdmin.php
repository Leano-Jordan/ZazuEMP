<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $allowedEmails = config('zazu.platform_admin_emails', []);

        abort_unless(
            $user && in_array(strtolower((string) $user->email), array_map('strtolower', $allowedEmails), true),
            403,
            'Platform administrator access is required.'
        );

        return $next($request);
    }
}
