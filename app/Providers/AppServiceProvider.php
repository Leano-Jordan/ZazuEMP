<?php

namespace App\Providers;

use Illuminate\Http\Request;\nuse Illuminate\Cache\RateLimiting\Limit;\nuse Illuminate\Support\Facades\RateLimiter;\nuse Illuminate\Support\ServiceProvider;\nuse Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
