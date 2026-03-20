<?php
session_start();
require 'connection.php';
require 'functions.php'; // contains send_email()

if ($_SERVER["REQUEST_METHOD"] === "POST") 
{
    $email = trim($_POST['email']);
    $sql = "SELECT * FROM user_tbl WHERE user_email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) 
	{
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+2 minutes"));

        // store otp
        $update = "UPDATE users_tbl SET otp='$otp', otp_expiry='$expiry' WHERE user_email='$email'";
        mysqli_query($conn, $update);

        // send OTP mail
        $subject = "Your " . $company_name . " Password Reset OTP";
        $body = "
        <h2>" . $company_name . " Password Reset</h2>
        <p>Hello, your OTP for password reset is:</p>
        <h1 style='color:#4CAF50;'>$otp</h1>
        <p>This OTP is valid for 5 minutes.</p>";

        if (send_email($email, $subject, $body)) 
		{
            $_SESSION['reset_email'] = $email;
            header("Location: verify_otp.php");
            exit;
        } 
		else 
		{
            $error = "❌ Failed to send OTP. Please try again.";
        }
    } 
	else 
	{
        $error = "❌ No user found with this email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - <?php echo $company_name; ?></title>
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
      width: 350px; text-align: center;
    }
    input {
      width: 90%; padding: 10px; margin: 10px 0;
      border: 1px solid #ccc; border-radius: 5px;
    }
    button {
      width: 95%; padding: 10px; background: #4CAF50;
      border: none; color: #fff; border-radius: 5px;
      font-weight: bold; cursor: pointer;
    }
    button:hover { background: #388E3C; }
    a { text-decoration: none; color: #4CAF50; }
    p.error { color: red; }
  </style>

  <div class="box">
    <h2 style="color:#4CAF50;">Forgot Password?</h2>
    <p>Enter your registered email to receive OTP.</p>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" id="forgotForm">
      <input type="email" name="email" id="forgotEmail" placeholder="Enter Email Address"><br>
      <button type="submit">Send OTP</button>
    </form>
    <p><a href="login.php">Back to Login</a></p>
  </div>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.getElementById('forgotForm').addEventListener('submit', function(e) {
      const email = document.getElementById('forgotEmail').value.trim();
      if (!email) {
        e.preventDefault();
        Swal.fire('Error', 'Please enter your email address.', 'error');
        return false;
      }
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        e.preventDefault();
        Swal.fire('Error', 'Please enter a valid email address.', 'error');
        return false;
      }
    });
  </script>
