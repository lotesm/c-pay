<?php

use CPay\Laravel\Http\Controllers\CPayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| C-Pay Package Routes
|--------------------------------------------------------------------------
|
| These routes handle payment flow redirects and callbacks.
| They are loaded automatically by the service provider.
|
| Prefix  : /cpay
| Middleware: web
|
*/

Route::prefix('cpay')
    ->middleware('web')
    ->name('cpay.')
    ->group(function () {
        // Show the payment iframe page
        Route::get('/payment', [CPayController::class, 'showPayment'])
            ->name('payment');

        // Payment confirmation page
        Route::get('/confirmation', [CPayController::class, 'confirmation'])
            ->name('confirmation');

        // Callback from the payment gateway
        Route::get('/callback', [CPayController::class, 'callback'])
            ->name('callback');

        // AJAX: check order status
        Route::post('/status', [CPayController::class, 'checkStatus'])
            ->name('status');

        // AJAX: cancel a pending payment
        Route::post('/cancel', [CPayController::class, 'cancel'])
            ->name('cancel');
    });
