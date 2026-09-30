<?php

namespace Centralog\ErrorMonitoring;

use Illuminate\Support\ServiceProvider;

class CentralogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/centralog.php', 'centralog');

        $this->app->singleton(CentralogClient::class, fn ($app): CentralogClient => new CentralogClient(
            endpoint: (string) config('centralog.endpoint'),
            apiKey: (string) config('centralog.api_key'),
            environment: (string) config('centralog.environment', 'production'),
            release: config('centralog.release'),
            timeout: (int) config('centralog.timeout', 2),
            enabled: (bool) config('centralog.enabled', true),
        ));

        $this->app->alias(CentralogClient::class, 'centralog');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/centralog.php' => config_path('centralog.php'),
            ], 'centralog-config');
        }
    }
}
