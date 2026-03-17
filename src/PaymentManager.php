<?php

declare(strict_types=1);

namespace CPay;

use CPay\Exceptions\PaymentException;
use CPay\Support\PaymentResponse;

class PaymentManager
{
    public function __construct(
        private readonly CPayClient $client,
    ) {}

    // -------------------------------------------------------------------------
    // Public helpers
    // -------------------------------------------------------------------------

    /**
     * Build a unique external transaction ID for an order.
     *
     * @param  string|int  $orderId
     */
    public function buildTransactionId(string|int $orderId): string
    {
        return 'TX_' . $orderId . '_' . time();
    }

    /**
     * Initiate a card payment for the given order data.
     *
     * @param  array{
     *     order_id:        string|int,
     *     amount:          string,
     *     msisdn:          string,
     *     currency?:       string,
     *     callbackUrl?:    string,
     *     redirectUrl?:    string,
     * } $orderData
     *
     * @throws PaymentException
     */
    public function initiatePayment(array $orderData): PaymentResponse
    {
        $transactionId = $this->buildTransactionId($orderData['order_id']);

        $transactionData = [
            'extTransactionId' => $transactionId,
            'amount'           => $orderData['amount'],
            'msisdn'           => $orderData['msisdn'] ?: '0000000',
            'currency'         => $orderData['currency'] ?? 'LSL',
            'callbackUrl'      => $orderData['callbackUrl'] ?? '',
            'redirectUrl'      => $orderData['redirectUrl'] ?? '',
        ];

        $response = $this->client->initiateCardPayment($transactionData);

        if (! $response->success) {
            $message = $this->extractErrorMessage($response);
            throw new PaymentException("Payment initiation failed: {$message}");
        }

        return $response;
    }

    /**
     * Check the status of a transaction.
     *
     * @throws PaymentException
     */
    public function checkStatus(string $extTransactionId): PaymentResponse
    {
        return $this->client->checkPaymentStatus($extTransactionId);
    }

    /**
     * Extract a human-readable error message from a failed response.
     */
    public function extractErrorMessage(PaymentResponse $response): string
    {
        if ($response->error) {
            return $response->error;
        }

        if ($response->decoded && isset($response->decoded['message'])) {
            return $response->decoded['message'];
        }

        if ($response->rawData && ! str_contains($response->rawData, '<html')) {
            return $response->rawData;
        }

        return 'An unexpected error occurred. Please try again.';
    }

    /**
     * Extract the iframe src URL from the API response HTML body.
     */
    public function extractIframeSrc(string $htmlBody): string
    {
        preg_match('/src="([^"]+)"/', $htmlBody, $matches);

        return $matches[1] ?? '';
    }

    public function getClient(): CPayClient
    {
        return $this->client;
    }
}
