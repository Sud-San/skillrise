<?php
require 'connection.php';

/* ✅ AJAX EMAIL CHECK */
if (isset($_POST['email_check'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email_check']);

    $query = "SELECT user_id FROM user_tbl WHERE user_email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }
    exit; // stop page from loading HTML
}

?>
<head>
    <?php include 'headtag.php'; ?>

    <style>
        #blurOverlay {
            pointer-events: auto;
        }
    </style>
</head>

<!-- ✅ LOGIN POPUP -->
<div id="loginPopup"
     style="display:none; position:fixed; top:50%; left:50%; 
     transform:translate(-50%, -50%); background:white; padding:30px;
     border-radius:10px; box-shadow:0 0 25px rgba(0,0,0,0.2);
     width:340px; z-index:10000; text-align:center; font-family:'Segoe UI', sans-serif;">
	 
	 <!-- ❌ Close Button -->
  <span id="closePopup"
        style="position:absolute; top:10px; right:15px; font-size:20px; color:#333; 
               cursor:pointer; font-weight:bold;">&times;</span>

  <h2 style="color:green; margin-bottom:20px;">Welcome to <?php echo $company_name; ?>!</h2>

  <form id="popupLoginForm">
    <div style="position:relative; margin-bottom:15px;">
      <input type="text" id="popupEmail" name="email" placeholder="Email (e.g. user@gmail.com)"
             style="width:100%; padding:10px 35px 10px 10px; border:1px solid #ccc; border-radius:5px;">
      <span id="popupEmailTick" style="position:absolute; right:10px; top:50%; transform:translateY(-50%);
            color:green; font-size:18px; display:none;">✅</span>
    </div>
    <span id="popupEmailError" style="color:red; font-size:0.85rem; display:block; text-align:left;"></span>

    <div style="position:relative; margin-bottom:15px;">
      <input type="password" id="popupPassword" name="password" placeholder="Password"
             style="width:100%; padding:10px 35px 10px 10px; border:1px solid #ccc; border-radius:5px;">
      <span id="popupTogglePass" style="position:absolute; right:10px; top:50%; transform:translateY(-50%);
            color:#555; font-size:18px; cursor:pointer;"><i class="fa-solid fa-eye"></i></span>
    </div>
    <span id="popupPassError" style="color:red; font-size:0.85rem; display:block; text-align:left;"></span>

    <a href="forgot_password.php" style="display:block; color:green; text-decoration:none; font-size:0.9rem; text-align:left;">
      Forgot Password?
    </a><br>

    <button type="submit"
            style="width:100%; padding:10px; background:green; border:none; color:white;
                   border-radius:5px; font-weight:bold; cursor:pointer;">
      Login
    </button>

    <p style="margin-top:15px; font-size:0.9rem;">
      Don’t have an account? 
      <a href="signup.php" style="color:green; text-decoration:none; font-weight:bold;">Sign up</a>
    </p>

    <p id="popupMessage" style="margin-top:10px; font-size:0.9rem;"></p>
  </form>
</div>

<!-- ✅ Background Blur Overlay -->
<div id="blurOverlay" style="
  display:none; position:fixed; top:0; left:0; width:100%; height:100%;
  background:rgba(0,0,0,0.5); backdrop-filter:blur(5px); z-index:9999;">
</div>

<!-- jQuery + FontAwesome -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
$(document).ready(function() {
  const emailRegex = /^[^\s@]+@gmail\.com$/;

  // ✅ Check if logged in — show popup if not
  const currentPage = window.location.pathname.split("/").pop();
  if (currentPage === "login.php" || currentPage === "signup.php") {
    return;   // ❌ Stop popup script completely
  }
  
  // 👇 ADD YOUR CLOSE CODE HERE
    $('.login-btn, a[href="login.php"]').on('click', function() {
        $('#blurOverlay').hide();
        $('#loginPopup').hide();
    });

  // ✅ Show popup only if user not logged in
  $.get('check_login.php', function(res) {
    if (res.trim() === 'notloggedin') {
      setTimeout(() => {
        $('#blurOverlay').fadeIn(300);
        $('#loginPopup').fadeIn(400);
      }, 15000 + Math.random() * 5000);
    }
  });

  // ✅ Email validation
  $('#popupEmail').on('blur', function() {
    const email = $(this).val().trim();
    if (email !== '' && emailRegex.test(email)) {
      $.post(window.location.href, { email_check: email }, function(resp) {
        if (resp.trim() === 'exists') {
          $('#popupEmailTick').fadeIn();
          $('#popupEmailError').text('');
        } else {
          $('#popupEmailTick').fadeOut();
          //$('#popupEmailError').text('Email not found.');
        }
      });
    } else {
      $('#popupEmailTick').fadeOut();
      $('#popupEmailError').text('');
    }
  });

  // 👁️ Toggle password visibility
  $('#popupTogglePass').on('click', function() {
    const passField = $('#popupPassword');
    const icon = $(this).find('i');
    if (passField.attr('type') === 'password') {
      passField.attr('type', 'text');
      icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      passField.attr('type', 'password');
      icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });

  // ✅ Submit login form
  $('#popupLoginForm').submit(function(e) {
    e.preventDefault();
    const email = $('#popupEmail').val().trim();
    const pass = $('#popupPassword').val().trim();
    $('#popupMessage').text('');

    if (email === '' || pass === '') {
      $('#popupMessage').css('color', 'red').text('Please enter email and password.');
      return;
    }

    $.post('login_action.php', { email: email, password: pass }, function(response) {
      if (response.trim() === 'student') {
        $('#popupMessage').css('color', 'green').text('Login successful! Redirecting...');
        setTimeout(() => window.location.href = 'index.php', 1200);
      } else {
        $('#popupMessage').css('color', 'red').text(response);
      }
    });
  });
  
  $('#closePopup').on('click', function() {
  $('#loginPopup').fadeOut(300);
  $('#blurOverlay').fadeOut(300);
	});
});
</script>
