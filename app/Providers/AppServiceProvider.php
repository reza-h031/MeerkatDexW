<?php

namespace App\Providers;

use App\Models\MyMedia;
use App\Observers\MediaObserver;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

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
        MyMedia::observe(MediaObserver::class);
        JsonResource::withoutWrapping();
    }
}
