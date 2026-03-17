<?php

declare(strict_types=1);

namespace CPay\Tests\Unit;

use CPay\CPayConfig;
use PHPUnit\Framework\TestCase;

class CPayConfigTest extends TestCase
{
    public function test_it_creates_from_array(): void
    {
        $config = CPayConfig::fromArray([
            'base_url'      => 'https://cpay.example.com',
            'client_code'   => 'MYSHOP',
            'api_key'       => 'key123',
            'client_secret' => 'secret456',
            'currency'      => 'LSL',
            'test_mode'     => true,
        ]);

        $this->assertSame('https://cpay.example.com', $config->baseUrl);
        $this->assertSame('MYSHOP', $config->clientCode);
        $this->assertSame('key123', $config->apiKey);
        $this->assertSame('LSL', $config->currency);
        $this->assertTrue($config->testMode);
    }

    public function test_it_trims_trailing_slash_from_base_url(): void
    {
        $config = CPayConfig::fromArray(['base_url' => 'https://cpay.example.com/']);

        $this->assertSame('https://cpay.example.com', $config->baseUrl);
    }

    public function test_it_uses_defaults_for_missing_keys(): void
    {
        $config = CPayConfig::fromArray([]);

        $this->assertSame('LSL', $config->currency);
        $this->assertSame(30, $config->timeout);
        $this->assertFalse($config->testMode);
    }

    public function test_it_accepts_legacy_api_url_key(): void
    {
        $config = CPayConfig::fromArray(['api_url' => 'https://legacy.example.com']);

        $this->assertSame('https://legacy.example.com', $config->baseUrl);
    }
}
