<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
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
        JsonResource::withoutWrapping();
        RateLimiter::for('cms-writes', fn (Request $request): Limit => Limit::perMinute((int) config('cms.writes_per_minute'))
            ->by((string) $request->bearerToken()));
        RateLimiter::for('cms-uploads', fn (Request $request): Limit => Limit::perMinute((int) config('cms.uploads_per_minute'))
            ->by((string) $request->bearerToken()));
    }
}
