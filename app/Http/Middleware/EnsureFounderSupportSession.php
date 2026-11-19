<?php

namespace App\Http\Middleware;

use App\Models\FounderSupportSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFounderSupportSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportSession = $request->route('supportSession');
        $sessionId = $request->session()->get('zazu_founder_support_session_id');

        abort_unless(
            $supportSession instanceof FounderSupportSession
                && $sessionId !== null
                && (int) $sessionId === (int) $supportSession->id
                && (int) $supportSession->actor_user_id === (int) $request->user()->getAuthIdentifier()
                && $supportSession->isActive(),
            403,
            'An active founder support session is required.'
        );

        return $next($request);
    }
}
