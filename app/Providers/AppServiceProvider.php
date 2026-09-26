<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        RateLimiter::for('login', function (Request $request) {
            $identifier = Str::lower(trim($request->string('identifier')->toString()));

            return [
                Limit::perMinute(6)->by('login:identifier-ip:'.$identifier.'|'.$request->ip()),
                Limit::perMinute(30)->by('login:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('password.email', function (Request $request) {
            $identifier = Str::lower(trim($request->string('identifier')->toString()));

            return [
                Limit::perMinute(5)->by('password-email:'.$identifier.'|'.$request->ip()),
                Limit::perMinute(20)->by('password-email:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('password.reset', function (Request $request) {
            return Limit::perMinute(10)->by('password-reset:ip:'.$request->ip());
        });
    }
}
