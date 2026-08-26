<?php

namespace App\Providers;

use App\Services\Contracts\SiteStructureGenerator;
use App\Services\OpenAiSiteStructureGenerator;
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
        $this->app->bind(SiteStructureGenerator::class, OpenAiSiteStructureGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        RateLimiter::for('site-structure-writes', fn (Request $request): Limit => Limit::perMinute((int) config('site_structure.writes_per_minute'))
            ->by((string) $request->bearerToken()));
        RateLimiter::for('site-structure-reads', fn (Request $request): Limit => Limit::perMinute((int) config('site_structure.reads_per_minute'))
            ->by((string) $request->bearerToken()));
    }
}
