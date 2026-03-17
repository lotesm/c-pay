<?php

namespace CPay;

use Illuminate\Support\ServiceProvider;

class CPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/c-pay.php', 'c-pay');

        $this->app->singleton(CPay::class, function ($app) {
            return new CPay(config('c-pay', []));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/c-pay.php' => config_path('c-pay.php'),
            ], 'c-pay-config');
        }
    }
}
