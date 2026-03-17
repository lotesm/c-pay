<?php

namespace CPay\Tests;

use CPay\CPay;

class CPayTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $cpay = new CPay(['key' => 'abc123']);

        $this->assertInstanceOf(CPay::class, $cpay);
    }

    public function test_it_reads_config_values(): void
    {
        $cpay = new CPay(['key' => 'abc123', 'mode' => 'live']);

        $this->assertSame('abc123', $cpay->getConfig('key'));
        $this->assertSame('live', $cpay->getConfig('mode'));
    }

    public function test_it_returns_default_when_config_missing(): void
    {
        $cpay = new CPay([]);

        $this->assertNull($cpay->getConfig('nonexistent'));
        $this->assertSame('default', $cpay->getConfig('nonexistent', 'default'));
    }

    public function test_service_provider_registers_binding(): void
    {
        $cpay = $this->app->make(CPay::class);

        $this->assertInstanceOf(CPay::class, $cpay);
        $this->assertSame('test-key', $cpay->getConfig('key'));
    }
}
