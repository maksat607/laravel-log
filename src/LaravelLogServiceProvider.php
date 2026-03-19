<?php

namespace Maksatsaparbekov\LaravelLog;

use Illuminate\Support\ServiceProvider;

class LaravelLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-log');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-log'),
            ], 'laravel-log-views');
        }
    }
}
