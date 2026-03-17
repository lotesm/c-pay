<?php

declare(strict_types=1);

namespace CPay\Http;

use CPay\Contracts\HttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleHttpClient implements HttpClientInterface
{
    private Client $client;

    public function __construct(
        private readonly int  $timeout = 30,
        private readonly bool $sslVerify = true,
    ) {
        $this->client = new Client([
            'timeout'  => $this->timeout,
            'verify'   => $this->sslVerify,
        ]);
    }

    /** @inheritDoc */
    public function post(string $url, array $payload, array $headers = []): array
    {
        try {
            $response = $this->client->post($url, [
                'headers' => array_merge($this->defaultHeaders(), $headers),
                'body'    => json_encode($payload),
            ]);

            return $this->format($response->getStatusCode(), (string) $response->getBody());
        } catch (GuzzleException $e) {
            return ['success' => false, 'status_code' => 0, 'data' => $e->getMessage()];
        }
    }

    /** @inheritDoc */
    public function get(string $url, array $query = [], array $headers = []): array
    {
        try {
            $response = $this->client->get($url, [
                'headers' => array_merge($this->defaultHeaders(), $headers),
                'query'   => $query,
            ]);

            return $this->format($response->getStatusCode(), (string) $response->getBody());
        } catch (GuzzleException $e) {
            return ['success' => false, 'status_code' => 0, 'data' => $e->getMessage()];
        }
    }

    /** @return array<string, string> */
    private function defaultHeaders(): array
    {
        return [
            'Accept'       => 'text/plain',
            'Content-Type' => 'application/json-patch+json',
        ];
    }

    /** @return array{success: bool, status_code: int, data: string} */
    private function format(int $statusCode, string $body): array
    {
        return [
            'success'     => $statusCode >= 200 && $statusCode < 300,
            'status_code' => $statusCode,
            'data'        => $body,
        ];
    }
}
