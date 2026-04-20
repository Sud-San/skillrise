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
    // Check student
    $safe_email = mysqli_real_escape_string($conn, $email);
    $student_sql = "SELECT * FROM user_tbl WHERE user_email = '$safe_email'";
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
          $details_sql = "SELECT * FROM user_details WHERE user_id = " . (int) $student_row['user_id'];
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?php echo $company_name; ?> Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="assets/css/login.css">
  <link rel="icon" sizes="180x180" href="skillrise.png" />
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
        <a href="signup.php" style="text-decoration: none; color: #054b40; cursor: default;"><B>Sign up here</B></a>
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
        e.preventDefault();

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
          this.submit();
        }
      });
    });
  </script>

</body>

</html>