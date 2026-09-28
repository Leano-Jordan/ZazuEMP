<?php

namespace App\Http\Middleware;

use App\Support\CurrentBusiness;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveBusinessContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $business = $user
            ? app(CurrentBusiness::class)->resolve($user)
            : null;

        abort_unless(
            $user && $business,
            403,
            'An active business workspace is required.'
        );

        // Route model binding happens before route middleware. Enforce a
        // structural ownership check for every bound model that declares its
        // business directly, so a controller cannot accidentally expose a
        // foreign-business record by forgetting a local where business_id.
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if (!$parameter instanceof Model) {
                continue;
            }

            $recordBusinessId = $parameter->getAttribute('business_id');

            if ($recordBusinessId === null) {
                continue;
            }

            abort_unless(
                (int) $recordBusinessId === (int) $business->id,
                404,
                'The requested record was not found.'
            );
        }

        return $next($request);
    }
}
