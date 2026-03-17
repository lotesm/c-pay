<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Secure Card Payment - C-Pay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body, html {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }

        .loading-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: white; display: flex; flex-direction: column;
            justify-content: center; align-items: center; z-index: 9999;
            transition: opacity 0.3s ease;
        }
        .loading-content { text-align: center; max-width: 500px; padding: 40px; }
        .spinner {
            width: 60px; height: 60px;
            border: 5px solid #f3f3f3; border-top: 5px solid #2ecc71;
            border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px;
        }
        @keyframes spin { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }
        .loading-content h2 { color: #2c3e50; margin-bottom: 15px; font-size: 24px; }
        .loading-content p  { color: #7f8c8d; font-size: 16px; line-height: 1.6; }
        .security-badge {
            display: inline-flex; align-items: center; gap: 8px;
            margin-top: 20px; padding: 10px 20px;
            background: #e8f5e9; border-radius: 20px; color: #2e7d32; font-weight: 600;
        }

        .payment-container { width: 100%; height: 100vh; }
        .payment-frame     { width: 100%; height: 100%; border: none; background: white; }

        .status-message {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            padding: 15px 25px; border-radius: 8px; z-index: 10000;
            max-width: 90%; text-align: center; display: none;
        }
        .status-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .status-error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }

        .exit-button {
            position: fixed; bottom: 20px; left: 20px; z-index: 1000;
            background: rgba(231,76,60,0.9); color: white; border: none;
            padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease;
            display: flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .exit-button:hover { background: #e74c3c; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .exit-button { bottom: 10px; left: 10px; padding: 10px 18px; font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h2>Loading Secure Payment Gateway</h2>
            <p>Please wait while we connect to the payment processor. This may take a few moments.</p>
            <div class="security-badge">
                <i class="fas fa-lock"></i>
                <span>Your payment is secured with 256-bit SSL encryption</span>
            </div>
        </div>
    </div>

    <div id="status-message" class="status-message"></div>

    <button type="button" id="exit-btn" class="exit-button">
        <i class="fas fa-times"></i> Exit
    </button>

    <div class="payment-container">
        <iframe
            src="{{ $iframeSrc }}"
            class="payment-frame"
            id="payment-frame"
            allow="payment"
            onload="hideLoading()"
        ></iframe>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    jQuery(document).ready(function($) {
        var orderId     = {{ $orderId }};
        var cancelUrl   = '{{ $cancelUrl }}';
        var statusUrl   = '{{ $statusUrl }}';
        var checkoutUrl = '{{ $checkoutUrl }}';
        var isProcessing = false;
        var isCompleted  = false;
        var pollTimer;

        window.hideLoading = function() {
            $('#loading-overlay').fadeOut(300);
        };

        $('#exit-btn').on('click', function() {
            if (confirm('Are you sure you want to exit? Your payment will be cancelled.')) {
                cancelPayment();
            }
        });

        function cancelPayment() {
            if (isProcessing) return;
            isProcessing = true;
            showStatusMessage('Cancelling payment...', 'error');
            $.post(cancelUrl, { order_id: orderId, _token: '{{ csrf_token() }}' })
                .always(function() { window.location.href = checkoutUrl; });
        }

        function checkPaymentStatus() {
            $.post(statusUrl, { order_id: orderId, _token: '{{ csrf_token() }}' }, function(response) {
                if (!response.success) return;
                var status = (response.data && response.data.status) ? response.data.status : response.status;
                if (status === 'completed' || status === 'processing') {
                    isCompleted = true;
                    clearInterval(pollTimer);
                    showStatusMessage('Payment successful! Redirecting...', 'success');
                    setTimeout(function() {
                        window.location.href = (response.data && response.data.redirect) ? response.data.redirect : '/';
                    }, 1500);
                } else if (status === 'failed') {
                    clearInterval(pollTimer);
                    showStatusMessage('Payment failed. Redirecting to checkout...', 'error');
                    setTimeout(function() { window.location.href = checkoutUrl; }, 3000);
                }
            });
        }

        function showStatusMessage(msg, type) {
            $('#status-message')
                .removeClass('status-success status-error')
                .addClass('status-' + type)
                .text(msg)
                .fadeIn();
        }

        // Poll order status every 5 s
        pollTimer = setInterval(function() {
            if (!isCompleted) checkPaymentStatus();
        }, 5000);

        // Safety fallback: hide loader after 30 s
        setTimeout(function() { $('#loading-overlay').fadeOut(300); }, 30000);

        $(window).on('beforeunload', function() {
            if (!isProcessing && !isCompleted) {
                $.ajax({
                    url: cancelUrl, type: 'POST', async: false,
                    data: { order_id: orderId, _token: '{{ csrf_token() }}' }
                });
            }
        });
    });
    </script>
</body>
</html>
