<?php

namespace Amghrby\Workflows;

use Illuminate\Support\ServiceProvider;

class WorkflowsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register package services
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Load API routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/workflows.php' => config_path('workflows.php'),
        ], 'config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');
    }
}
