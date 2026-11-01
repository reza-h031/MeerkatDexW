<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->ip());
        });

        RateLimiter::for('auth-login', function (Request $request) {
            $email = Str::lower(
                trim((string) $request->input('email'))
            );

            return Limit::perMinute(5)
                ->by($request->ip() . '|' . $email);
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip());
        });
    }
}