<?php

declare(strict_types=1);

namespace CPay\Support;

class PaymentResponse
{
    public function __construct(
        public readonly bool   $success,
        public readonly int    $statusCode,
        public readonly string $rawData,
        public readonly ?array $decoded = null,
        public readonly ?string $error = null,
    ) {}

    public static function fromRaw(array $raw): self
    {
        $decoded = null;
        if (!empty($raw['data'])) {
            $decoded = json_decode($raw['data'], true);
        }

        return new self(
            success:    $raw['success'] ?? false,
            statusCode: $raw['status_code'] ?? 0,
            rawData:    $raw['data'] ?? '',
            decoded:    is_array($decoded) ? $decoded : null,
            error:      $raw['error'] ?? null,
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->decoded[$key] ?? $default;
    }
}
