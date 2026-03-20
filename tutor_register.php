<?php
include 'connection.php';

$success = "";
$error = "";

// Process form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  // Validation
  $errors = [];

  if (empty($name)) {
    $errors[] = "Name is required.";
  } elseif (strlen($name) < 2) {
    $errors[] = "Name must be at least 2 characters.";
  }

  if (empty($email)) {
    $errors[] = "Email is required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
  }

  if (empty($phone)) {
    $errors[] = "Phone number is required.";
  } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = "Phone number must be 10 digits.";
  }

  if (empty($password)) {
    $errors[] = "Password is required.";
  } elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters.";
  }

  if (empty($confirm)) {
    $errors[] = "Please confirm your password.";
  } elseif ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
  }

  if (!isset($_POST['terms'])) {
    $errors[] = "You must agree to the terms and conditions.";
  }

  if (empty($errors)) {
    // Check if email exists
    $check_query = "SELECT tutor_id FROM tutor_tbl WHERE tutor_email = '" . mysqli_real_escape_string($conn, $email) . "'";

    $check_result = mysqli_query($conn, $check_query);
    if (!$check_result) {
      $error = "Database error checking email.";
    } elseif (mysqli_num_rows($check_result) > 0) {
      $error = "Email already registered.";
    } else {
      // Start transaction
      mysqli_begin_transaction($conn);

      try {
        // Insert into tutor_tbl
        $query = "INSERT INTO tutor_tbl (
                            tutor_name, 
                            tutor_email, 
                            tutor_phone, 
                            password, 
                            tutor_status,
                            verification_status
                          ) VALUES (
                            '" . mysqli_real_escape_string($conn, $name) . "', 
                            '" . mysqli_real_escape_string($conn, $email) . "', 
                            '" . mysqli_real_escape_string($conn, $phone) . "', 
                            '" . mysqli_real_escape_string($conn, $password) . "', 
                            0, 
                            'pending'
                          )";

        if (mysqli_query($conn, $query)) {
          $tutor_id = mysqli_insert_id($conn);

          // Insert into tutor_profile_tbl
          $profile_query = "INSERT INTO tutor_profile_tbl (tutor_id) 
                                     VALUES (" . intval($tutor_id) . "";

          if (mysqli_query($conn, $profile_query)) {
            mysqli_commit($conn);
            $success = "Registration successful! Your account is pending admin approval. You'll be notified via email once approved.";
            $_POST = array();
          } else {
            mysqli_rollback($conn);
            $error = "Error creating profile. Please try again.";
          }
        } else {
          mysqli_rollback($conn);
          $error = "Error creating account. Please try again.";
        }
      } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Error: " . $e->getMessage();
      }
    }
  } else {
    $error = implode("<br>", $errors);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Codezy - Tutor Registration</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* KEEP SAME GREEN THEME AS STUDENT SIGNUP */
    .emoji {
      position: absolute;
      bottom: -50px;
      font-size: 2rem;
      animation: floatEmoji 10s infinite ease-in-out;
    }

    @keyframes floatEmoji {
      0% {
        transform: translateY(0) scale(1);
        opacity: 0;
      }

      50% {
        opacity: 1;
        transform: translateY(-50vh) scale(1.2);
      }

      100% {
        transform: translateY(-100vh) scale(0.8);
        opacity: 0;
      }
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #d4f9d4, #a6e3b8);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow-x: hidden;
      overflow-y: auto;
      padding: 20px;
    }

    .background {
      position: absolute;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 0;
    }

    .circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(0, 128, 0, 0.1);
      animation: float 10s infinite linear;
    }

    .circle:nth-child(1) {
      width: 80px;
      height: 80px;
      left: 10%;
      animation-duration: 12s;
    }

    .circle:nth-child(2) {
      width: 100px;
      height: 100px;
      left: 70%;
      animation-duration: 15s;
    }

    .circle:nth-child(3) {
      width: 50px;
      height: 50px;
      left: 40%;
      animation-duration: 18s;
    }

    @keyframes float {
      0% {
        bottom: -150px;
        transform: translateX(0);
      }

      100% {
        bottom: 100vh;
        transform: translateX(50px);
      }
    }

    .container {
      position: relative;
      z-index: 1;
      background: #fff;
      padding: 30px 25px;
      border-radius: 15px;
      box-shadow: 0 10px 40px rgba(0, 128, 0, 0.2);
      width: 100%;
      font-size: 1.0rem;
      max-width: 450px;
    }

    .container h2 {
      color: #28a745;
      margin-bottom: 25px;
      text-align: center;
    }

    .container .subtitle {
      text-align: center;
      color: #666;
      margin-bottom: 25px;
      font-size: 0.95rem;
    }

    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="password"] {
      width: 100%;
      padding: 14px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      padding-right: 40px;
      font-size: 15px;
    }

    .form-group {
      position: relative;
      margin-bottom: 5px;
    }

    .form-group i {
      position: absolute;
      top: 50%;
      right: 15px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #777;
    }

    .terms {
      font-size: 14px;
      margin: 15px 0;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 8px;
    }

    .terms label {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .terms a {
      color: #28a745;
      text-decoration: none;
    }

    .terms a:hover {
      text-decoration: underline;
    }

    .btn {
      padding: 14px;
      border: none;
      border-radius: 8px;
      background-color: #28a745;
      color: white;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
      margin-top: 10px;
      width: 100%;
    }

    .btn:hover {
      background-color: #218838;
    }

    .login-link {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }

    .login-link a {
      color: green;
      text-decoration: none;
      font-weight: 600;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    .student-link {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
      color: #666;
    }

    .student-link a {
      color: #007bff;
      text-decoration: none;
      font-weight: bold;
    }

    .student-link a:hover {
      text-decoration: underline;
    }

    .message {
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .success {
      background: #d1e7dd;
      color: #0f5132;
      border-left: 4px solid #198754;
    }

    .error {
      background: #f8d7da;
      color: #842029;
      border-left: 4px solid #dc3545;
    }

    .info-box {
      background: #e7f3ff;
      border-left: 4px solid #0d6efd;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 13px;
      color: #0c5460;
    }

    .info-box h4 {
      margin-bottom: 5px;
      color: #0d6efd;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .info-box ul {
      margin-left: 20px;
      margin-top: 5px;
    }

    .info-box li {
      margin-bottom: 3px;
    }

    @media (max-width: 480px) {
      .container {
        padding: 20px 15px;
      }

      .container h2 {
        font-size: 1.5rem;
      }

      .info-box {
        font-size: 12px;
      }
    }
  </style>
  <link rel="icon" sizes="180x180" href="codez3.png" />
</head>

<body>
  <div class="background">
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>

    <span class="emoji" style="left:10%;">👨‍🏫</span>
    <span class="emoji" style="left:30%;">📚</span>
    <span class="emoji" style="left:50%;">💡</span>
    <span class="emoji" style="left:70%;">🎯</span>
    <span class="emoji" style="left:90%;">🌟</span>
  </div>

  <div class="container">
    <h2>Register as Tutor</h2>

    <p class="subtitle">Join Codezy as an instructor. Share your knowledge with students.</p>

    <?php if (isset($error) && !empty($error)): ?>
      <div class="message error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($success) && !empty($success)): ?>
      <div class="message success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
      </div>
    <?php else: ?>

      <div class="info-box">
        <h4><i class="fas fa-info-circle"></i> Registration Process</h4>
        <ul>
          <li>Fill basic details now, complete profile later</li>
          <li>Admin approval required before teaching</li>
          <li>You'll be notified via email when approved</li>
          <li>After approval, you can add courses and profile details</li>
        </ul>
      </div>

      <form id="tutorRegisterForm" method="post" action="">
        <input type="text" name="name" placeholder="Full Name *" minlength="2"
          value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">

        <input type="email" name="email" placeholder="Email Address *"
          value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

        <input type="tel" name="phone" placeholder="Phone Number (10 digits) *" pattern="[0-9]{10}"
          value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">

        <div class="form-group">
          <input type="password" name="password" id="password" placeholder="Password (min 6 characters) *" minlength="6">
          <i class="fa-solid fa-eye toggle-password" toggle="#password"></i>
        </div>

        <div class="form-group">
          <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password *">
          <i class="fa-solid fa-eye toggle-password" toggle="#confirmPassword"></i>
        </div>

        <div class="terms">
          <label>
            <input type="checkbox" name="terms" />
            I agree to the <a href="terms.php" target="_blank">Terms & Privacy Policy</a>
          </label>
        </div>

        <button type="submit" class="btn">
          <i class="fas fa-user-plus me-2"></i> Register as Tutor
        </button>
      </form>

      <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
      </div>

      <div class="student-link">
        Want to learn? <a href="signup.php">Register as Student</a>
      </div>

    <?php endif; ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Password visibility toggle
      document.querySelectorAll('.toggle-password').forEach(function (icon) {
        icon.addEventListener('click', function () {
          const toggleAttr = this.getAttribute('toggle');
          if (!toggleAttr) return;

          const input = document.querySelector(toggleAttr);
          if (!input) return;

          if (input.type === 'password') {
            input.type = 'text';
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
          } else {
            input.type = 'password';
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
          }
        });
      });

      // Real-time password validation
      const passwordInput = document.getElementById('password');
      const confirmInput = document.getElementById('confirmPassword');

      function validatePasswords() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;

        if (password && confirm && password !== confirm) {
          confirmInput.style.borderColor = '#dc3545';
        } else {
          confirmInput.style.borderColor = '';
        }

        if (password.length > 0 && password.length < 6) {
          passwordInput.style.borderColor = '#dc3545';
        } else if (password.length >= 6) {
          passwordInput.style.borderColor = '#28a745';
        }
      }

      if (passwordInput) {
        passwordInput.addEventListener('input', validatePasswords);
      }

      if (confirmInput) {
        confirmInput.addEventListener('input', validatePasswords);
      }

      // Real-time phone validation
      const phoneInput = document.querySelector('input[name="phone"]');
      if (phoneInput) {
        phoneInput.addEventListener('input', function () {
          this.value = this.value.replace(/[^0-9]/g, '');
          if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
          }
        });
      }

      // Form validation
      const form = document.getElementById('tutorRegisterForm');
      if (form) {
        form.addEventListener('submit', function (e) {
          let isValid = true;
          const errors = [];

          // Name validation
          const name = this.name.value.trim();
          if (!name || name.length < 2) {
            errors.push('Name must be at least 2 characters.');
            isValid = false;
          }

          // Email validation
          const email = this.email.value.trim();
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!email || !emailRegex.test(email)) {
            errors.push('Please enter a valid email address.');
            isValid = false;
          }

          // Phone validation
          const phone = this.phone.value.trim();
          const phoneRegex = /^[0-9]{10}$/;
          if (!phone || !phoneRegex.test(phone)) {
            errors.push('Phone number must be exactly 10 digits.');
            isValid = false;
          }

          // Password validation
          const password = this.password.value;
          if (!password || password.length < 6) {
            errors.push('Password must be at least 6 characters.');
            isValid = false;
          }

          // Confirm password
          const confirm = this.confirm_password.value;
          if (!confirm) {
            errors.push('Please confirm your password.');
            isValid = false;
          } else if (password !== confirm) {
            errors.push('Passwords do not match.');
            isValid = false;
          }

          // Terms validation
          const terms = this.terms;
          if (!terms.checked) {
            errors.push('You must agree to the terms and conditions.');
            isValid = false;
          }

          if (!isValid) {
            e.preventDefault();

            // Show all errors in one alert
            if (errors.length > 0) {
              Swal.fire('Validation Errors', errors.join('\n'), 'error');
            }

            return false;
          }

          // Add loading state
          const submitBtn = this.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Registering...';
          submitBtn.disabled = true;

          // Re-enable after 5 seconds if something goes wrong
          setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
          }, 5000);

          return true;
        });
      }
    });
  </script>
</body>

</html>