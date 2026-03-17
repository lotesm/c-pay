<?php

declare(strict_types=1);

namespace CPay\Laravel;

use CPay\CPayClient;
use CPay\CPayConfig;
use CPay\Http\GuzzleHttpClient;
use CPay\PaymentManager;
use Illuminate\Support\ServiceProvider;

class CPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/c-pay.php', 'c-pay');

        $this->app->singleton(CPayConfig::class, function ($app) {
            return CPayConfig::fromArray(config('c-pay', []));
        });

        $this->app->singleton(CPayClient::class, function ($app) {
            /** @var CPayConfig $config */
            $config = $app->make(CPayConfig::class);

            return new CPayClient(
                config: $config,
                http: new GuzzleHttpClient(
                    timeout:   $config->timeout,
                    sslVerify: $config->sslVerify,
                ),
            );
        });

        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager($app->make(CPayClient::class));
        });

        // Alias so `app('cpay')` also works
        $this->app->alias(PaymentManager::class, 'cpay');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../../config/c-pay.php' => config_path('c-pay.php'),
            ], 'c-pay-config');

            // Publish views/templates
            $this->publishes([
                __DIR__ . '/Views/templates' => resource_path('views/vendor/c-pay'),
            ], 'c-pay-views');

            // Publish assets
            $this->publishes([
                __DIR__ . '/../../resources/assets' => public_path('vendor/c-pay/assets'),
            ], 'c-pay-assets');
        }

        // Load package routes if the file exists
        $routesFile = __DIR__ . '/routes.php';
        if (file_exists($routesFile)) {
            $this->loadRoutesFrom($routesFile);
        }

        // Load package views
        $this->loadViewsFrom(__DIR__ . '/Views/templates', 'c-pay');
    }
}
