<?php

namespace App\Providers;

use App\Models\MyMedia;
use App\Observers\MediaObserver;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
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
}
}
