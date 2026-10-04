<?php

namespace App\Http\Middleware;

use App\Models\SyncDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSyncDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if (! $plainToken) {
            return response()->json(['message' => 'A Zazu sync device token is required.'], 401);
        }

        $hash = hash('sha256', $plainToken);
        $device = SyncDevice::query()
            ->active()
            ->where('metadata->sync_token_hash', $hash)
            ->first();

        if (! $device) {
            return response()->json(['message' => 'The Zazu sync device token is invalid or revoked.'], 401);
        }

        $request->attributes->set('sync_device', $device);

        return $next($request);
    }
}
