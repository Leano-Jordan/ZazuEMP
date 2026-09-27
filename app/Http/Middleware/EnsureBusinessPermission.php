<?php

namespace App\Http\Middleware;

use App\Support\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        abort_unless(
            app(PermissionService::class)->allows($permission, $request->user()),
            403,
            'You do not have permission for this action.'
        );

        return $next($request);
    }
}