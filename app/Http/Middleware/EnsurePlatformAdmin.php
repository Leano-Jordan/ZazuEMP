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
        $founderEmail = (string) config('zazu.platform_admin_email', '');

        abort_unless(
            $user
                && $founderEmail !== ''
                && hash_equals($founderEmail, strtolower(trim((string) $user->email))),
            403,
            'Founder access is required.'
        );

        return $next($request);
    }
}
