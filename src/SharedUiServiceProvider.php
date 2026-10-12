<?php

namespace UniqueWorkbench\SharedUi;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use UniqueWorkbench\SharedUi\FeatureApi\VerifyConnectionToken;
use UniqueWorkbench\SharedUi\FeatureApi\VerifyFeatureApiKey;
use UniqueWorkbench\SharedUi\Mail\WorkbenchMailTransport;
use UniqueWorkbench\SharedUi\Workbench\Connections;
use UniqueWorkbench\SharedUi\Workbench\Directory;
use UniqueWorkbench\SharedUi\Workbench\DirectoryChangedController;
use UniqueWorkbench\SharedUi\Workbench\EnsureWorkbenchContext;
use UniqueWorkbench\SharedUi\Workbench\Workbench;

class SharedUiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/shared-ui.php', 'shared-ui');

        // Client apps' organization context (workbench()) and the account app's shared directory
        require_once __DIR__ . '/Workbench/helpers.php';
        $this->app->scoped(Workbench::class, fn ($app) => new Workbench($app['session.store']));
        $this->app->singleton(Directory::class);
        // Other apps' shared data, through connection tokens from the account app
        $this->app->singleton(Connections::class);
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

        // The UI kit (the standard look, built from the shared components), outside production
        $this->app->booted(function () {
            if (! $this->app->environment('production') && ! $this->app->routesAreCached()) {
                $this->app['router']->middleware(['web', 'auth'])->get('/ui-kit', fn () => view('shared-ui::ui-kit'))->name('shared-ui.ui-kit');
            }
        });

        // The `workbench` mail driver (MAIL_MAILER=workbench): email goes out through the account app's mail relay,
        // so apps need no mail provider settings. Defined here so apps' own config/mail.php needn't list it.
        Mail::extend('workbench', fn () => new WorkbenchMailTransport());
        if (! config()->has('mail.mailers.workbench')) {
            config(['mail.mailers.workbench' => ['transport' => 'workbench']]);
        }

        // Guards routes the account app's App Features read (X-Api-Key = FEATURE_API_KEY)
        $this->app['router']->aliasMiddleware('feature-api', VerifyFeatureApiKey::class);
        // Guards what the app shares with other apps (config/workbench.php `provides`): the Feature API key
        // or a connection token the account app issued for that share — `workbench.share:service-codes`
        $this->app['router']->aliasMiddleware('workbench.share', VerifyConnectionToken::class);
        // Signed-in pages get the organization context, or go back through SSO (client apps add it to `web`)
        $this->app['router']->aliasMiddleware('workbench', EnsureWorkbenchContext::class);

        // Client apps built on the workbench (they have a config/workbench.php manifest)
        if (config()->has('workbench.audiences')) {
            // A gate per app permission, e.g. @can('schedule.publish')
            foreach (array_keys(config('workbench.permissions', [])) as $permission) {
                Gate::define($permission, fn () => workbench()->can($permission));
            }

            $this->app->booted(function () {
                if (! $this->app->routesAreCached()) {
                    // The account app says an organization's shared directory changed: drop the cached copies
                    $this->app['router']->middleware(['api', 'feature-api', 'throttle:120,1'])
                        ->post('/api/features/directory-changed', DirectoryChangedController::class)
                        ->name('features.directory-changed');
                    // The permissions, contact types, shares and uses the app declares, for the account app to sync
                    $this->app['router']->middleware(['api', 'feature-api', 'throttle:120,1'])
                        ->get('/api/features/permissions', fn () => [
                            'permissions' => Workbench::declaredPermissions(),
                            'contact_types' => Workbench::declaredContactTypes(),
                            'provides' => Workbench::declaredShares(),
                            'uses' => Workbench::declaredUses(),
                        ])
                        ->name('features.permissions');
                }
            });
        }

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/shared-ui'),
        ], 'shared-ui-views');

        $this->publishes([
            __DIR__ . '/../config/shared-ui.php' => config_path('shared-ui.php'),
        ], 'shared-ui-config');
    }
}