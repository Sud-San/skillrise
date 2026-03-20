<?php
session_start();
include_once 'connection.php';

$alert = "";

// If already logged in, redirect to dashboard
if (!empty($_SESSION['tutor_logged']) && $_SESSION['tutor_logged'] === true) {
    header("Location: index.php");
    exit();
}

// Check if email session exists
if (empty($_SESSION['tutor_reset_email'])) {
    header("Location: tutor_forgot_password.php");
    exit();
}

if (isset($_POST['otp'])) {
    $entered_otp = trim($_POST['otp']);
    
    // Check if OTP expired
    if (time() > $_SESSION['tutor_otp_expire']) {
        $alert = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'OTP Expired',
                    text: 'Your verification code has expired. Please request a new one.',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location.href = 'tutor_forgot_password.php';
                });
            </script>
        ";
    } 
    // Verify OTP
    elseif ($entered_otp == $_SESSION['tutor_otp']) {
        // OTP is correct, redirect to reset password
        $_SESSION['otp_verified'] = true;
        $alert = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Verified!',
                    text: 'Code verified successfully.',
                    timer: 1500,
                    timerProgressBar: true,
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location.href = 'tutor_reset_password.php';
                });
            </script>
        ";
    } else {
        $alert = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Code',
                    text: 'The verification code you entered is incorrect.',
                    confirmButtonColor: '#10b981'
                });
            </script>
        ";
    }
}

$company_name = isset($company_name) ? $company_name : 'TutorPanel';

