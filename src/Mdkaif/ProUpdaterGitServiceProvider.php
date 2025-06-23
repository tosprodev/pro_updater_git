<?php

// File: pro_updater_git/src/Mdkaif/ProUpdaterGitServiceProvider.php
// This service provider registers the package's resources, commands, and views.

namespace Mdkaif\ProUpdaterGit; // Updated vendor namespace

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Mdkaif\ProUpdaterGit\Console\UpdateCommand; // Updated namespace
use Mdkaif\ProUpdaterGit\Console\SetupCommand;   // Updated namespace
use Mdkaif\ProUpdaterGit\Http\Middleware\CheckForUpdate; // Updated namespace
use Mdkaif\ProUpdaterGit\Http\Controllers\UpdateController; // Updated namespace
use Illuminate\Support\Facades\Route;

class ProUpdaterGitServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration file
        $this->publishes([
            __DIR__.'/../config/auto-updater.php' => config_path('auto-updater.php'),
        ], 'pro-updater-git-config'); // Tag remains the same, but belongs to Mdkaif/ProUpdaterGit

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish migrations (allows users to publish migrations if they need to modify them)
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'pro-updater-git-migrations'); // Tag remains the same

        // Load views from the package's resources/views directory
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'pro-updater-git'); // Unique namespace for views

        // Publish views (allows users to customize modals by publishing them to resources/views/vendor/pro-updater-git)
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/pro-updater-git'),
        ], 'pro-updater-git-views'); // Tag remains the same

        // Register commands (only when running in console)
        if ($this->app->runningInConsole()) {
            $this->commands([
                UpdateCommand::class,
                SetupCommand::class,
            ]);
        }

        // Register middleware alias for the main Laravel application (to be added in app/Http/Kernel.php by user)
        $router = $this->app['router'];
        $router->aliasMiddleware('pro-updater-check', CheckForUpdate::class);

        // Register package web routes
        $this->mapWebRoutes();

        // Register Blade directive for the update button
        Blade::directive('proUpdaterButton', function () {
            // Reference the package view using its unique namespace
            return "<?php echo \$__env->make('pro-updater-git::update-button')->render(); ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge package configuration with the application's configuration.
        $this->mergeConfigFrom(
            __DIR__.'/../config/auto-updater.php', 'auto-updater'
        );

        // Register the GitService as a singleton
        $this->app->singleton(GitService::class, function ($app) {
            return new GitService(
                config('auto-updater.repository_path'),
                config('auto-updater.version_file'),
                config('auto-updater.git_bin_path')
            );
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => ['web'],
            'prefix' => 'pro-updater',
            'as' => 'pro-updater.',
            'namespace' => 'Mdkaif\ProUpdaterGit\Http\Controllers' // Updated namespace
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }
}