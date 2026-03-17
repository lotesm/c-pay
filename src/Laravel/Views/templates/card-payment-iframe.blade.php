<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Payment - C-Pay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        :root{
            --primary:#2ecc71; --primary-dark:#27ae60; --primary-light:#d5f4e6;
            --danger:#e74c3c; --light:#ffffff; --dark:#2c3e50;
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
        .phone-info{ background:var(--primary-light); border-radius:14px; padding:28px; margin-bottom:34px; text-align:center; border:2px dashed rgba(46,204,113,.4); }
        .phone-label{ display:block; color:var(--primary-dark); font-size:14px; font-weight:700; margin-bottom:10px; text-transform:uppercase; letter-spacing:1px; }
        .phone-number{ font-size:32px; font-weight:800; color:var(--primary-dark); margin:16px 0; }
        .phone-hint{ color:var(--gray); font-size:15px; line-height:1.6; }
        .otp-section{ margin-bottom:34px; }
        .section-title{ font-size:19px; font-weight:700; color:var(--dark); margin-bottom:22px; display:flex; align-items:center; gap:10px; justify-content:center; }
        .section-title i{ color:var(--primary); font-size:22px; }
        .otp-inputs{ display:flex; gap:18px; justify-content:center; margin-bottom:16px; }
        .otp-input{
            width:80px; height:80px; font-size:40px; font-weight:700; text-align:center;
            border-radius:50%; border:4px solid var(--border); background:#f8fcf9; color:var(--dark);
            outline:none; caret-color:transparent; box-shadow:0 4px 14px rgba(0,0,0,.08);
            transition:var(--transition);
        }
        .otp-input:focus{ border-color:var(--primary); box-shadow:0 0 0 4px rgba(46,204,113,.2); transform:translateY(-2px); }
        .otp-input.filled{ border-color:var(--primary); background:white; color:var(--primary-dark); }
        .otp-hint{ text-align:center; color:var(--gray); font-size:15px; margin-top:16px; }
        .timer-section{ background:#fff8e1; border-radius:14px; padding:22px; margin:28px 0; text-align:center; border:2px solid #ffd54f; }
        .timer-label{ display:block; color:#f57c00; font-size:15px; margin-bottom:10px; font-weight:700; }
        #otp-timer{ font-size:38px; font-weight:800; color:var(--danger); font-family:'Courier New',monospace; letter-spacing:2px; }
        .action-buttons{ display:flex; gap:16px; margin-top:36px; }
        .btn{ flex:1; padding:22px; border:none; border-radius:14px; font-size:18px; font-weight:700; cursor:pointer; transition:var(--transition); display:flex; align-items:center; justify-content:center; gap:12px; min-height:72px; }
        .btn-primary{ background:linear-gradient(135deg,var(--primary),var(--primary-dark)); color:white; box-shadow:0 6px 20px rgba(46,204,113,.3); }
        .btn-primary:hover:not(:disabled){ transform:translateY(-3px); }
        .btn-secondary{ background:white; color:var(--primary); border:3px solid var(--primary); }
        .btn-secondary:hover:not(:disabled){ background:var(--primary-light); transform:translateY(-2px); }
        .btn:disabled{ opacity:.6; cursor:not-allowed; }
        .loading-state{ text-align:center; padding:50px; display:none; }
        .spinner{ border:5px solid var(--primary-light); border-top:5px solid var(--primary); border-radius:50%; width:70px; height:70px; animation:spin 1s linear infinite; margin:0 auto 24px; }
        @keyframes spin{ 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }
        .message-container{ margin-top:24px; }
        .message{ padding:18px; border-radius:12px; margin:12px 0; font-weight:600; display:flex; align-items:center; gap:12px; font-size:16px; }
        .message-error  { background:#fee; color:var(--danger); border:2px solid #fcc; }
        .message-success{ background:#d5f4e6; color:var(--primary-dark); border:2px solid #a3e4d7; }
        .back-link{ text-align:center; margin-top:30px; }
        .back-link a{ color:white; text-decoration:none; font-size:15px; display:inline-flex; align-items:center; gap:10px; padding:16px 28px; background:rgba(255,255,255,.15); border-radius:14px; font-weight:600; }
        .back-link a:hover{ background:rgba(255,255,255,.25); }
        @keyframes pulse{ 0%,100%{opacity:1} 50%{opacity:.7} }
        .pulse{ animation:pulse 1s infinite; }
        @media(max-width:600px){ .card-body{padding:28px} .action-buttons{flex-direction:column} .otp-input{width:68px;height:68px;font-size:34px} }
        @media(max-width:420px){ .otp-inputs{gap:12px} .otp-input{width:60px;height:60px;font-size:30px;border-width:3px} }
    </style>
</head>
<body>
<div class="cpay-container">
    <div class="logo-container">
        <img src="{{ asset('vendor/c-pay/assets/images/Visa.png') }}" alt="C-Pay" class="logo" onerror="this.style.display='none'">
        <p>Secure Mobile Payment Verification</p>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="header-icon"><i class="fas fa-shield-alt"></i></div>
            <h2>Payment Verification</h2>
            <p>Enter your OTP to confirm the transaction</p>
        </div>

        <div class="card-body">
            <div class="phone-info">
                <span class="phone-label">OTP Sent To</span>
                <div class="phone-number">{{ $phoneNumber ?? '••••••••••' }}</div>
                <p class="phone-hint">A 4-digit verification code has been sent via SMS to your mobile number.</p>
            </div>

            <div class="otp-section">
                <div class="section-title">
                    <i class="fas fa-key"></i>
                    <span>Enter Verification Code</span>
                </div>
                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" data-index="1" autocomplete="off" inputmode="numeric" placeholder="•">
                    <input type="text" class="otp-input" maxlength="1" data-index="2" autocomplete="off" inputmode="numeric" placeholder="•">
                    <input type="text" class="otp-input" maxlength="1" data-index="3" autocomplete="off" inputmode="numeric" placeholder="•">
                    <input type="text" class="otp-input" maxlength="1" data-index="4" autocomplete="off" inputmode="numeric" placeholder="•">
                </div>
                <input type="hidden" id="full-otp">
                <p class="otp-hint">Enter the 4-digit code as shown in your SMS</p>
            </div>

            <div class="timer-section">
                <span class="timer-label">Code Expires In</span>
                <div id="otp-timer">02:00</div>
            </div>

            @isset($orderTotal)
            <div style="background:#f8fcf9;border-radius:14px;padding:22px;margin-bottom:20px;border:2px solid var(--primary-light);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                    <i class="fas fa-shopping-bag" style="color:var(--primary);font-size:20px;"></i>
                    <strong style="font-size:18px;">Amount Due: {{ $orderTotal }}</strong>
                </div>
            </div>
            @endisset

            <div class="loading-state" id="loading-state">
                <div class="spinner"></div>
                <p>Verifying your payment...</p>
            </div>

            <div class="action-buttons" id="action-buttons">
                <button type="button" id="verify-btn" class="btn btn-primary">
                    <i class="fas fa-check-circle"></i> Verify & Pay
                </button>
                <button type="button" id="resend-btn" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Resend
                </button>
            </div>

            <div class="message-container" id="message-container"></div>
        </div>
    </div>

    <div class="back-link">
        <a href="{{ $checkoutUrl ?? '/' }}">
            <i class="fas fa-arrow-left"></i> Return to Checkout
        </a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
jQuery(document).ready(function($) {
    var verifyUrl   = '{{ $verifyUrl ?? "#" }}';
    var resendUrl   = '{{ $resendUrl ?? "#" }}';
    var checkoutUrl = '{{ $checkoutUrl ?? "/" }}';
    var processing  = false;
    var timeLeft    = 120;
    var otpTimer;

    initOTP();
    startTimer();
    setTimeout(function(){ $('.otp-input').first().focus(); }, 400);

    $('#verify-btn').on('click', verifyOTP);
    $('#resend-btn').on('click', resendOTP);

    function initOTP() {
        var inputs = $('.otp-input');

        inputs.on('input', function() {
            var idx = parseInt($(this).data('index'));
            var val = $(this).val();
            if (val.length === 1 && idx < 4) inputs.eq(idx).focus();
            $(this).toggleClass('filled', !!val);
            updateFull();
            if (getOTP().length === 4) setTimeout(verifyOTP, 300);
        });

        inputs.on('keydown', function(e) {
            var idx = parseInt($(this).data('index'));
            if (e.key === 'Backspace' && !$(this).val() && idx > 1) inputs.eq(idx - 2).focus();
            if (!/[0-9]/.test(e.key) && !['Backspace','Delete','Tab','ArrowLeft','ArrowRight'].includes(e.key) && e.key.length === 1) e.preventDefault();
        });

        inputs.first().on('paste', function(e) {
            e.preventDefault();
            var nums = e.originalEvent.clipboardData.getData('text').replace(/\D/g,'').split('').slice(0,4);
            nums.forEach(function(n,i){ inputs.eq(i).val(n).addClass('filled'); });
            updateFull();
            if (nums.length === 4) setTimeout(verifyOTP, 300);
        });
    }

    function getOTP()    { return $('.otp-input').map(function(){ return $(this).val(); }).get().join(''); }
    function updateFull(){ $('#full-otp').val(getOTP()); }

    function startTimer() {
        clearInterval(otpTimer);
        timeLeft = 120;
        tick();
        otpTimer = setInterval(tick, 1000);
    }

    function tick() {
        var m = Math.floor(timeLeft / 60), s = timeLeft % 60;
        $('#otp-timer').text((m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s);
        if (timeLeft < 30) $('#otp-timer').addClass('pulse');
        if (timeLeft <= 0) {
            clearInterval(otpTimer);
            showMsg('Code expired. Please request a new one.', 'error');
        }
        timeLeft--;
    }

    function verifyOTP() {
        var otp = getOTP();
        if (processing) return;
        if (otp.length !== 4) { showMsg('Please enter all 4 digits.', 'error'); return; }

        processing = true;
        showLoading(true);

        $.post(verifyUrl, { otp: otp, _token: '{{ csrf_token() }}' }, function(res) {
            if (res.success) {
                showMsg('Verified! Redirecting...', 'success');
                setTimeout(function(){ window.location.href = res.data.redirect; }, 1200);
            } else {
                processing = false;
                showLoading(false);
                showMsg(res.data || 'Verification failed. Try again.', 'error');
                clearOTP();
            }
        }).fail(function() {
            processing = false;
            showLoading(false);
            showMsg('An error occurred. Please try again.', 'error');
        });
    }

    function resendOTP() {
        if (processing) return;
        processing = true;
        $('#resend-btn').prop('disabled', true).html('<i class="fas fa-redo"></i> Sending...');

        $.post(resendUrl, { _token: '{{ csrf_token() }}' }, function(res) {
            processing = false;
            $('#resend-btn').prop('disabled', false).html('<i class="fas fa-redo"></i> Resend');
            if (res.success) { showMsg('New code sent!', 'success'); clearOTP(); startTimer(); }
            else showMsg(res.data || 'Failed to resend.', 'error');
        }).fail(function() {
            processing = false;
            $('#resend-btn').prop('disabled', false).html('<i class="fas fa-redo"></i> Resend');
            showMsg('Failed to resend. Try again.', 'error');
        });
    }

    function showLoading(show) {
        if (show) { $('.otp-section,.timer-section,#action-buttons').hide(); $('#loading-state').show(); }
        else       { $('.otp-section,.timer-section,#action-buttons').show(); $('#loading-state').hide(); }
    }

    function showMsg(msg, type) {
        var icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
        $('#message-container').html(
            '<div class="message message-' + type + '"><i class="fas ' + icon + '"></i><span>' + msg + '</span></div>'
        ).show();
    }

    function clearOTP() {
        $('.otp-input').val('').removeClass('filled');
        $('#full-otp').val('');
        $('.otp-input').first().focus();
    }
});
</script>
</body>
</html>
