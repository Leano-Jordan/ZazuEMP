<?php

namespace App\Http\Middleware;

use App\Support\CurrentBusiness;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $business = app(CurrentBusiness::class)->resolve($user);

        abort_unless(
            $user && $business,
            403,
            'An active business owner context is required.'
        );

        abort_unless(
            $user->businesses()
                ->whereKey($business->id)
                ->wherePivot('role', 'owner')
                ->exists(),
            403,
            'Owner access is required.'
        );

        return $next($request);
    }
}
