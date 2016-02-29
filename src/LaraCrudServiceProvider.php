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
        $this->loadViewsFrom(__DIR__.'/views', 'lara_crud');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

}