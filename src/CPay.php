<?php

namespace CPay;

class CPay
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get a config value.
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    // TODO: Add your payment logic here
}
