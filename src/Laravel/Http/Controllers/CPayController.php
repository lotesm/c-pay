<?php

declare(strict_types=1);

namespace CPay\Laravel\Http\Controllers;

use CPay\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class CPayController extends Controller
{
    public function __construct(
        private readonly PaymentManager $payments,
    ) {}

    /**
     * Render the payment iframe page.
     * Replaces: cpay_card_payment_redirect + card-payment-process.php
     */
    public function showPayment(Request $request): Response|RedirectResponse
    {
        $orderId      = $request->query('order');
        $iframeHtml   = Session::get('cpay_card_iframe_content', '');
        $iframeSrc    = $this->payments->extractIframeSrc($iframeHtml);

        if (! $orderId || ! $iframeSrc) {
            return redirect()->route('checkout')->withErrors('Payment session expired. Please try again.');
        }

        return response()->view('c-pay::card-payment-process', [
            'orderId'       => $orderId,
            'iframeSrc'     => $iframeSrc,
            'checkoutUrl'   => route('checkout'),
            'cancelUrl'     => route('cpay.cancel'),
            'statusUrl'     => route('cpay.status'),
        ]);
    }

    /**
     * Render the payment confirmation page.
     * Replaces: cpay_card_confirmation_redirect + payment-confirmation.php
     */
    public function confirmation(Request $request): Response|RedirectResponse
    {
        $orderId = Session::get('cpay_card_order_id', $request->query('order'));

        if (! $orderId) {
            return redirect('/');
        }

        return response()->view('c-pay::payment-confirmation', [
            'orderId' => $orderId,
        ]);
    }

    /**
     * Handle the payment gateway callback (GET redirect from C-Pay).
     * Replaces: cpay_card_callback_handler
     */
    public function callback(Request $request): RedirectResponse
    {
        $orderId = $request->query('order_id');
        $status  = $request->query('status');

        // TODO: Look up your order model and update status here.
        // Example for Laravel + custom Order model:
        //
        // $order = Order::findOrFail($orderId);
        // if ($status === 'success') {
        //     $order->markAsPaid();
        //     Session::forget(['cpay_card_order_id', 'cpay_card_iframe_content']);
        //     return redirect()->route('orders.show', $orderId)->with('success', 'Payment completed!');
        // }
        // $order->markAsFailed();
        // return redirect()->route('checkout')->withErrors('Payment failed. Please try again.');

        // Fire an event so host apps can hook in
        event('cpay.callback', ['order_id' => $orderId, 'status' => $status]);

        return redirect('/');
    }

    /**
     * AJAX endpoint — return the current payment status for an order.
     * Replaces: cpay_card_check_status_handler
     */
    public function checkStatus(Request $request): JsonResponse {
        $orderId       = $request->input('order_id');
        $transactionId = $request->input('ext_transaction_id');

        if (! $orderId && ! $transactionId) {
            return response()->json(['success' => false, 'message' => 'Missing order_id or ext_transaction_id'], 422);
        }

        if ($transactionId) {
            $response = $this->payments->checkStatus($transactionId);

            return response()->json([
                'success' => $response->success,
                'data'    => $response->decoded,
            ]);
        }

        // TODO: Look up order status from your own database here.
        // Example:
        // $order = Order::findOrFail($orderId);
        // return response()->json(['success' => true, 'status' => $order->status]);

        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * AJAX endpoint — cancel a pending payment.
     * Replaces: cpay_card_cancel_payment_handler
     */
    public function cancel(Request $request): JsonResponse
    {
        $orderId = $request->input('order_id');

        Session::forget(['cpay_card_order_id', 'cpay_card_iframe_content', 'cpay_card_gateway_url']);

        // TODO: Update order status in your own database here.
        // Example:
        // $order = Order::find($orderId);
        // if ($order && in_array($order->status, ['pending', 'on-hold'])) {
        //     $order->cancel('Payment cancelled by user');
        // }

        event('cpay.cancelled', ['order_id' => $orderId]);

        return response()->json(['success' => true, 'message' => 'Payment cancelled']);
    }
}
