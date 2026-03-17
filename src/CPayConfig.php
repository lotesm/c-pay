<?php

declare(strict_types=1);

namespace CPay;

class CPayConfig
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $clientCode,
        public readonly string $apiKey,
        public readonly string $clientSecret,
        public readonly string $merchantCode = '',
        public readonly string $currency = 'LSL',
        public readonly bool   $testMode = false,
        public readonly int    $timeout = 30,
        public readonly bool   $sslVerify = true,
    ) {}

    /**
     * Create from a plain associative array (e.g. config('c-pay')).
     */
    public static function fromArray(array $config): self
    {
        return new self(
            baseUrl:      rtrim($config['base_url'] ?? $config['api_url'] ?? '', '/'),
            clientCode:   $config['client_code'] ?? '',
            apiKey:       $config['api_key'] ?? '',
            clientSecret: $config['client_secret'] ?? '',
            merchantCode: $config['merchant_code'] ?? '',
            currency:     $config['currency'] ?? 'LSL',
            testMode:     (bool) ($config['test_mode'] ?? false),
            timeout:      (int) ($config['timeout'] ?? 30),
            sslVerify:    (bool) ($config['ssl_verify'] ?? true),
        );
    }
}