// Calculate remaining time
$remaining_seconds = max(0, $_SESSION['tutor_otp_expire'] - time());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Verify OTP | <?php echo htmlspecialchars($company_name); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="shortcut icon" href="codez3.png">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --emerald:     #10b981;
            --emerald-mid: #059669;
            --forest:      #065f46;
            --forest-deep: #022c22;
            --mint-light:  #ecfdf5;
            --white:       #ffffff;
            --text-primary:   #0f172a;
            --text-secondary: #475569;
            --text-muted:     #94a3b8;
            --border:         #e2e8f0;
        }

        html, body { height: 100%; min-height: 100vh; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(150deg, var(--forest-deep) 0%, #044533 38%, #047a55 70%, var(--emerald-mid) 100%);
            color: var(--text-primary);
            overflow: hidden;
            position: relative;
        }

        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(100px); opacity: 0.16;
            animation: drift 10s ease-in-out infinite alternate;
            pointer-events: none; z-index: 0;
        }
        .orb-1 { width: 550px; height: 550px; background: #10b981; top: -180px; left: -150px; }
        .orb-2 { width: 400px; height: 400px; background: #34d399; bottom: -120px; right: -120px; animation-delay: 3s; }

        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(22px,30px) scale(1.08); }
        }

        .noise {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .container {
            position: relative; z-index: 1;
            display: flex; align-items: center; justify-content: center;
            width: 100%; min-height: 100vh;
            padding: 2rem 1.5rem;
        }

        .verify-card {
            background: var(--white);
            border-radius: 26px;
            padding: 2.6rem 2.4rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 30px 80px rgba(2,44,34,.55), 0 4px 20px rgba(0,0,0,.12);
            border: 1px solid rgba(255,255,255,.07);
            animation: cardIn .9s cubic-bezier(.22,1,.36,1) .1s both;
        }

        @keyframes cardIn {
            from { opacity:0; transform:translateY(26px) scale(.97); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }

        .logo-area { display:flex; align-items:center; gap:11px; margin-bottom:1.8rem; }
        .logo-mark {
            width:41px; height:41px; border-radius:11px;
            background:linear-gradient(135deg, var(--emerald) 0%, var(--forest) 100%);
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 4px 14px rgba(16,185,129,.38); flex-shrink:0;
        }
        .logo-mark svg { width:20px; height:20px; }
        .logo-name {
            font-family:'Cormorant Garamond',serif;
            font-size:1.25rem; font-weight:700;
            color:var(--text-primary); line-height:1; letter-spacing:-0.01em;
        }
        .logo-tag {
            font-size:9.5px; font-weight:600;
            text-transform:uppercase; letter-spacing:0.1em;
            color:var(--emerald); margin-top:2px;
        }

        .card-heading h1 {
            font-family:'Cormorant Garamond',serif;
            font-size:1.8rem; font-weight:700;
            color:var(--text-primary); line-height:1.15;
            letter-spacing:-0.025em; margin-bottom:0.3rem;
        }
        .card-heading p { 
            font-size:13px; color:var(--text-secondary); 
            line-height:1.6; margin-bottom:0.5rem; 
        }
        
        .email-display {
            background: #f8fafc;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1.6rem;
            border: 1px solid var(--border);
        }
        .email-display strong {
            color: var(--emerald);
            font-size: 14px;
        }

        .timer-display {
            text-align: center;
            margin-bottom: 1.2rem;
            padding: 12px;
            background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(6,95,70,0.05));
            border-radius: 10px;
        }
        .timer-display .time {
            font-size: 24px;
            font-weight: 700;
            color: var(--emerald);
            font-family: 'Courier New', monospace;
        }
        .timer-display .label {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .form-group { margin-bottom:1.35rem; }
        .form-group label {
            display:block; font-size:11.5px; font-weight:600;
            color:var(--text-primary); margin-bottom:5px; letter-spacing:0.01em;
            text-align: center;
        }
        
        .otp-input-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .otp-input {
            width: 50px;
            height: 55px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            border: 2px solid var(--border);
            border-radius: 10px;
            background: #f8fafc;
            color: var(--text-primary);
            outline: none;
            transition: all 0.2s;
        }
        
        .otp-input:focus {
            border-color: var(--emerald);
            background: white;
            box-shadow: 0 0 0 3px rgba(16,185,129,.1);
        }
        
        .otp-input:hover {
            border-color: #cbd5e1;
            background: white;
        }

        .btn-submit {
            width:100%; padding:12.5px; border:none; border-radius:10px;
            font-family:'Plus Jakarta Sans',sans-serif;
            font-size:14px; font-weight:700; color:#fff; cursor:pointer;
            background:linear-gradient(135deg, var(--emerald) 0%, var(--forest) 100%);
            box-shadow:0 4px 18px rgba(16,185,129,.38);
            position:relative; overflow:hidden;
            transition:transform .2s, box-shadow .2s; letter-spacing:0.01em;
        }
        .btn-submit::before {
            content:''; position:absolute; inset:0;
            background:linear-gradient(135deg, rgba(255,255,255,.18) 0%, transparent 60%);
            opacity:0; transition:opacity .25s;
        }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(16,185,129,.48); }
        .btn-submit:hover::before { opacity:1; }
        .btn-submit:active { transform:translateY(0); }

        .card-footer { 
            text-align:center; margin-top:1.35rem; 
            font-size:12px; color:var(--text-muted); 
        }
        .card-footer a { 
            color:var(--emerald-mid); font-weight:600; 
            text-decoration:none; transition:color .2s; 
        }
        .card-footer a:hover { color:var(--forest); }

        .resend-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 13px;
        }
        .resend-link a {
            color: var(--emerald-mid);
            font-weight: 600;
            text-decoration: none;
            transition: color .2s;
        }
        .resend-link a:hover {
            color: var(--forest);
        }
    </style>
