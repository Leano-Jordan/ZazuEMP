<?php

namespace App\Http\Controllers;

use App\Support\ZazuLocalSentinelRecovery;
use App\Support\ZazuSystemHealth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class SentinelLocalRecoveryController extends Controller
{
    public function show(): Response
    {
        return $this->privateResponse([
            'health' => null,
            'verified' => false,
            'error' => null,
        ]);
    }

    public function verify(Request $request, ZazuLocalSentinelRecovery $recovery, ZazuSystemHealth $health): Response
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/D'],
        ]);

        if (! $recovery->consume($validated['code'])) {
            return $this->privateResponse([
                'health' => null,
                'verified' => false,
                'error' => 'That recovery code is invalid, expired or already used. Generate a new code on this PC.',
            ], 403);
        }

        return $this->privateResponse([
            'health' => $health->inspect(),
            'verified' => true,
            'error' => null,
        ]);
    }

    private function privateResponse(array $data, int $status = 200): Response
    {
        return response()
            ->view('sentinel.local-recovery', $data, $status)
            ->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
