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

      try {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
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
                            '" . mysqli_real_escape_string($conn, $hashed_password) . "', 
                            0, 
                            'pending'
                          )";
        if (mysqli_query($conn, $query)) {
          $success = "Registration successful! Your account is pending admin approval. You'll be notified via email once approved.";
        } else {
          $error = "Error creating account. Please try again.";
        }
      } catch (Exception $e) {
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
  <title>
    <?php echo $company_name; ?> - Tutor Registration
  </title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/tutor_register.css">
  <link rel="icon" sizes="180x180" href="skillrise.png" />
  <style>
    .error-msg {
      color: #dc3545;
      font-size: 13px;
      margin-top: 2px;
      margin-bottom: 12px;
      display: none;
      text-align: left;
      width: 100%;
      font-weight: 500;
    }
    .input-error {
      border-color: #dc3545 !important;
      background-color: #fff8f8 !important;
    }
  </style>
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

    <p class="subtitle">Join
      <?php echo $company_name; ?> as an instructor. Share your knowledge with students.
    </p>

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
        <input type="text" name="name" id="name" placeholder="Full Name *"
          value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        <div class="error-msg" id="name_error"></div>

        <input type="email" name="email" id="email" placeholder="Email Address *"
          value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        <div class="error-msg" id="email_error"></div>

        <input type="tel" name="phone" id="phone" placeholder="Phone Number (10 digits) *"
          value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
        <div class="error-msg" id="phone_error"></div>

        <div class="form-group mb-0">
          <input type="password" name="password" id="password" placeholder="Password (min 6 characters) *">
          <i class="fa-solid fa-eye toggle-password" toggle="#password"></i>
        </div>
        <div class="error-msg" id="password_error"></div>

        <div class="form-group mb-0 mt-2">
          <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password *">
          <i class="fa-solid fa-eye toggle-password" toggle="#confirmPassword"></i>
        </div>
        <div class="error-msg" id="confirm_error"></div>

        <div class="terms">
          <label>
            <input type="checkbox" name="terms" id="termsCheckbox" />
            I agree to the <a href="terms.php" target="_blank">Terms & Privacy Policy</a>
          </label>
        </div>
        <div class="error-msg" id="terms_error"></div>

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
          
          // Clear previous errors
          document.querySelectorAll('.error-msg').forEach(el => {
              el.style.display = 'none';
              el.textContent = '';
          });
          document.querySelectorAll('.input-error').forEach(el => {
              el.classList.remove('input-error');
          });

          // Helper function
          function showError(id, msg, inputId) {
              const errEl = document.getElementById(id);
              if (errEl) {
                  errEl.textContent = msg;
                  errEl.style.display = 'block';
              }
              if (inputId) {
                  const inputEl = document.getElementById(inputId);
                  if(inputEl) inputEl.classList.add('input-error');
              }
              isValid = false;
          }

          // Name validation
          const name = this.name.value.trim();
          if (!name || name.length < 2) {
            showError('name_error', 'Name must be at least 2 characters.', 'name');
          }

          // Email validation
          const email = this.email.value.trim();
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!email || !emailRegex.test(email)) {
            showError('email_error', 'Please enter a valid email address.', 'email');
          }

          // Phone validation
          const phone = this.phone.value.trim();
          const phoneRegex = /^[0-9]{10}$/;
          if (!phone || !phoneRegex.test(phone)) {
            showError('phone_error', 'Phone number must be exactly 10 digits.', 'phone');
          }

          // Password validation
          const password = this.password.value;
          if (!password || password.length < 6) {
            showError('password_error', 'Password must be at least 6 characters.', 'password');
          }

          // Confirm password
          const confirm = this.confirm_password.value;
          if (!confirm) {
            showError('confirm_error', 'Please confirm your password.', 'confirmPassword');
          } else if (password !== confirm) {
            showError('confirm_error', 'Passwords do not match.', 'confirmPassword');
          }

          // Terms validation
          const terms = document.getElementById('termsCheckbox');
          if (!terms.checked) {
            showError('terms_error', 'You must agree to the terms and conditions.');
          }

          if (!isValid) {
            e.preventDefault();
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