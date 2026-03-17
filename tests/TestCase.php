<?php

declare(strict_types=1);

namespace CPay\Tests;

use CPay\Laravel\CPayServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [CPayServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('c-pay.base_url',      'https://cpay-test.example.com');
        $app['config']->set('c-pay.client_code',   'TEST_CLIENT');
        $app['config']->set('c-pay.api_key',       'test-api-key');
        $app['config']->set('c-pay.client_secret', 'test-secret');
        $app['config']->set('c-pay.currency',      'LSL');
        $app['config']->set('c-pay.test_mode',     true);
        $app['config']->set('c-pay.ssl_verify',    false);
    }
}
