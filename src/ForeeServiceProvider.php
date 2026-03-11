<?php

namespace smartlane\Foree;

use Illuminate\Support\ServiceProvider;
use smartlane\Foree\Services\ForeeClient;
use smartlane\Foree\Services\ForeeBillService;

class ForeeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge default config (so .env values work even without publishing)
        $this->mergeConfigFrom(__DIR__ . '/../config/foree.php', 'foree');

        // Bind ForeeClient as singleton — reads from config automatically
        $this->app->singleton(ForeeClient::class, fn () => ForeeClient::fromConfig());

        // Bind ForeeBillService — depends on ForeeClient
        $this->app->singleton(ForeeBillService::class, function ($app) {
            return new ForeeBillService($app->make(ForeeClient::class));
        });

        // Alias for Facade
        $this->app->alias(ForeeBillService::class, 'foree');
    }

    public function boot(): void
    {
        // Allow projects to publish config: php artisan vendor:publish --tag=foree-config
        $this->publishes([
            __DIR__ . '/../config/foree.php' => config_path('foree.php'),
        ], 'foree-config');

        // Allow projects to publish migration: php artisan vendor:publish --tag=foree-migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'foree-migrations');

        // Load migrations automatically (no need to publish if they prefer this)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load package routes (webhook endpoint)
        $this->loadRoutesFrom(__DIR__ . '/../routes/foree.php');
    }
}
