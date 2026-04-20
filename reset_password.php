<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['reset_email'])) {
  header("Location: forgot_password.php");
  exit;
}

$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $new_pass = trim($_POST['new_pass']);
  $confirm_pass = trim($_POST['confirm_pass']);

  if ($new_pass === $confirm_pass) {
    $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
    $update = "UPDATE user_tbl SET user_password='$hashed_password' WHERE user_email='$email'";

    if (mysqli_query($conn, $update)) {
      unset($_SESSION['reset_email']);
      unset($_SESSION['reset_otp']);
      unset($_SESSION['reset_otp_expiry']);
      $success = "✅ Password reset successfully! You can now login.";
      header("refresh:10; url=index.php");

    } else {
      $error = "❌ Error updating password.";
    }
  } else {
    $error = "❌ Passwords do not match.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Reset Password - <?php echo $company_name; ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(to right, #e0f7fa, #e8f5e9);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', sans-serif;
    }

    .box {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      width: 350px;
      text-align: center;
    }

    input {
      width: 90%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      width: 95%;
      padding: 10px;
      background: #4CAF50;
      border: none;
      color: #fff;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }

    button:hover {
      background: #388E3C;
    }

    a {
      text-decoration: none;
      color: #4CAF50;
    }

    p.error {
      color: red;
    }

    p.success {
      color: green;
    }
  </style>
</head>

<body>
  <div class="box">
    <h2 style="color:#4CAF50;">Reset Password</h2>
    <p>Set a new password for <b><?php echo htmlspecialchars($email); ?></b></p>

    <?php if (isset($error))
      echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($success))
      echo "<p class='success'>$success</p>"; ?>

    <?php if (!isset($success)) { ?>
      <form method="POST" id="resetPassForm">
        <input type="password" name="new_pass" id="newPass" placeholder="Enter new password"><br>
        <input type="password" name="confirm_pass" id="confirmPass" placeholder="Confirm new password"><br>
        <button type="submit">Update Password</button>
      </form>
    <?php } ?>

    <p><a href="login.php">Back to Login</a></p>
  </div>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.getElementById('resetPassForm').addEventListener('submit', function (e) {
      const newPass = document.getElementById('newPass').value;
      const confirmPass = document.getElementById('confirmPass').value;

      if (!newPass || !confirmPass) {
        e.preventDefault();
        Swal.fire('Error', 'Please fill all password fields.', 'error');
        return false;
      }

      if (newPass.length < 6) {
        e.preventDefault();
        Swal.fire('Error', 'Password must be at least 6 characters.', 'error');
        return false;
      }

      if (newPass !== confirmPass) {
        e.preventDefault();
        Swal.fire('Error', 'Passwords do not match.', 'error');
        return false;
      }
    });
  </script>

</html>