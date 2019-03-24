<?php

namespace Shamaseen\Laravel\Ratchet;

use Illuminate\Support\ServiceProvider;

/**
 * Class GeneratorServiceProvider
 * @package Shamaseen\Repository\Generator
 */
class LaravelRatchetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebSocketService::class
            ]);
        }

        $this->publishes([
            __DIR__.'/config' => realpath('config'),
        ],'laravel-ratchet');

        if ($this->app['config']->get('laravel-ratchet') === null) {
            $this->app['config']->set('laravel-ratchet', require __DIR__.'config/laravel-ratchet.php');
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    }
}
