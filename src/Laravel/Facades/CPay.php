<?php

declare(strict_types=1);

namespace CPay\Laravel\Facades;

use CPay\PaymentManager;
use CPay\Support\PaymentResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static PaymentResponse initiatePayment(array $orderData)
 * @method static PaymentResponse checkStatus(string $extTransactionId)
 * @method static string buildTransactionId(string|int $orderId)
 * @method static string extractIframeSrc(string $htmlBody)
 *
 * @see \CPay\PaymentManager
 */
class CPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PaymentManager::class;
    }
}
