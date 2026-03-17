<?php

declare(strict_types=1);

namespace CPay;

use CPay\Contracts\HttpClientInterface;
use CPay\Exceptions\PaymentException;
use CPay\Http\GuzzleHttpClient;
use CPay\Support\PaymentResponse;

class CPayClient
{
    private HttpClientInterface $http;

    public function __construct(
        private readonly CPayConfig $config,
        ?HttpClientInterface $http = null,
    ) {
        $this->http = $http ?? new GuzzleHttpClient(
            timeout:   $config->timeout,
            sslVerify: $config->sslVerify,
        );
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Initiate a card payment and return the raw API response.
     *
     * The $transactionData array must include:
     *   - extTransactionId  string   Your unique transaction reference
     *   - amount            string   Formatted amount, e.g. "150.00"
     *   - msisdn            string   Customer phone number
     *   - currency          string   Optional — defaults to config value
     *   - callbackUrl       string   Optional redirect / callback URL
     *
     * On success the API returns HTTP 202 with an HTML iframe body.
     *
     * @param  array<string, mixed>  $transactionData
     * @throws PaymentException
     */
    public function initiateCardPayment(array $transactionData): PaymentResponse
    {
        $endpoint = $this->url('/api/cpaypayments/payment?cardPayment=true&rememberMe=false');

        $checksum = $this->getChecksum($transactionData);

        $payload = [
            'transactionRequest' => [
                'extTransactionId' => $transactionData['extTransactionId'],
                'clientCode'       => $this->config->clientCode,
                'msisdn'           => $transactionData['msisdn'],
                'amount'           => $transactionData['amount'],
                'checksum'         => $checksum,
                'currency'         => $transactionData['currency'] ?? $this->config->currency,
                'redirectUrl'      => $transactionData['callbackUrl'] ?? '',
            ],
        ];

        if (isset($transactionData['otp'])) {
            $payload['transactionRequest']['otp']       = $transactionData['otp'];
            $payload['transactionRequest']['otpMedium'] = 'sms';
        }

        $raw = $this->http->post($endpoint, $payload, $this->authHeaders());

        return PaymentResponse::fromRaw($raw);
    }

    /**
     * Check the status of a transaction by its external transaction ID.
     *
     * @throws PaymentException
     */
    public function checkPaymentStatus(string $extTransactionId): PaymentResponse
    {
        $endpoint = $this->url('/api/cpaypayments/transaction-status');

        $raw = $this->http->get($endpoint, [
            'requestReference' => $extTransactionId,
            'dateTime'         => date('Y-m-d'),
        ], $this->authHeaders());

        return PaymentResponse::fromRaw($raw);
    }

    /**
     * Retrieve a server-side checksum for the given transaction data.
     * Falls back to local HMAC generation if the API call fails.
     *
     * @param  array<string, mixed>  $transactionData
     */
    public function getChecksum(array $transactionData): string
    {
        $endpoint = $this->url('/api/cpaypayments/getchecksum');

        $payload = [
            'transactionRequest' => [
                'extTransactionId' => $transactionData['extTransactionId'],
                'clientCode'       => $this->config->clientCode,
                'msisdn'           => $transactionData['msisdn'],
                'amount'           => $transactionData['amount'],
                'currency'         => $transactionData['currency'] ?? $this->config->currency,
                'redirectUrl'      => $transactionData['redirectUrl'] ?? '',
            ],
        ];

        $raw = $this->http->post($endpoint, $payload, $this->authHeaders());
        $response = PaymentResponse::fromRaw($raw);

        if ($response->success && $response->get('checksum')) {
            return (string) $response->get('checksum');
        }

        // Fallback: generate locally
        return $this->generateChecksum($transactionData);
    }

    /**
     * Generate a local HMAC-SHA256 checksum.
     *
     * @param  array<string, mixed>  $data
     */
    public function generateChecksum(array $data): string
    {
        $salt = $data['extTransactionId'] . $this->config->clientCode . $data['amount'] . $data['msisdn'];

        return hash_hmac('sha256', $salt, $this->config->clientSecret);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function url(string $path): string
    {
        return $this->config->baseUrl . $path;
    }

    /** @return array<string, string> */
    private function authHeaders(): array
    {
        return ['Authorization' => $this->config->apiKey];
    }
}
