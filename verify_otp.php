<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['reset_email'])) {
  header("Location: forgot_password.php");
  exit;
}

$email = $_SESSION['reset_email'];
$expiry_ts = $_SESSION['reset_otp_expiry'] ?? 0;

// Handle OTP verification
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['otp_full'])) {
  $user_otp = trim($_POST['otp_full']);
  $session_otp = $_SESSION['reset_otp'] ?? '';

  if ($user_otp == $session_otp) {
    if (time() < $expiry_ts) {
      header("Location: reset_password.php");
      exit;
    } else {
      $error = "❌ OTP expired! Request new one.";
    }
  } else {
    $error = "❌ Invalid OTP.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Verify OTP - Codezy</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    :root {
      --primary: #10b981;
      --primary-dark: #059669;
      --bg-gradient: linear-gradient(135deg, #f0fdf4 0%, #d1fae5 100%);
    }

    body {
      margin: 0;
      padding: 0;
      background: var(--bg-gradient);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
    }

    .box {
      background: #fff;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
      width: 400px;
      text-align: center;
      border: 1px solid rgba(16, 185, 129, 0.1);
    }

    h2 {
      margin-top: 0;
      font-weight: 700;
      color: #1e293b;
    }

    .otp-container {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin: 25px 0;
    }

    .otp-box {
      width: 48px;
      height: 54px;
      text-align: center;
      font-size: 24px;
      font-weight: 700;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      outline: none;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      color: #334155;
      background: #f8fafc;
    }

    .otp-box:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
      background: #fff;
    }

    button {
      width: 100%;
      padding: 14px;
      background: var(--primary);
      border: none;
      color: #fff;
      border-radius: 12px;
      font-weight: 700;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.2s;
      margin-top: 15px;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }

    button:hover {
      background: var(--primary-dark);
      transform: translateY(-1px);
      box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
    }

    button:disabled {
      background: #94a3b8;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .resend-link {
      margin-top: 20px;
      font-size: 14px;
      color: #64748b;
    }

    .resend-link a {
      text-decoration: none;
      color: var(--primary);
      font-weight: 600;
    }

    .resend-link a:hover {
      text-decoration: underline;
    }

    #timer {
      font-weight: 600;
      margin-top: 10px;
      font-size: 15px;
      color: #64748b;
      display: inline-block;
      padding: 4px 12px;
      background: #f1f5f9;
      border-radius: 20px;
    }

    .error-text {
      color: #ef4444;
      background: #fef2f2;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      font-weight: 500;
    }
  </style>
</head>

<body>
  <div class="box">
    <h2>Verify OTP</h2>
    <p style="color: #64748b; font-size: 15px;">Check your email: <b><?php echo htmlspecialchars($email); ?></b></p>
    <div id="timer">Loading...</div>
    
    <div style="margin-top: 20px;">
      <?php if (isset($error)) echo "<div class='error-text'>$error</div>"; ?>
    </div>

    <form method="POST" id="otpForm">
      <div class="otp-container">
        <input type="text" maxlength="1" class="otp-box">
        <input type="text" maxlength="1" class="otp-box">
        <input type="text" maxlength="1" class="otp-box">
        <input type="text" maxlength="1" class="otp-box">
        <input type="text" maxlength="1" class="otp-box">
        <input type="text" maxlength="1" class="otp-box">
      </div>
      <button type="submit" id="verifyBtn">Verify OTP</button>
      <input type="hidden" name="otp_full" id="otp_full">
    </form>

      <p class="resend-link">Didn't get the code? <a href="forgot_password.php">Resend OTP</a></p>
  </div>

  <script>
    // ========== OTP BOX LOGIC ==========
    const boxes = document.querySelectorAll('.otp-box');
    const hiddenOtp = document.getElementById('otp_full');
    const form = document.getElementById('otpForm');

    boxes.forEach((box, index) => {
      box.addEventListener('input', () => {
        if (box.value.length === 1 && index < boxes.length - 1) {
          boxes[index + 1].focus();
        }
      });
      box.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !box.value && index > 0) {
          boxes[index - 1].focus();
        }
      });
    });
    boxes[0].focus();

    form.addEventListener('submit', function (e) {
      let otp = Array.from(boxes).map(b => b.value).join('');
      if (otp.length < 6) {
        e.preventDefault();
        Swal.fire('Error', 'Please enter all 6 digits.', 'error');
        return;
      }
      hiddenOtp.value = otp;
    });

    // ========== TIMER LOGIC ==========
    const expiryTimestamp = <?php echo $expiry_ts * 1000; ?>; // from PHP (ms)
    const timerDisplay = document.getElementById('timer');
    const verifyBtn = document.getElementById('verifyBtn');

    function updateTimer() {
      const now = new Date().getTime();
      const diff = expiryTimestamp - now;

      if (diff <= 0) {
        timerDisplay.textContent = "OTP expired!";
        verifyBtn.disabled = true;
        boxes.forEach(b => b.disabled = true);
        clearInterval(timer);
      } else {
        const minutes = Math.floor(diff / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);
        timerDisplay.textContent = `Expires in: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
      }
    }

    const timer = setInterval(updateTimer, 1000);
    updateTimer();
  </script>
</body>

</html>