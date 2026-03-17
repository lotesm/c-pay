<?php

declare(strict_types=1);

namespace CPay\Tests\Feature;

use CPay\CPayClient;
use CPay\CPayConfig;
use CPay\PaymentManager;
use CPay\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_cpay_config_is_bound(): void
    {
        $config = $this->app->make(CPayConfig::class);

        $this->assertInstanceOf(CPayConfig::class, $config);
        $this->assertSame('TEST_CLIENT', $config->clientCode);
        $this->assertSame('LSL', $config->currency);
    }

    public function test_cpay_client_is_bound_as_singleton(): void
    {
        $a = $this->app->make(CPayClient::class);
        $b = $this->app->make(CPayClient::class);

        $this->assertSame($a, $b);
    }

    public function test_payment_manager_is_bound(): void
    {
        $manager = $this->app->make(PaymentManager::class);

        $this->assertInstanceOf(PaymentManager::class, $manager);
    }

    public function test_cpay_alias_resolves(): void
    {
        $manager = $this->app->make('cpay');

        $this->assertInstanceOf(PaymentManager::class, $manager);
    }

    public function test_config_is_published_to_correct_key(): void
    {
        $this->assertSame('https://cpay-test.example.com', config('c-pay.base_url'));
        $this->assertSame('test-api-key', config('c-pay.api_key'));
    }
}
