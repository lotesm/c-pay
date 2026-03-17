<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - C-Pay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        :root{
            --primary:#2ecc71; --primary-dark:#27ae60; --primary-light:#d5f4e6;
            --success:#2ecc71; --danger:#e74c3c; --light:#ffffff; --dark:#2c3e50;
            --gray:#7f8c8d; --border:#d5dbdb;
            --shadow:0 20px 60px rgba(46,204,113,.15); --radius:16px; --transition:all .3s ease;
        }
        body{
            font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
            background:linear-gradient(135deg,#1abc9c 0%,#16a085 100%);
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            padding:20px; color:var(--dark);
        }
        .cpay-container{ width:100%; max-width:520px; margin:0 auto; }
        .logo-container{ text-align:center; margin-bottom:40px; }
        .logo{ height:72px; margin-bottom:15px; }
        .logo-container p{ color:rgba(255,255,255,.95); font-size:18px; font-weight:500; }
        .card{ background:var(--light); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; animation:slideUp .5s ease-out; }
        @keyframes slideUp{ from{opacity:0;transform:translateY(40px)} to{opacity:1;transform:translateY(0)} }
        .card-header{ background:linear-gradient(135deg,var(--primary),var(--primary-dark)); color:white; padding:40px 30px; text-align:center; }
        .header-icon{ background:rgba(255,255,255,.2); width:90px; height:90px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 25px; font-size:40px; border:2px solid rgba(255,255,255,.3); }
        .card-header h2{ font-size:32px; font-weight:700; margin-bottom:10px; }
        .card-header p{ opacity:.95; font-size:17px; }
        .card-body{ padding:40px; }
        .success-message{ background:var(--primary-light); border-radius:14px; padding:30px; margin-bottom:30px; text-align:center; border:2px dashed rgba(46,204,113,.4); }
        .success-icon{ background:var(--success); color:white; width:75px; height:75px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:36px; animation:successPulse 2s infinite; }
        @keyframes successPulse{ 0%,100%{transform:scale(1);box-shadow:0 0 0 0 rgba(46,204,113,.7)} 70%{transform:scale(1.05);box-shadow:0 0 0 10px rgba(46,204,113,0)} }
        .success-title{ color:var(--primary-dark); font-size:22px; font-weight:800; margin-bottom:12px; }
        .success-text{ color:var(--gray); font-size:15px; line-height:1.6; }
        .payment-details,.order-section{ background:#f8fcf9; border-radius:14px; padding:28px; margin:25px 0; border:2px solid var(--primary-light); }
        .details-header,.order-header{ display:flex; align-items:center; gap:12px; margin-bottom:22px; }
        .details-header i,.order-header i{ color:var(--primary); font-size:24px; }
        .details-header h3,.order-header h3{ font-size:20px; font-weight:700; color:var(--dark); margin:0; }
        .detail-item,.order-item{ display:flex; justify-content:space-between; padding:14px 0; border-bottom:1px solid rgba(0,0,0,.06); }
        .detail-item:last-child,.order-item:last-child{ border-bottom:none; }
        .detail-label,.item-name{ color:var(--dark); font-size:16px; }
        .detail-value,.item-price{ color:var(--primary); font-weight:700; font-size:16px; }
        .order-total{ display:flex; justify-content:space-between; padding:22px 0 0; margin-top:22px; border-top:3px solid var(--border); font-weight:800; font-size:20px; }
        .total-amount{ color:var(--success); font-size:26px; }
        .action-buttons{ display:flex; gap:16px; margin-top:36px; }
        .btn{ flex:1; padding:22px; border:none; border-radius:14px; font-size:18px; font-weight:700; cursor:pointer; transition:var(--transition); display:flex; align-items:center; justify-content:center; gap:12px; min-height:72px; }
        .btn-primary{ background:linear-gradient(135deg,var(--primary),var(--primary-dark)); color:white; box-shadow:0 6px 20px rgba(46,204,113,.3); }
        .btn-primary:hover{ transform:translateY(-3px); box-shadow:0 12px 30px rgba(46,204,113,.4); }
        .btn-secondary{ background:white; color:var(--primary); border:3px solid var(--primary); }
        .btn-secondary:hover{ background:var(--primary-light); transform:translateY(-2px); }
        .redirect-timer{ text-align:center; margin-top:24px; color:var(--gray); font-size:15px; }
        #redirect-countdown{ font-weight:700; color:var(--primary); }
        @media(max-width:600px){ .card-body{padding:28px} .action-buttons{flex-direction:column} .btn{min-height:64px;font-size:16px} }
    </style>
</head>
<body>
<div class="cpay-container">
    <div class="logo-container">
        <img src="{{ asset('vendor/c-pay/assets/images/Visa.png') }}" alt="C-Pay" class="logo" onerror="this.style.display='none'">
        <p>Secure Payment Gateway</p>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="header-icon"><i class="fas fa-check-circle"></i></div>
            <h2>Payment Successful!</h2>
            <p>Thank you for your purchase</p>
        </div>

        <div class="card-body">
            <div class="success-message">
                <div class="success-icon"><i class="fas fa-check"></i></div>
                <h3 class="success-title">Payment Completed</h3>
                <p class="success-text">Your payment has been processed successfully. You will receive a confirmation shortly.</p>
            </div>

            <div class="payment-details">
                <div class="details-header">
                    <i class="fas fa-receipt"></i>
                    <h3>Transaction Details</h3>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Order Number</span>
                    <span class="detail-value">#{{ $orderId }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value">Visa / MasterCard</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Transaction Status</span>
                    <span class="detail-value" style="color:var(--success)">Completed</span>
                </div>
                {{-- Host app can pass $transactionDate, $transactionId via the controller --}}
                @isset($transactionDate)
                <div class="detail-item">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">{{ $transactionDate }}</span>
                </div>
                @endisset
                @isset($transactionId)
                <div class="detail-item">
                    <span class="detail-label">Transaction ID</span>
                    <span class="detail-value">{{ $transactionId }}</span>
                </div>
                @endisset
            </div>

            {{-- Order summary — populated by host app passing $orderItems, $orderTotal --}}
            @isset($orderItems)
            <div class="order-section">
                <div class="order-header">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Order Summary</h3>
                </div>
                @foreach ($orderItems as $item)
                <div class="order-item">
                    <span class="item-name">{{ $item['quantity'] }} × {{ $item['name'] }}</span>
                    <span class="item-price">{{ $item['price'] }}</span>
                </div>
                @endforeach
                @isset($orderTotal)
                <div class="order-total">
                    <span>Total Paid</span>
                    <span class="total-amount">{{ $orderTotal }}</span>
                </div>
                @endisset
            </div>
            @endisset

            <div class="action-buttons">
                <button type="button" id="view-order-btn" class="btn btn-primary">
                    <i class="fas fa-eye"></i> View Order
                </button>
                <button type="button" id="continue-btn" class="btn btn-secondary">
                    <i class="fas fa-shopping-cart"></i> Continue Shopping
                </button>
            </div>

            <div class="redirect-timer">
                Redirecting in <span id="redirect-countdown">10</span> seconds
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
jQuery(document).ready(function($) {
    var homeUrl      = '{{ $homeUrl ?? "/" }}';
    var orderViewUrl = '{{ $orderViewUrl ?? "/" }}';
    var shopUrl      = '{{ $shopUrl ?? "/" }}';
    var countdown    = 10;
    var timer = setInterval(function() {
        countdown--;
        $('#redirect-countdown').text(countdown);
        if (countdown <= 0) { clearInterval(timer); window.location.href = homeUrl; }
    }, 1000);

    $('#view-order-btn').on('click', function() { clearInterval(timer); window.location.href = orderViewUrl; });
    $('#continue-btn').on('click', function()   { clearInterval(timer); window.location.href = shopUrl; });
});
</script>
</body>
</html>
