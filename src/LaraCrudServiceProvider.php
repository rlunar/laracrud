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
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/laracrud'),
        ]);

        $this->app->singleton('laracrud', function ($app) {
            return new LaraCrud();
        });
    }

}