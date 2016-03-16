<?php

namespace LaraCrud;

use Illuminate\Support\ServiceProvider;

class LaraCrudServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Load views for CRUD
        $this->loadViewsFrom(__DIR__.'/views', 'lara_crud');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/laracrud'),
        ]);

        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations')
        ], 'migrations');

        $this->app->singleton('laracrud', function ($app) {
            return new LaraCrud();
        });
    }

}