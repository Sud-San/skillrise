<?php
include 'connection.php';

$success = "";
$error = "";

// Fetch states for dropdown
$states = [];
$state_query = "SELECT state_id, state_name FROM state_tbl WHERE state_status = 1 ORDER BY state_name";
$state_result = mysqli_query($conn, $state_query);
if ($state_result && mysqli_num_rows($state_result) > 0) {
  while ($row = mysqli_fetch_assoc($state_result)) {
    $states[] = $row;
  }
} else {
  $error = "Could not load states. Please contact administrator.";
}

// Process form
if (!empty($states) && $_SERVER["REQUEST_METHOD"] == "POST") {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $mobile = trim($_POST['mobile'] ?? '');
  $dob = trim($_POST['dob'] ?? '');
  $gender = strtolower(trim($_POST['gender'] ?? ''));
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';
  $state_id = $_POST['state'] ?? 0;
  $city_id = $_POST['city'] ?? 0;

  // Validation
  if (empty($name) || empty($email) || empty($mobile) || empty($dob) || empty($password) || empty($confirm) || empty($gender) || empty($state_id) || empty($city_id)) {
    $error = "Please fill all required fields.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email.";
  } elseif (strtotime($dob) >= strtotime('2016-01-01')) {
    $error = "Date of birth must be before 2016.";
  } elseif ($password !== $confirm) {
    $error = "Passwords do not match.";
  } elseif (strlen($password) < 6) {
    $error = "Password must be at least 6 characters.";
  } else {
    // Check if email exists
    $check_query = "SELECT user_id FROM user_tbl WHERE user_email = '" . mysqli_real_escape_string($conn, $email) . "'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
      $error = "Email already registered.";
    } else {
      // Start transaction
      mysqli_begin_transaction($conn);

      try {
        // Clean mobile number (remove non-numeric)
        $mobile_clean = preg_replace('/[^0-9]/', '', $mobile);

        // Ensure city_id fits in TINYINT (max 255)
        $city_id_safe = ($city_id > 255) ? 1 : intval($city_id);

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into user_tbl - Using plain text password as requested
        $query1 = "INSERT INTO user_tbl (
                            profile_pic, 
                            user_name, 
                            gender, 
                            city, 
                            user_email, 
                            user_password, 
                            mobile, 
                            user_status
                          ) VALUES (
                            'acc logo white.png', 
                            '" . mysqli_real_escape_string($conn, $name) . "', 
                            '" . mysqli_real_escape_string($conn, $gender) . "', 
                            " . $city_id_safe . ", 
                            '" . mysqli_real_escape_string($conn, $email) . "', 
                            '" . mysqli_real_escape_string($conn, $hashed_password) . "', 
                            " . intval($mobile_clean) . ", 
                            1
                          )";

        if (mysqli_query($conn, $query1)) {
          $user_id = mysqli_insert_id($conn);
          mysqli_commit($conn);
          $success = "Account created successfully! You can <a href='login.php'>login</a>.";
          $_POST = array(); // Clear form
        } else {
          mysqli_rollback($conn);
          $error = "Error creating account: " . mysqli_error($conn);
        }
      } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Error: " . $e->getMessage();
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $company_name; ?> - Student Registration</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/signup.css">
  <link rel="icon" sizes="180x180" href="skillrise.png" />
</head>

<body>
  <div class="background">
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>

    <span class="emoji" style="left:10%;">👩‍💻</span>
    <span class="emoji" style="left:30%;">🚀</span>
    <span class="emoji" style="left:50%;">🎓</span>
    <span class="emoji" style="left:70%;">💻</span>
    <span class="emoji" style="left:90%;">🔥</span>
  </div>

  <div class="container">
    <h2>Create Your Student Account</h2>

    <?php if (isset($error) && !empty($error)): ?>
      <div style="color:#dc3545; background:#f8d7da; padding:12px; border-radius:8px; margin-bottom:15px;">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($success) && !empty($success)): ?>
      <div style="color:#198754; background:#d1e7dd; padding:12px; border-radius:8px; margin-bottom:20px;">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
      </div>
    <?php else: ?>

      <?php if (empty($states)): ?>
        <div style="color:#dc3545; background:#f8d7da; padding:12px; border-radius:8px; margin-bottom:15px;">
          <i class="fas fa-exclamation-circle"></i> Could not load states. Please try again later.
        </div>
      <?php else: ?>

        <!-- Progress Bar -->
        <div class="progress-bar">
          <div class="step active" id="step1-indicator">
            1
            <div class="step-label">Personal</div>
          </div>
          <div class="step" id="step2-indicator">
            2
            <div class="step-label">Location</div>
          </div>
          <div class="step" id="step3-indicator">
            3
            <div class="step-label">Password</div>
          </div>
        </div>

        <form id="registerForm" method="post" action="">
          <!-- Step 1: Personal Info -->
          <div class="form-step active" id="step1">
            <input type="text" name="name" placeholder="Full Name *"
              value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            <span class="error-text" id="error-name"></span>

            <input type="email" name="email" id="emailInput" placeholder="Email Address *"
              value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            <span class="error-text" id="error-email"></span>

            <input type="tel" name="mobile" placeholder="Mobile Number *" pattern="[0-9]{10}"
              value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>">
            <span class="error-text" id="error-mobile"></span>

            <input type="date" class="" name="dob" max="<?php echo date('Y-m-d', strtotime('-6 years')); ?>"
              placeholder="Date of Birth *" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
            <span class="error-text" id="error-dob"></span>

            <div class="gender">
              <label><input type="radio" name="gender" value="male" <?php echo ($_POST['gender'] ?? '') == 'male' ? 'checked' : ''; ?>> Male</label>
              <label><input type="radio" name="gender" value="female" <?php echo ($_POST['gender'] ?? '') == 'female' ? 'checked' : ''; ?>> Female</label>
              <label><input type="radio" name="gender" value="other" <?php echo ($_POST['gender'] ?? '') == 'other' ? 'checked' : ''; ?>> Other</label>
            </div>
            <span class="error-text" id="error-gender"></span>

            <button type="button" id="btnNext1" class="btn btn-next" onclick="handleNextStep1()">Next →</button>
          </div>

          <!-- Step 2: Location -->
          <div class="form-step" id="step2">
            <select name="state" id="stateSelect">
              <option value="">-- Select State * --</option>
              <?php foreach ($states as $state): ?>
                <option value="<?php echo $state['state_id']; ?>" <?php echo ($_POST['state'] ?? '') == $state['state_id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($state['state_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <span class="error-text" id="error-state"></span>

            <div class="city-loader" id="cityLoader">
              <i class="fas fa-spinner fa-spin"></i> Loading cities...
            </div>

            <select name="city" id="citySelect" disabled>
              <option value="">-- Select City * --</option>
            </select>
            <span class="error-text" id="error-city"></span>

            <div class="form-actions">
              <button type="button" class="btn btn-prev" onclick="prevStep(1)">← Previous</button>
              <button type="button" class="btn btn-next" onclick="nextStep(3)">Next →</button>
            </div>
          </div>

          <!-- Step 3: Password & Terms -->
          <div class="form-step" id="step3">
            <div class="form-group">
              <input type="password" name="password" id="password" placeholder="Password (min 6 chars) *">
              <i class="fa-solid fa-eye toggle-password" toggle="#password"></i>
            </div>
            <span class="error-text" id="error-password"></span>

            <div class="form-group">
              <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password *">
              <i class="fa-solid fa-eye toggle-password" toggle="#confirmPassword"></i>
            </div>
            <span class="error-text" id="error-confirm_password"></span>

            <div class="terms">
              <label>
                <input type="checkbox" name="terms" />
                I agree to the <a href="terms.php">Terms & Privacy Policy</a>
              </label>
              <span class="error-text" id="error-terms"></span>
            </div>

            <div class="form-actions">
              <button type="button" class="btn btn-prev" onclick="prevStep(2)">← Previous</button>
              <button type="submit" class="btn btn-submit">Create Account</button>
            </div>
          </div>
        </form>

        <div class="login-link">
          Already have an account? <a href="login.php">Login here</a>
        </div>

        <div class="tutor-link">
          Want to teach? <a href="tutor_register.php">Register as Tutor</a>
        </div>

      <?php endif; // end states check ?>
    <?php endif; // end success check ?>
  </div>

  <script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function () {
      let currentStep = 1;

      // Real-time validation on blur
      document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('blur', function () {
          validateField(this);
        });
      });

      function validateField(input) {
        const name = input.name;
        if (!name) return true;

        const val = input.value.trim();
        const errorSpan = document.getElementById('error-' + name);
        if (!errorSpan) return true;

        let error = "";
        const isRequired = input.hasAttribute('placeholder') && input.placeholder.includes('*') || name === 'gender' || name === 'state' || name === 'city';

        if (isRequired && !val && name !== 'gender') {
          error = "This field is required.";
        } else if (name === 'gender') {
          const checked = document.querySelector('input[name="gender"]:checked');
          if (!checked) error = "Please select gender.";
        } else if (val) {
          if (name === 'email' && !isValidEmail(val)) {
            error = "Invalid email format.";
          } else if (name === 'mobile' && !/^[0-9]{10}$/.test(val)) {
            error = "10-digit number required.";
          } else if (name === 'dob') {
            const dobDate = new Date(val);
            if (dobDate >= new Date('<?php echo date('Y-m-d', strtotime('-6 years')); ?>')) {
              error = "Minimum age is 6 years.";
            }
          }
        }

        errorSpan.textContent = error;
        return error === "";
      }

      // Show specific step
      function showStep(stepNumber) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(step => {
          step.classList.remove('active');
        });

        // Show current step
        const stepElement = document.getElementById('step' + stepNumber);
        if (stepElement) {
          stepElement.classList.add('active');
        }

        // Update progress indicators
        document.querySelectorAll('.step').forEach((step, index) => {
          const stepNum = index + 1;
          if (stepNum < stepNumber) {
            step.classList.remove('active');
            step.classList.add('completed');
          } else if (stepNum === stepNumber) {
            step.classList.add('active');
            step.classList.remove('completed');
          } else {
            step.classList.remove('active', 'completed');
          }
        });

        currentStep = stepNumber;
      }

      // Next button
      window.handleNextStep1 = function () {
        if (!validateStep(1)) return;

        const emailInput = document.getElementById('emailInput');
        const nextBtn = document.getElementById('btnNext1');
        const email = emailInput.value.trim();
        const errorSpan = document.getElementById('error-email');

        nextBtn.disabled = true;
        nextBtn.textContent = "Checking...";

        // AJAX Email Check
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax/check_email.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
          nextBtn.disabled = false;
          nextBtn.textContent = "Next →";

          if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.exists) {
              errorSpan.textContent = "Email already registered.";
              Swal.fire('Error', 'This email is already registered.', 'error');
            } else {
              showStep(2);
            }
          } else {
            Swal.fire('Error', 'Server error checking email.', 'error');
          }
        };
        xhr.send('email=' + encodeURIComponent(email));
      };

      window.nextStep = function (step) {
        if (validateStep(currentStep)) {
          showStep(step);
        }
      }

      // Previous button
      window.prevStep = function (step) {
        showStep(step);
      }

      // Step validation
      function validateStep(step) {
        let isValid = true;

        // Step 1 validation
        if (step === 1) {
          const fields = ['name', 'email', 'mobile', 'dob'];
          let stepValid = true;
          fields.forEach(f => {
            const input = document.querySelector(`[name="${f}"]`);
            if (input && !validateField(input)) stepValid = false;
          });
          const genderInput = document.querySelector(`input[name="gender"]`);
          if (genderInput && !validateField(genderInput)) stepValid = false;
          isValid = stepValid;
        }

        // Step 2 validation
        if (step === 2) {
          const stateValid = validateField(document.getElementById('stateSelect'));
          const cityValid = validateField(document.getElementById('citySelect'));
          isValid = stateValid && cityValid;
        }

        // Step 3 validation
        if (step === 3) {
          const pass = document.getElementById('password');
          const conf = document.getElementById('confirmPassword');
          const terms = document.querySelector('input[name="terms"]');

          const passValid = validateField(pass);
          const confValid = validateField(conf);

          let termValid = true;
          if (!terms.checked) {
            document.getElementById('error-terms').textContent = "You must agree to terms.";
            termValid = false;
          } else {
            document.getElementById('error-terms').textContent = "";
          }

          if (passValid && confValid && pass.value !== conf.value) {
            document.getElementById('error-confirm_password').textContent = "Passwords do not match.";
            isValid = false;
          } else {
            isValid = passValid && confValid && termValid;
          }
        }

        return isValid;
      }

      // Email validation helper
      function isValidEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
      }

      // Load cities via AJAX
      function loadCities(stateId) {
        const citySelect = document.getElementById('citySelect');
        const cityLoader = document.getElementById('cityLoader');

        if (!citySelect || !cityLoader) return;

        if (!stateId) {
          citySelect.innerHTML = '<option value="">-- Select City * --</option>';
          citySelect.disabled = true;
          return;
        }

        // Show loader, hide dropdown
        cityLoader.style.display = 'block';
        citySelect.style.display = 'none';
        citySelect.disabled = true;

        // Simple AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax/get_cities.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
          if (cityLoader) cityLoader.style.display = 'none';
          if (citySelect) {
            citySelect.style.display = 'block';

            if (xhr.status === 200) {
              try {
                const response = JSON.parse(xhr.responseText);

                if (response.success) {
                  let options = '<option value="">-- Select City * --</option>';
                  response.cities.forEach(city => {
                    // Filter cities with ID <= 255 for TINYINT compatibility
                    if (city.city_id <= 255) {
                      options += `<option value="${city.city_id}">${city.city_name}</option>`;
                    }
                  });
                  citySelect.innerHTML = options;
                  citySelect.disabled = false;

                  // If no cities <= 255, show warning
                  if (options === '<option value="">-- Select City * --</option>') {
                    Swal.fire('Warning', 'No cities available for this state due to database constraints. Please select another state.', 'warning');
                    citySelect.disabled = true;
                  }
                } else {
                  citySelect.innerHTML = '<option value="">-- Select City * --</option>';
                  citySelect.disabled = true;
                  Swal.fire('Error', 'Could not load cities. Please try again.', 'error');
                }
              } catch (e) {
                citySelect.innerHTML = '<option value="">-- Select City * --</option>';
                citySelect.disabled = true;
              }
            } else {
              citySelect.innerHTML = '<option value="">-- Select City * --</option>';
              citySelect.disabled = true;
            }
          }
        };

        xhr.onerror = function () {
          if (cityLoader) cityLoader.style.display = 'none';
          if (citySelect) {
            citySelect.style.display = 'block';
            citySelect.innerHTML = '<option value="">-- Select City * --</option>';
            citySelect.disabled = true;
            Swal.fire('Error', 'Network error. Please check your connection.', 'error');
          }
        };

        xhr.send('state_id=' + encodeURIComponent(stateId));
      }

      // When state changes, load cities
      const stateSelect = document.getElementById('stateSelect');
      if (stateSelect) {
        stateSelect.addEventListener('change', function () {
          loadCities(this.value);
        });
      }

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

      // Form submission validation
      const registerForm = document.getElementById('registerForm');
      if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
          // Validate all steps
          for (let step = 1; step <= 3; step++) {
            if (!validateStep(step)) {
              e.preventDefault();
              showStep(step);
              return;
            }
          }

          // Additional validations
          const password = document.getElementById('password');
          const confirm = document.getElementById('confirmPassword');
          const terms = document.querySelector('input[name="terms"]');

          if (!password || !confirm) {
            e.preventDefault();
            Swal.fire('Error', 'Password fields not found.', 'error');
            return;
          }

          if (password.value !== confirm.value) {
            e.preventDefault();
            Swal.fire('Error', 'Passwords do not match.', 'error');
            showStep(3);
            return;
          }

          if (password.value.length < 6) {
            e.preventDefault();
            Swal.fire('Error', 'Password must be at least 6 characters.', 'error');
            showStep(3);
            return;
          }

          if (!terms || !terms.checked) {
            e.preventDefault();
            Swal.fire('Error', 'You must agree to the terms and conditions.', 'error');
            showStep(3);
            return;
          }

          // If all good, form submits
        });
      }

      // Make functions available globally
      window.nextStep = nextStep;
      window.prevStep = prevStep;
      window.loadCities = loadCities;

      // Initialize - if state already selected, load its cities
      if (stateSelect && stateSelect.value) {
        loadCities(stateSelect.value);
      }
    });
  </script>
</body>

</html>