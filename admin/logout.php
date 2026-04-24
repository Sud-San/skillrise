<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Disable caching for logout page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once("connection.php");

// Fully destroy the session
$_SESSION = array(); // Clear all session variables

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();
?>

<!DOCTYPE html>
<html lang="en" data-layout="">

<head>
    <meta charset="utf-8" />
    <title>Log Out | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../SkillRise_logo1.png">

    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- Website Theme overrides -->
    <link href="assets/css/website-theme.css" rel="stylesheet" type="text/css" />

    <!-- Redirect 2: Go to login page after 3 seconds -->
    <meta http-equiv="refresh" content="3;url=login.php">
</head>

<body class="h-100">

    <div class="auth-bg d-flex min-vh-100">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xxl-3 col-lg-5 col-md-6">
                <a href="index.php" class="auth-brand d-flex justify-content-center mb-2">
                    <img src="../SkillRise_logo1.png" alt="dark logo" height="26" class="logo-dark">
                    <img src="../SkillRise_logo1.png" alt="logo light" height="26" class="logo-light">
                </a>

                <p class="fw-semibold mb-4 text-center text-muted fs-15">SkillRise Academy Admin Panel</p>

                <div class="card overflow-hidden text-center p-xxl-4 p-3 mb-0">

                    <h4 class="fw-semibold mb-2 fs-18">You are Logged Out</h4>

                    <div class="text-center">
                        <div class="mt-4">
                            <div class="logout-checkmark">
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
                                    <circle class="path circle" fill="none" stroke="#4bd396" stroke-width="6"
                                        stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1"></circle>
                                    <polyline class="path check" fill="none" stroke="#4bd396" stroke-width="6"
                                        stroke-linecap="round" stroke-miterlimit="10"
                                        points="100.2,40.2 51.5,88.8 29.8,67.5 "></polyline>
                                </svg>
                            </div>
                        </div>

                        <h3 class="mt-2">See you again !</h3>

                        <p class="text-muted"> You are now successfully sign out. </p>
                    </div>

                    <div class="d-block mt-2">
                        <!-- <button class="btn btn-primary fw-semibold" type="button">Support Center</button> -->
                    </div>

                    <p class="text-muted fs-14 mt-3 mb-0">
                        Back to <a href="login.php" class="text-danger fw-semibold ms-1">Login !</a>
                    </p>

                </div>

                <p class="mt-4 text-center mb-0">
                    <script>document.write(new Date().getFullYear())</script> © SkillRise Academy
                </p>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>

</html>