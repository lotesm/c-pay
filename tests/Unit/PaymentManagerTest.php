<?php

declare(strict_types=1);

namespace CPay\Tests\Unit;

use CPay\CPayClient;
use CPay\CPayConfig;
use CPay\Contracts\HttpClientInterface;
use CPay\Exceptions\PaymentException;
use CPay\PaymentManager;
use CPay\Support\PaymentResponse;
use PHPUnit\Framework\TestCase;

class PaymentManagerTest extends TestCase
{
    private function makeManager(array $httpResponse): PaymentManager
    {
        $config = CPayConfig::fromArray([
            'base_url'      => 'https://cpay.test',
            'client_code'   => 'TEST01',
            'api_key'       => 'key',
            'client_secret' => 'secret',
        ]);

        $http = $this->createMock(HttpClientInterface::class);
        $http->method('post')->willReturn($httpResponse);
        $http->method('get')->willReturn($httpResponse);

        return new PaymentManager(new CPayClient($config, $http));
    }

    public function test_build_transaction_id_includes_order_id(): void
    {
        $manager = $this->makeManager(['success' => true, 'status_code' => 200, 'data' => '']);
        $id      = $manager->buildTransactionId(99);

        $this->assertStringStartsWith('TX_99_', $id);
    }

    public function test_initiate_payment_throws_on_failure(): void
    {
        $this->expectException(PaymentException::class);

        $manager = $this->makeManager([
            'success'     => false,
            'status_code' => 400,
            'data'        => json_encode(['message' => 'Invalid credentials']),
        ]);

        $manager->initiatePayment([
            'order_id' => 1,
            'amount'   => '100.00',
            'msisdn'   => '26657000000',
        ]);
    }

    public function test_initiate_payment_returns_response_on_success(): void
    {
        $manager = $this->makeManager([
            'success'     => true,
            'status_code' => 202,
            'data'        => '<iframe src="https://pay.cpay.test/form"></iframe>',
        ]);

        $response = $manager->initiatePayment([
            'order_id' => 5,
            'amount'   => '300.00',
            'msisdn'   => '26657000000',
        ]);

        $this->assertInstanceOf(PaymentResponse::class, $response);
        $this->assertTrue($response->success);
    }

    public function test_extract_iframe_src_parses_correctly(): void
    {
        $manager = $this->makeManager(['success' => true, 'status_code' => 200, 'data' => '']);
        $src     = $manager->extractIframeSrc('<iframe src="https://pay.cpay.test/frame?token=abc"></iframe>');

        $this->assertSame('https://pay.cpay.test/frame?token=abc', $src);
    }

    public function test_extract_iframe_src_returns_empty_on_no_match(): void
    {
        $manager = $this->makeManager(['success' => true, 'status_code' => 200, 'data' => '']);

        $this->assertSame('', $manager->extractIframeSrc('<p>No iframe here</p>'));
    }
}
