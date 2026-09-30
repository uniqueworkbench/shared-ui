<?php

namespace UniqueWorkbench\SharedUi;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use UniqueWorkbench\SharedUi\FeatureApi\VerifyFeatureApiKey;

class SharedUiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/shared-ui.php', 'shared-ui');
    }

    public function boot(): void
    {
        // Add package views to the global view finder (makes @extends('layouts.app') work)
        $this->callAfterResolving('view', function ($view) {
            $view->addLocation(__DIR__ . '/../resources/views');
        });

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'shared-ui');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components');

        // Guards routes the account app's App Features read (X-Api-Key = FEATURE_API_KEY)
        $this->app['router']->aliasMiddleware('feature-api', VerifyFeatureApiKey::class);

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/shared-ui'),
        ], 'shared-ui-views');

        $this->publishes([
            __DIR__ . '/../config/shared-ui.php' => config_path('shared-ui.php'),
        ], 'shared-ui-config');
    }
}