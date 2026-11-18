<?php

namespace App\Http\Controllers;

use App\Support\ZazuSystemHealth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

final class SentinelRescueController extends Controller
{
    public function show(ZazuSystemHealth $health): Response
    {
        return response()
            ->view('sentinel.rescue', [
                'health' => $health->inspect(),
                'phpVersion' => PHP_VERSION,
                'laravelVersion' => App::version(),
            ])
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
