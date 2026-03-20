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
    $gender = strtolower(trim($_POST['gender'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $state_id = $_POST['state'] ?? 0;
    $city_id = $_POST['city'] ?? 0;

    // Validation
    if (empty($name) || empty($email) || empty($mobile) || empty($password) || empty($confirm) || empty($gender) || empty($state_id) || empty($city_id)) {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email.";
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
                            '" . mysqli_real_escape_string($conn, $password) . "', 
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
  <style>
    /* KEEP ALL ORIGINAL STYLES */
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
      min-height: 90vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .container h2 {
      color: #28a745;
      margin-bottom: 30px;
      text-align: center;
    }

    /* Progress Bar Styles */
    .progress-bar {
      display: flex;
      justify-content: space-between;
      margin-bottom: 25px;
      position: relative;
    }

    .progress-bar::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 2px;
      background: #e0e0e0;
      z-index: 1;
      transform: translateY(-50%);
    }

    .step {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background: #fff;
      border: 2px solid #e0e0e0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: #999;
      position: relative;
      z-index: 2;
      transition: all 0.3s;
      font-size: 14px;
    }

    .step.active {
      border-color: #28a745;
      background: #28a745;
      color: white;
    }

    .step.completed {
      border-color: #28a745;
      background: #28a745;
      color: white;
    }

    .step-label {
      position: absolute;
      top: 40px;
      font-size: 11px;
      color: #666;
      white-space: nowrap;
      left: 50%;
      transform: translateX(-50%);
      width: max-content;
    }

    /* Form Steps */
    .form-step {
      display: none;
    }

    .form-step.active {
      display: block;
      animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    /* Form Styles */
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="tel"],
    select {
      width: 100%;
      padding: 14px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      padding-right: 40px;
      font-size: 15px;
    }

    select {
      cursor: pointer;
    }

    .gender {
      display: flex;
      justify-content: space-around;
      margin: 10px 0;
      flex-wrap: wrap;
    }

    .gender label {
      margin: 5px;
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
    }

    .btn:hover {
      background-color: #218838;
    }

    .btn-prev {
      background-color: #6c757d;
      width: 48%;
    }

    .btn-prev:hover {
      background-color: #5a6268;
    }

    .btn-next {
      width: 48%;
    }

    .btn-submit {
      width: 100%;
    }

    .login-link {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }

    .login-link a {
      color: green;
      text-decoration: none;
    }

    .tutor-link {
      text-align: center;
      margin-top: 10px;
      font-size: 14px;
      color: #666;
    }

    .tutor-link a {
      color: #007bff;
      text-decoration: none;
      font-weight: bold;
    }

    .tutor-link a:hover {
      text-decoration: underline;
    }

    .form-actions {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-top: 15px;
    }

    .city-loader {
      display: none;
      text-align: center;
      padding: 5px;
      color: #666;
      font-size: 13px;
    }
  </style>
  <link rel="icon" sizes="180x180" href="codez3.png" />
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
        <input type="text" name="name" placeholder="Full Name *" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        
        <input type="email" name="email" placeholder="Email Address *" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        
        <input type="tel" name="mobile" placeholder="Mobile Number *" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>">

        <div class="gender">
          <label><input type="radio" name="gender" value="male" <?php echo ($_POST['gender'] ?? '') == 'male' ? 'checked' : ''; ?>> Male</label>
          <label><input type="radio" name="gender" value="female" <?php echo ($_POST['gender'] ?? '') == 'female' ? 'checked' : ''; ?>> Female</label>
          <label><input type="radio" name="gender" value="other" <?php echo ($_POST['gender'] ?? '') == 'other' ? 'checked' : ''; ?>> Other</label>
        </div>
        
        <button type="button" class="btn btn-next" onclick="nextStep(2)">Next →</button>
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

        <div class="city-loader" id="cityLoader">
          <i class="fas fa-spinner fa-spin"></i> Loading cities...
        </div>
        
        <select name="city" id="citySelect" disabled>
          <option value="">-- Select City * --</option>
        </select>
        
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

        <div class="form-group">
          <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password *">
          <i class="fa-solid fa-eye toggle-password" toggle="#confirmPassword"></i>
        </div>

        <div class="terms">
          <label>
            <input type="checkbox" name="terms" />
            I agree to the <a href="terms.php">Terms & Privacy Policy</a>
          </label>
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
    document.addEventListener('DOMContentLoaded', function() {
      let currentStep = 1;

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
      function nextStep(step) {
        if (validateStep(currentStep)) {
          showStep(step);
        }
      }

      // Previous button
      function prevStep(step) {
        showStep(step);
      }

      // Step validation
      function validateStep(step) {
        let isValid = true;
        
        // Step 1 validation
        if (step === 1) {
          const name = document.querySelector('input[name="name"]');
          const email = document.querySelector('input[name="email"]');
          const mobile = document.querySelector('input[name="mobile"]');
          const gender = document.querySelectorAll('input[name="gender"]');
          
          if (!name || !name.value.trim()) {
            Swal.fire('Error', 'Please enter your name.', 'error');
            isValid = false;
          } else if (!email || !email.value.trim() || !isValidEmail(email.value.trim())) {
            Swal.fire('Error', 'Please enter a valid email.', 'error');
            isValid = false;
          } else if (!mobile || !mobile.value.trim() || !/^[0-9]{10}$/.test(mobile.value.trim())) {
            Swal.fire('Error', 'Please enter a valid 10-digit mobile number.', 'error');
            isValid = false;
          } else if (!gender || ![...gender].some(g => g.checked)) {
            Swal.fire('Error', 'Please select your gender.', 'error');
            isValid = false;
          }
        }
        
        // Step 2 validation
        if (step === 2) {
          const state = document.getElementById('stateSelect');
          const city = document.getElementById('citySelect');
          
          if (!state || !state.value) {
            Swal.fire('Error', 'Please select your state.', 'error');
            isValid = false;
          } else if (!city || !city.value || city.disabled) {
            Swal.fire('Error', 'Please select your city.', 'error');
            isValid = false;
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
        
        xhr.onload = function() {
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
        
        xhr.onerror = function() {
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
        stateSelect.addEventListener('change', function() {
          loadCities(this.value);
        });
      }

      // Password visibility toggle
      document.querySelectorAll('.toggle-password').forEach(function(icon) {
        icon.addEventListener('click', function() {
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
        registerForm.addEventListener('submit', function(e) {
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