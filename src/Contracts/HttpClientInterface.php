<?php

declare(strict_types=1);

namespace CPay\Contracts;

interface HttpClientInterface
{
    /**
     * Send a POST request and return the raw response body.
     *
     * @param  string  $url
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     * @return array{success: bool, status_code: int, data: string}
     */
    public function post(string $url, array $payload, array $headers = []): array;

    /**
     * Send a GET request and return the raw response body.
     *
     * @param  string  $url
     * @param  array<string, string>  $query
     * @param  array<string, string>  $headers
     * @return array{success: bool, status_code: int, data: string}
     */
    public function get(string $url, array $query = [], array $headers = []): array;
}
