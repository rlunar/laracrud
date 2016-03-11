<?php

namespace LaraCrud;

use Illuminate\Support\ServiceProvider;

class LaraCrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
    	// Load views for CRUD
        $this->loadViewsFrom(__DIR__.'/views', 'lara_crud');

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/laracrud'),
        ]);

        // $this->commands([
		//     Acme\MyCommand::class
		// ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('laracrud', function ($app) {
            return new LaraCrud();
        });
    }

}