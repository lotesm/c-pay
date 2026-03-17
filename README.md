# C-Pay PHP SDK

[![Tests](https://github.com/lotesm/c-pay/actions/workflows/tests.yml/badge.svg)](https://github.com/lotesm/c-pay/actions)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A framework-agnostic PHP SDK for the **C-Pay card payment gateway** (Visa / MasterCard).  
Includes first-class Laravel integration via a service provider, facade, controller, and Blade templates. yup

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | 8.1+ |
| Guzzle | ^7.0 |
| Laravel *(optional)* | 10 or 11 |

---

## Installation

```bash
composer require c-pay/c-pay
```

Laravel's auto-discovery registers the service provider and facade automatically.

### Publish config

```bash
php artisan vendor:publish --tag=c-pay-config
```

### Publish views

```bash
php artisan vendor:publish --tag=c-pay-views
```

### Publish assets

```bash
php artisan vendor:publish --tag=c-pay-assets
```

---

## Configuration

Add to your `.env`:

```env
CPAY_BASE_URL=https://cpay-uat-env.chaperone.co.ls:5100
CPAY_CLIENT_CODE=YOUR_CLIENT_CODE
CPAY_API_KEY=your-api-key
CPAY_CLIENT_SECRET=your-client-secret
CPAY_MERCHANT_CODE=your-merchant-code
CPAY_CURRENCY=LSL
CPAY_TEST_MODE=true
CPAY_SSL_VERIFY=true
```

---

## Usage

### Laravel — Facade

```php
use CPay\Laravel\Facades\CPay;

// Initiate a card payment
$response = CPay::initiatePayment([
    'order_id'    => $order->id,
    'amount'      => number_format($order->total, 2, '.', ''),
    'msisdn'      => $order->billing_phone,
    'currency'    => 'LSL',
    'callbackUrl' => route('cpay.callback'),
]);

// Store the iframe HTML in session then redirect
session(['cpay_card_iframe_content' => $response->rawData]);

return redirect()->route('cpay.payment', ['order' => $order->id]);
```

### Laravel — Dependency Injection

```php
use CPay\PaymentManager;

class CheckoutController extends Controller
{
    public function __construct(private PaymentManager $cpay) {}

    public function pay(Order $order)
    {
        $response = $this->cpay->initiatePayment([
            'order_id' => $order->id,
            'amount'   => $order->formattedTotal(),
            'msisdn'   => $order->phone,
        ]);

        session(['cpay_card_iframe_content' => $response->rawData]);

        return redirect()->route('cpay.payment', ['order' => $order->id]);
    }
}
```

### Plain PHP (no framework)

```php
use CPay\CPayClient;
use CPay\CPayConfig;
use CPay\PaymentManager;

$config  = CPayConfig::fromArray([
    'base_url'      => 'https://cpay-uat-env.chaperone.co.ls:5100',
    'client_code'   => 'MY_SHOP',
    'api_key'       => 'my-api-key',
    'client_secret' => 'my-secret',
]);

$manager  = new PaymentManager(new CPayClient($config));
$response = $manager->initiatePayment([
    'order_id' => 42,
    'amount'   => '199.00',
    'msisdn'   => '26657000000',
]);

if ($response->success && $response->statusCode === 202) {
    $iframeSrc = $manager->extractIframeSrc($response->rawData);
    // Render your own payment page with $iframeSrc
}
```

---

## Routes (Laravel)

The package registers the following routes automatically under the `/cpay` prefix:

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/cpay/payment` | `cpay.payment` | Renders the iframe payment page |
| GET | `/cpay/confirmation` | `cpay.confirmation` | Renders the payment confirmation page |
| GET | `/cpay/callback` | `cpay.callback` | Gateway callback (order status update) |
| POST | `/cpay/status` | `cpay.status` | AJAX: poll order status |
| POST | `/cpay/cancel` | `cpay.cancel` | AJAX: cancel a pending payment |

> **Important:** The callback and status routes contain `// TODO` comments.  
> You **must** implement order lookup logic there to match your application's order model.

---

## Customising Views

After publishing views (`--tag=c-pay-views`), templates land in `resources/views/vendor/c-pay/`:

| Template | Purpose |
|---|---|
| `card-payment-process.blade.php` | Full-screen iframe payment page |
| `payment-confirmation.blade.php` | Post-payment success page |
| `card-payment-iframe.blade.php`  | OTP verification page |

Pass extra data to the confirmation template from your controller override:

```php
return view('c-pay::payment-confirmation', [
    'orderId'         => $order->id,
    'transactionDate' => $order->paid_at->format('F j, Y g:i A'),
    'transactionId'   => $order->ext_transaction_id,
    'orderItems'      => $order->items->map(fn($i) => [
                           'quantity' => $i->qty,
                           'name'     => $i->product_name,
                           'price'    => 'LSL ' . number_format($i->total, 2),
                         ])->all(),
    'orderTotal'      => 'LSL ' . number_format($order->total, 2),
    'homeUrl'         => url('/'),
    'orderViewUrl'    => route('orders.show', $order),
    'shopUrl'         => route('shop.index'),
]);
```

---

## Custom HTTP Client

Swap out Guzzle with any HTTP client by implementing `HttpClientInterface`:

```php
use CPay\Contracts\HttpClientInterface;

class MyHttpClient implements HttpClientInterface
{
    public function post(string $url, array $payload, array $headers = []): array { ... }
    public function get(string $url, array $query = [], array $headers = []): array { ... }
}

$client  = new CPayClient($config, new MyHttpClient());
$manager = new PaymentManager($client);
```

---

## Testing

```bash
composer install
vendor/bin/phpunit
```

---

## Migrating from the WordPress Plugin

| WordPress | This Package |
|---|---|
| `Cpay_Card_API` | `CPay\CPayClient` |
| `WC_Cpay_Card_Gateway::process_payment()` | `PaymentManager::initiatePayment()` |
| `wp_remote_request()` | `GuzzleHttpClient` (swappable) |
| `add_action('template_redirect', ...)` | `CPayController` (Laravel routes) |
| `wp_ajax_*` handlers | `CPayController` POST routes |
| WooCommerce session | Laravel `Session` |
| PHP templates | Blade templates |
| `CPAY_CARD_PLUGIN_URL` constant | `asset('vendor/c-pay/assets/...')` |

---

## License

MIT © Moeketsi Titisi — [chaperone.co.ls](https://chaperone.co.ls)