</head>
<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="noise"></div>

    <div class="container">
        <div class="verify-card">

            <div class="logo-area">
                <div class="logo-mark">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3L2 8l10 5 10-5-10-5Z" fill="white" opacity=".9"/>
                        <path d="M2 13l10 5 10-5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity=".7"/>
                        <path d="M2 17.5l10 5 10-5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity=".4"/>
                    </svg>
                </div>
                <div>
                    <div class="logo-name">TutorPanel</div>
                    <div class="logo-tag">Tutor Portal</div>
                </div>
            </div>

            <div class="card-heading">
                <h1>Verify Your Email 📧</h1>
                <p>We've sent a 6-digit verification code to:</p>
            </div>
            
            <div class="email-display">
                <strong><?php echo htmlspecialchars($_SESSION['tutor_reset_email']); ?></strong>
            </div>

            <div class="timer-display">
                <div class="time" id="timer"><?php echo gmdate("i:s", $remaining_seconds); ?></div>
                <div class="label">Time Remaining</div>
            </div>

            <form action="" method="POST" id="otpForm">
                <div class="form-group">
                    <label>Enter Verification Code</label>
                </div>
                
                <div class="otp-input-group">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                </div>

                <input type="hidden" name="otp" id="otpValue">

                <button class="btn-submit" type="submit">Verify Code</button>
            </form>

            <div class="resend-link">
                Didn't receive the code? <a href="#" id="resendLink">Resend Code</a>
            </div>

            <p class="card-footer">
                <a href="login.php">Back to Login</a>
            </p>

        </div>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ALERTS (must be after SweetAlert2 is loaded) -->
    <?php if (!empty($alert)) echo $alert; ?>

    <script>
        // OTP Input handling
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpForm = document.getElementById('otpForm');
        const otpValue = document.getElementById('otpValue');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Only allow numbers
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                // Move to next input
                if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                
                // Update hidden field
                updateOTPValue();
            });

            input.addEventListener('keydown', (e) => {
                // Move to previous input on backspace
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
                
                for (let i = 0; i < pasteData.length && index + i < otpInputs.length; i++) {
                    otpInputs[index + i].value = pasteData[i];
                }
                
                if (index + pasteData.length < otpInputs.length) {
                    otpInputs[index + pasteData.length].focus();
                }
                
                updateOTPValue();
            });
        });

        function updateOTPValue() {
            let otp = '';
            otpInputs.forEach(input => {
                otp += input.value;
            });
            otpValue.value = otp;
        }

        // Timer countdown
        let remainingSeconds = <?php echo $remaining_seconds; ?>;
        const timerElement = document.getElementById('timer');
        const timerDisplay = document.querySelector('.timer-display');
        const submitBtn = document.querySelector('.btn-submit');
        const resendLink = document.getElementById('resendLink');

        function lockInputs() {
            otpInputs.forEach(input => {
                input.value = '';
                input.disabled = true;
                input.style.background = '#f1f5f9';
                input.style.color = '#94a3b8';
                input.style.borderColor = '#e2e8f0';
                input.style.cursor = 'not-allowed';
            });
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.45';
            submitBtn.style.cursor = 'not-allowed';
            timerElement.textContent = '00:00';
            timerElement.style.color = '#ef4444';
            timerDisplay.style.background = 'linear-gradient(135deg, rgba(239,68,68,0.08), rgba(239,68,68,0.03))';
            resendLink.style.color = '#ef4444';
            resendLink.style.fontWeight = '700';
            resendLink.style.textDecoration = 'underline';
            Swal.fire({
                icon: 'warning',
                title: 'Code Expired',
                text: 'Your code has expired. Click "Resend Code" to get a new one.',
                confirmButtonColor: '#10b981'
            });
        }

        function unlockInputs() {
            otpInputs.forEach(input => {
                input.disabled = false;
                input.value = '';
                input.style.background = '#f8fafc';
                input.style.color = '';
                input.style.borderColor = '';
                input.style.cursor = '';
            });
            submitBtn.disabled = false;
            submitBtn.style.opacity = '';
            submitBtn.style.cursor = '';
            timerElement.style.color = '#10b981';
            timerDisplay.style.background = 'linear-gradient(135deg, rgba(16,185,129,0.1), rgba(6,95,70,0.05))';
            resendLink.style.color = '';
            resendLink.style.fontWeight = '';
            resendLink.style.textDecoration = '';
            resendLink.style.pointerEvents = '';
            resendLink.textContent = 'Resend Code';
            otpInputs[0].focus();
        }

        function updateTimer() {
            if (remainingSeconds <= 0) {
                lockInputs();
                return;
            }
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            timerElement.textContent =
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            if (remainingSeconds <= 60) timerElement.style.color = '#f59e0b';
            if (remainingSeconds <= 30) timerElement.style.color = '#ef4444';
            remainingSeconds--;
            setTimeout(updateTimer, 1000);
        }

        // Resend OTP — calls resend_otp.php, stays on this page
        resendLink.addEventListener('click', function(e) {
            e.preventDefault();
            resendLink.style.pointerEvents = 'none';
            resendLink.textContent = 'Sending...';

            fetch('resend_otp.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    unlockInputs();
                    remainingSeconds = 300;
                    updateTimer();
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Sent!',
                        text: 'A new verification code has been sent to your email.',
                        timer: 2500,
                        timerProgressBar: true,
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    resendLink.textContent = 'Resend Code';
                    resendLink.style.pointerEvents = '';
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    });
                }
            })
            .catch(() => {
                resendLink.textContent = 'Resend Code';
                resendLink.style.pointerEvents = '';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again.',
                    confirmButtonColor: '#10b981'
                });
            });
        });

        // Start timer
        updateTimer();

        // Focus first input on load
        otpInputs[0].focus();
    </script>

</body>
</html>