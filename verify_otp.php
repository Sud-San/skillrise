<?php
	session_start();
	require 'connection.php';

	if (!isset($_SESSION['reset_email'])) 
	{
		header("Location: forgot_password.php");
		exit;
	}

	$email = $_SESSION['reset_email'];

	// Fetch expiry time from DB
	$sql = "SELECT otp_expiry FROM users_tbl WHERE user_email='$email'";
	$res = mysqli_query($conn, $sql);
	$row = mysqli_fetch_assoc($res);
	$expiry = $row ? strtotime($row['otp_expiry']) : time();

	// Handle OTP verification
	if ($_SERVER["REQUEST_METHOD"] === "POST") 
	{
		$otp = trim($_POST['otp_full']); // Combined 6 digits
		$sql = "SELECT * FROM users_tbl WHERE user_email='$email' AND otp='$otp'";
		$res = mysqli_query($conn, $sql);

		if (mysqli_num_rows($res) > 0) 
		{
			$row = mysqli_fetch_assoc($res);
			if (date("Y-m-d H:i:s") < $row['otp_expiry']) 
			{
				header("Location: reset_password.php");
				exit;
			} 
			else 
			{
				$error = "❌ OTP expired! Request new one.";
			}
		}
		else 
		{
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
  <style>
    body {
      margin: 0; padding: 0;
      background: linear-gradient(to right, #e0f7fa, #e8f5e9);
      height: 100vh; display: flex;
      justify-content: center; align-items: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .box {
      background: #fff; padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      width: 370px; text-align: center;
    }
    .otp-container {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 20px 0;
    }
    .otp-box {
      width: 45px; height: 45px;
      text-align: center;
      font-size: 20px;
      border: 2px solid #ccc;
      border-radius: 8px;
      outline: none;
      transition: 0.2s;
    }
    .otp-box:focus { border-color: #4CAF50; }
    button {
      width: 95%; padding: 10px; background: #4CAF50;
      border: none; color: #fff; border-radius: 5px;
      font-weight: bold; cursor: pointer;
      margin-top: 10px;
    }
    button:hover { background: #388E3C; }
    a { text-decoration: none; color: #4CAF50; }
    p.error { color: red; }
    #timer {
      color: red;
      font-weight: bold;
      margin-top: 5px;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2 style="color:#4CAF50;">Verify OTP</h2>
    <p>We’ve sent a 6-digit OTP to <b><?php echo htmlspecialchars($email); ?></b></p>
    <div id="timer">Checking expiry...</div>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

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

    <p><a href="forgot_password.php">Resend OTP</a></p>
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

    form.addEventListener('submit', function(e) {
      let otp = Array.from(boxes).map(b => b.value).join('');
      if (otp.length < 6) {
        e.preventDefault();
        Swal.fire('Error', 'Please enter all 6 digits.', 'error');
        return;
      }
      hiddenOtp.value = otp;
    });

    // ========== TIMER LOGIC ==========
    const expiryTimestamp = <?php echo $expiry * 1000; ?>; // from PHP (ms)
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
