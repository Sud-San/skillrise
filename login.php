<?php
session_set_cookie_params(["path" => "/"]);
session_start();
require 'connection.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $msg = "Please enter email and password.";
  } else {
    // Check tutor first
    $tutor_sql = "SELECT * FROM tutor_tbl WHERE tutor_email='$email'";
    $tutor_result = mysqli_query($conn, $tutor_sql);

    if (mysqli_num_rows($tutor_result) > 0) {
      $tutor_row = mysqli_fetch_assoc($tutor_result);

      // Try password_verify first (for hashed passwords)
      if (password_verify($password, $tutor_row['password'])) {
        if ($tutor_row['tutor_status'] == 1 && $tutor_row['verification_status'] === 'approved') {
          $_SESSION['user_id'] = $tutor_row['tutor_id'];
          $_SESSION['profile_pic'] = $tutor_row['profile_pic'];
          $_SESSION['user_name'] = $tutor_row['tutor_name'];
          $_SESSION['user_email'] = $tutor_row['tutor_email'];
          $_SESSION['user_phone'] = $tutor_row['tutor_phone'];
          $_SESSION['user_role'] = 'tutor';

          header("Location: index.php");
          exit;
        } else {
          $msg = "Your tutor account is pending approval or inactive.";
        }
      }
      // Fallback: direct comparison (remove this in production)
      else if ($password === $tutor_row['password']) {
        if ($tutor_row['tutor_status'] == 1 && $tutor_row['verification_status'] === 'approved') {
          $_SESSION['user_id'] = $tutor_row['tutor_id'];
          $_SESSION['profile_pic'] = $tutor_row['profile_pic'];
          $_SESSION['user_name'] = $tutor_row['tutor_name'];
          $_SESSION['user_email'] = $tutor_row['tutor_email'];
          $_SESSION['user_phone'] = $tutor_row['tutor_phone'];
          $_SESSION['user_role'] = 'tutor';

          header("Location: index.php");
          exit;
        } else {
          $msg = "Your tutor account is pending approval or inactive.";
        }
      } else {
        $msg = "Invalid credentials.";
      }
    }
    // Check student
    else {
      $student_sql = "SELECT * FROM user_tbl WHERE user_email='$email'";
      $student_result = mysqli_query($conn, $student_sql);

      if (mysqli_num_rows($student_result) > 0) {
        $student_row = mysqli_fetch_assoc($student_result);

        // Try password_verify first (for hashed passwords)
        if (password_verify($password, $student_row['user_password'])) {
          if ($student_row['user_status'] == 1) {
            $_SESSION['user_id'] = $student_row['user_id'];
            $_SESSION['user_name'] = $student_row['user_name'];
            $_SESSION['user_email'] = $student_row['user_email'];
            $_SESSION['user_role'] = 'student';
            $_SESSION['user_profile_pic'] = $user_profile_path . ($student_row['profile_pic'] ?? 'assets/images/default.png');
            $_SESSION['game_preloader'] = 0;

            // Get additional details from user_details
            $details_sql = "SELECT * FROM user_details WHERE user_id = " . $student_row['user_id'];
            $details_result = mysqli_query($conn, $details_sql);
            if (mysqli_num_rows($details_result) > 0) {
              $details = mysqli_fetch_assoc($details_result);
              $_SESSION['user_mobile'] = $details['mobile'] ?? '';
              $_SESSION['user_gender'] = $details['gender'] ?? '';
            }
            if (isset($_SESSION['prelogin_redirect'])) {
              $redirect_url = $_SESSION['prelogin_redirect'];
              unset($_SESSION['prelogin_redirect']);
              header("Location: " . $redirect_url);
              exit;
            }
            header("Location: index.php");
            exit;
          } else {
            $msg = "Your account is inactive.";
          }
        }
        // Fallback: direct comparison (remove this in production)
        else if ($password === $student_row['user_password']) {
          if ($student_row['user_status'] == 1) {
            $_SESSION['user_id'] = $student_row['user_id'];
            $_SESSION['user_name'] = $student_row['user_name'];
            $_SESSION['user_email'] = $student_row['user_email'];
            $_SESSION['user_role'] = 'student';
            $_SESSION['game_preloader'] = 0;
            $_SESSION['user_profile_pic'] = $user_profile_path . ($student_row['profile_pic'] ?? 'assets/images/default.png');

            $details_sql = "SELECT * FROM user_details WHERE user_id = " . $student_row['user_id'];
            $details_result = mysqli_query($conn, $details_sql);
            if (mysqli_num_rows($details_result) > 0) {
              $details = mysqli_fetch_assoc($details_result);
              $_SESSION['user_mobile'] = $details['mobile'] ?? '';
              $_SESSION['user_gender'] = $details['gender'] ?? '';
            }
            if (isset($_SESSION['prelogin_redirect'])) {
              $redirect_url = $_SESSION['prelogin_redirect'];
              unset($_SESSION['prelogin_redirect']);
              header("Location: " . $redirect_url);
              exit;
            }
            header("Location: index.php");
            exit;
          } else {
            $msg = "Your account is inactive.";
          }
        } else {
          $msg = "Invalid credentials.";
        }
      } else {
        $msg = "Email not found.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?php echo $company_name; ?> Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #e8f5f3 0%, #e0f2ef 50%, #d1f0ea 100%);
      height: 100vh;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
    }

    .animated-icons {
      position: absolute;
      width: 100%;
      height: 100%;
      z-index: -1;
      top: 0;
      left: 0;
      overflow: hidden;
    }

    .icon {
      position: absolute;
      font-size: 3rem;
      opacity: 0.4;
      animation: floatIcons 15s linear infinite;
    }

    @keyframes floatIcons {
      0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0.1;
      }

      100% {
        transform: translateY(-10vh) rotate(360deg);
        opacity: 0.6;
      }
    }

    .icon:nth-child(1) {
      left: 5%;
      animation-duration: 12s;
    }

    .icon:nth-child(2) {
      left: 20%;
      animation-duration: 18s;
    }

    .icon:nth-child(3) {
      left: 35%;
      animation-duration: 14s;
    }

    .icon:nth-child(4) {
      left: 50%;
      animation-duration: 16s;
    }

    .icon:nth-child(5) {
      left: 65%;
      animation-duration: 19s;
    }

    .icon:nth-child(6) {
      left: 80%;
      animation-duration: 13s;
    }

    .icon:nth-child(7) {
      left: 90%;
      animation-duration: 17s;
    }

    .login-box {
      background: white;
      margin-top: 10px;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      z-index: 1;
      width: 340px;
      text-align: center;
      position: relative;
    }

    .login-box h2 {
      margin-bottom: 20px;
      color: #333;
    }

    .input-wrapper {
      position: relative;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .input-wrapper input {
      width: 100%;
      max-width: 260px;
      padding: 10px 35px 10px 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }

    .tick {
      position: absolute;
      right: 40px;
      top: 50%;
      transform: translateY(-50%);
      color: green;
      font-size: 18px;
      display: none;
      pointer-events: none;
    }

    .eye-toggle {
      position: absolute;
      right: 40px;
      top: 50%;
      transform: translateY(-50%);
      color: #555;
      font-size: 18px;
      cursor: pointer;
    }

    .login-box button {
      padding: 10px 20px;
      background: #054b40;
      border: none;
      border-radius: 8px;
      color: white;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 300px;
      box-shadow: 0 4px 12px rgba(5, 75, 64, 0.2);
    }

    .login-box button:hover {
      background: #033d35;
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(5, 75, 64, 0.3);
    }

    .forgot-link {
      display: block;
      margin-top: 12px;
      font-size: 0.9rem;
      color: #054b40;
      text-decoration: none;
      text-align: left;
    }

    .forgot-link:hover {
      text-decoration: underline;
    }

    .error-msg {
      color: red;
      font-size: 0.9rem;
      margin-top: -10px;
      margin-bottom: 10px;
      display: block;
    }

    /* Success Animation */
    .success-check {
      display: none;
      margin-top: 30px;
      animation: pop 0.6s ease-out forwards;
    }

    .check-circle {
      width: 60px;
      height: 60px;
      background: #054b40;
      border-radius: 50%;
      margin: 0 auto;
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: 0 0 10px rgba(0, 128, 0, 0.3);
      transform: scale(0);
      animation: scaleUp 0.4s ease-out forwards;
    }

    .check-circle::before {
      content: '✔';
      color: white;
      font-size: 30px;
      font-weight: bold;
    }

    .success-msg {
      margin-top: 15px;
      color: #054b40;
      font-weight: bold;
      font-size: 16px;
      opacity: 0;
      animation: fadeIn 0.6s ease 0.5s forwards;
    }

    @keyframes scaleUp {
      to {
        transform: scale(1);
      }
    }

    @keyframes fadeIn {
      to {
        opacity: 1;
      }
    }

    /* Fullscreen Overlay loader */
    #overlaySpinner {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      z-index: 9999;
      justify-content: center;
      align-items: center;
      display: none;
    }

    /* Spinner Container */
    .spinner {
      width: 40px;
      height: 40px;
      position: relative;
      animation: sk-rotate 2.0s infinite linear;
    }

    /* Dots */
    .dot1,
    .dot2 {
      width: 60%;
      height: 60%;
      display: inline-block;
      position: absolute;
      top: 0;
      background-color: #054b40;
      border-radius: 100%;
      animation: sk-bounce 2.0s infinite ease-in-out;
    }

    .dot2 {
      top: auto;
      bottom: 0;
      animation-delay: -1.0s;
    }

    /* Animations */
    @keyframes sk-rotate {
      100% {
        transform: rotate(360deg);
      }
    }

    @keyframes sk-bounce {

      0%,
      100% {
        transform: scale(0.0);
      }

      50% {
        transform: scale(1.0);
      }
    }
  </style>
  <link rel="icon" sizes="180x180" href="codez3.png" />
</head>

<body>

  <div id="overlaySpinner">
    <div class="spinner">
      <div class="dot1"></div>
      <div class="dot2"></div>
    </div>
  </div>

  <!-- Background Icons -->
  <div class="animated-icons">
    <div class="icon">🎮</div>
    <div class="icon">🧠</div>
    <div class="icon">👨‍💻</div>
    <div class="icon">📚</div>
    <div class="icon">💻</div>
    <div class="icon">🏆</div>
    <div class="icon">🧩</div>
  </div>

  <!-- Login Box -->
  <div class="login-box">
    <h2 style="text-decoration: none; color: #054b40; cursor: default; font-weight: 600;">Welcome to
      <?php echo $company_name; ?>!
    </h2>
    <?php if (isset($msg)): ?>
      <div class="error-msg" style="margin-bottom: 20px; text-align: center;"><?php echo $msg; ?></div>
    <?php endif; ?>
    <form id="loginForm" method="POST" action="">
      <div class="input-wrapper">
        <input type="text" id="email" name="email" placeholder="Email">
        <span class="tick" id="emailTick">✅</span>
      </div>
      <span id="emailError" class="error-msg"></span>

      <div class="input-wrapper">
        <input type="password" id="password" name="password" placeholder="Password">
        <span class="eye-toggle" id="togglePassword">
          <i class="fa-solid fa-eye"></i>
        </span>
      </div>
      <span id="passError" class="error-msg"></span>
      <a href="forgot_password.php" class="forgot-link"
        style="text-decoration: none; color: #054b40;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Forgot Password?</a>
      <br>
      <button type="submit" class="btn btn-default">Login</button>
      <div class="text-center">
        <br>
        <span class="text-gray-400 me-2">Don't have an account? </span>
        <a href="signup.php" style="text-decoration: none; color: #054b40; cursor: default;"><B>Sign up as
            Student</B></a>
        <br>
        <span class="text-gray-400 me-2">Want to teach? </span>
        <a href="tutor_register.php" style="text-decoration: none; color: #054b40; cursor: default;"><B>Register as
            Tutor</B></a>
      </div>
    </form>
  </div>

  <script>
    $(document).ready(function () {
      // Email regex for basic validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      // 👁️ Password show/hide toggle
      $('#togglePassword').on('click', function () {
        const passwordInput = $('#password');
        const icon = $(this).find('i');

        if (passwordInput.attr('type') === 'password') {
          passwordInput.attr('type', 'text');
          icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
          passwordInput.attr('type', 'password');
          icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
      });

      // ✅ Login form submission
      $('#loginForm').submit(function (e) {
        const email = $('#email').val().trim();
        const pass = $('#password').val().trim();
        let valid = true;

        $('#emailError').text('');
        $('#passError').text('');

        if (email === '') {
          $('#emailError').text('Email is required.');
          valid = false;
        } else if (!emailRegex.test(email)) {
          $('#emailError').text('Please enter a valid email address.');
          valid = false;
        }

        if (pass === '') {
          $('#passError').text('Password is required.');
          valid = false;
        }

        // ✅ If valid, show spinner and submit
        if (valid) {
          $('#overlaySpinner').css('display', 'flex');

          // Form will submit normally, PHP handles login
          return true;
        } else {
          e.preventDefault();
          return false;
        }
      });
    });
  </script>

</body>

</html>