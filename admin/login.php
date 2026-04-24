<?php
session_start();
include_once 'connection.php';

// Initialize flags for alerts
$login_error = '';
$account_deactivated = false;

// If already logged in, send to dashboard
if (!empty($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';


    // YOU SHOULD use prepared statements in real apps, but keeping your style for now
    $str = "SELECT * FROM admin_tbl WHERE admin_email='$email'";
    $result = mysqli_query($conn, $str);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_array($result);

        if (password_verify($password, $admin['admin_password'])) {

            if ($admin['admin_status'] != 1) {
                // Just set flag, JS will show alert later
                $account_deactivated = true;
            } else {
                $_SESSION['admin'] = $admin['admin_id'];
                $_SESSION['admin_email'] = $admin['admin_email'];
                $_SESSION['admin_name'] = $admin['admin_name'];
                $_SESSION['admin_image'] = $admin['admin_image'];
                $_SESSION['admin_logged'] = true;

                header('Location: index.php');
                exit();
            }
        } else {
            $login_error = 'Invalid email or password';
        }
    } else {
        $login_error = 'Invalid email or password';
    }


}
?>
<!DOCTYPE html>
<html lang="en" data-layout="">

<head>
    <meta charset="utf-8" />
    <title>Log In | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../SkillRise_logo1.png">

    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- Website Theme overrides -->
    <link href="assets/css/website-theme.css" rel="stylesheet" type="text/css" />
</head>

<body>

    <div class="auth-bg d-flex min-vh-100">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xxl-3 col-lg-5 col-md-6">
                <a href="index.php" class="auth-brand d-flex justify-content-center mb-2">
                    <img src="../SkillRise_logo1.png" alt="dark logo" height="26" class="logo-dark">
                    <img src="../SkillRise_logo1.png" alt="logo light" height="26" class="logo-light">
                </a>

                <p class="fw-semibold mb-4 text-center text-muted fs-15">SkillRise Academy Admin Panel</p>

                <div class="card overflow-hidden text-center p-xxl-4 p-3 mb-0">

                    <h4 class="fw-semibold mb-3 fs-18">Log in to your account</h4>

                    <form id="adminLoginForm" action="" method="post" class="text-start mb-3">
                        <div class="mb-3">
                            <label class="form-label" for="example-email">Email</label>
                            <input type="email" id="example-email" name="email" class="form-control"
                                placeholder="Enter your email">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="example-password">Password</label>
                            <input type="password" name="password" id="example-password" class="form-control"
                                placeholder="Enter your password">
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <div class="form-check">
                                <!-- <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                <label class="form-check-label" for="checkbox-signin">Remember me</label> -->
                            </div>

                            <a href="recover_pass.php" class="text-muted border-bottom border-dashed">
                                Forget Password
                            </a>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary fw-semibold" type="submit">Login</button>
                        </div>
                    </form>

                    <!-- <p class="text-muted fs-14 mb-0">
                        Don't have an account?
                        <a href="auth-register.html" class="fw-semibold text-danger ms-1">Sign Up !</a>
                    </p> -->

                </div>
                <p class="mt-4 text-center mb-0">
                    <script>
                        document.write(new Date().getFullYear())
                    </script>
                    © <?php echo $company_name; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <!-- SweetAlert2 JS (load AFTER page content to be safe) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (!empty($login_error)): ?>
        <script>
            Swal.fire({
                title: 'Login Failed',
                text: '<?php echo addslashes($login_error); ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>
    <?php endif; ?>

    <?php if ($account_deactivated): ?>
        <script>
            Swal.fire({
                title: 'Account Deactivated',
                text: 'Your account is deactivated.',
                icon: 'warning',
                confirmButtonText: 'OK'
            }).then((result) => {
                window.location = 'index.php';
            });
        </script>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('adminLoginForm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const email = document.getElementById('example-email').value.trim();
                    const password = document.getElementById('example-password').value.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    let errorMsg = '';
                    if (!email || !emailRegex.test(email)) {
                        errorMsg = 'Please enter a valid email address.';
                    } else if (!password) {
                        errorMsg = 'Please enter your password.';
                    }

                    if (errorMsg) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Validation Error',
                            text: errorMsg,
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    </script>

</body>

</html>