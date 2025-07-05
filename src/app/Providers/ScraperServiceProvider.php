<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Scraper\BrightDataScraperService;
use App\Services\GeminiApiService;

class ScraperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BrightDataScraperService::class, function ($app) {
            return new BrightDataScraperService(
                $app->make(GeminiApiService::class)
            );
        });

        // Register other scraper services here in the future
        $this->app->bind('scraper.brightdata', BrightDataScraperService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
