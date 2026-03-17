<?php

declare(strict_types=1);

namespace CPay\Tests\Unit;

use CPay\CPayClient;
use CPay\CPayConfig;
use CPay\Contracts\HttpClientInterface;
use CPay\Exceptions\PaymentException;
use PHPUnit\Framework\TestCase;

class CPayClientTest extends TestCase
{
    private CPayConfig $config;

    protected function setUp(): void
    {
        $this->config = CPayConfig::fromArray([
            'base_url'      => 'https://cpay.test',
            'client_code'   => 'TEST01',
            'api_key'       => 'api-key-123',
            'client_secret' => 'secret-xyz',
        ]);
    }

    public function test_generate_checksum_returns_hmac_sha256(): void
    {
        $client = new CPayClient($this->config, $this->mockHttp());

        $checksum = $client->generateChecksum([
            'extTransactionId' => 'TX_1_1700000000',
            'amount'           => '100.00',
            'msisdn'           => '26657000000',
        ]);

        $this->assertNotEmpty($checksum);
        $this->assertSame(64, strlen($checksum)); // SHA-256 hex = 64 chars
    }

    public function test_initiate_card_payment_calls_correct_endpoint(): void
    {
        $capturedUrl     = null;
        $capturedPayload = null;

        $http = $this->createMock(HttpClientInterface::class);
        $http->method('post')
            ->willReturnCallback(function (string $url, array $payload) use (&$capturedUrl, &$capturedPayload) {
                $capturedUrl     = $url;
                $capturedPayload = $payload;
                // First call is getchecksum, second is the payment endpoint
                if (str_contains($url, 'getchecksum')) {
                    return ['success' => true, 'status_code' => 200, 'data' => json_encode(['checksum' => 'abc123'])];
                }
                return ['success' => true, 'status_code' => 202, 'data' => '<iframe src="https://pay.cpay.test/frame"></iframe>'];
            });

        $client   = new CPayClient($this->config, $http);
        $response = $client->initiateCardPayment([
            'extTransactionId' => 'TX_42_1700000000',
            'amount'           => '250.00',
            'msisdn'           => '26657000001',
            'currency'         => 'LSL',
            'callbackUrl'      => 'https://myshop.test/cpay/callback',
        ]);

        $this->assertTrue($response->success);
        $this->assertSame(202, $response->statusCode);
        $this->assertStringContainsString('/api/cpaypayments/payment', $capturedUrl);
    }

    public function test_check_payment_status_calls_correct_endpoint(): void
    {
        $capturedQuery = null;

        $http = $this->createMock(HttpClientInterface::class);
        $http->method('get')
            ->willReturnCallback(function (string $url, array $query) use (&$capturedQuery) {
                $capturedQuery = $query;
                return ['success' => true, 'status_code' => 200, 'data' => json_encode(['status' => 'completed'])];
            });

        $client   = new CPayClient($this->config, $http);
        $response = $client->checkPaymentStatus('TX_42_1700000000');

        $this->assertTrue($response->success);
        $this->assertSame('TX_42_1700000000', $capturedQuery['requestReference']);
    }

    public function test_get_checksum_falls_back_to_local_on_api_failure(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->method('post')->willReturn([
            'success' => false, 'status_code' => 500, 'data' => '',
        ]);

        $client   = new CPayClient($this->config, $http);
        $checksum = $client->getChecksum([
            'extTransactionId' => 'TX_1',
            'amount'           => '50.00',
            'msisdn'           => '26657000000',
        ]);

        // Should fall back to local HMAC — must still return a 64-char hex string
        $this->assertSame(64, strlen($checksum));
    }

    /** @return HttpClientInterface */
    private function mockHttp(array $response = []): HttpClientInterface
    {
        $default = ['success' => true, 'status_code' => 200, 'data' => json_encode(['checksum' => 'mock'])];
        $mock    = $this->createMock(HttpClientInterface::class);
        $mock->method('post')->willReturn(array_merge($default, $response));
        $mock->method('get')->willReturn(array_merge($default, $response));
        return $mock;
    }
}
