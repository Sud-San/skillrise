<?php
session_start();
include_once 'connection.php';

// Redirect if session email not available
if (!isset($_SESSION['admin_email'])) {
    $_SESSION['admin_logged'] = false;
    header("Location: login.php");
    exit();
}

$_SESSION['admin_logged'] = false;
$alert = ""; // message holder

if (isset($_POST['lock-password'])) {

    $password = $_POST['lock-password'];
    $email = $_SESSION['admin_email'];


    // Simple password check
    $query = "SELECT * FROM admin_tbl WHERE admin_email='$email'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if (password_verify($password, $row['admin_password'])) {
        $alert = "success";
        $_SESSION['admin_logged'] = true;
    } else {
        $alert = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-layout="">

<head>
    <meta charset="utf-8" />
    <title>Lock Screen | <?php echo $company_name; ?></title>
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

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Website Theme overrides -->
    <link href="assets/css/website-theme.css" rel="stylesheet" type="text/css" />
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

                    <h4 class="fw-semibold mb-4 fs-20">Welcome Back</h4>

                    <div class="text-center">
                        <img src="assets/images/admin/<?php echo $_SESSION['admin_image']; ?>" alt=""
                            class="avatar-xl rounded-circle img-thumbnail">
                        <div class="mt-2 mb-3">
                            <h4 class="fw-semibold">Hi ! <?php echo $_SESSION['admin_name']; ?>.</h4>
                            <p class="mb-0 fst-italic text-muted">Enter your password to access the admin.</p>
                        </div>
                    </div>

                    <form action="" method="post" class="text-start mb-3">
                        <div class="mb-3">
                            <label class="form-label" for="lock-password">Enter Password</label>
                            <input type="password" id="lock-password" name="lock-password" class="form-control"
                                placeholder="Password">
                        </div>
                        <div class="mb-2 d-grid">
                            <button class="btn btn-primary fw-semibold" type="submit">Access to Screen</button>
                        </div>
                    </form>

                    <p class="text-muted fs-14 mb-0">
                        Not you? return <a href="login.php" class="fw-semibold text-danger ms-1">Login !</a>
                    </p>
                </div>

                <p class="mt-3 text-center mb-0">
                    <script>document.write(new Date().getFullYear())</script> © SkillRise Academy
                </p>
            </div>
        </div>
    </div>
    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if ($alert == "success") { ?>

        <script>
            Swal.fire({
                icon: 'success',
                title: 'Unlocked',
                text: 'Access granted. Redirecting...',
                showConfirmButton: false,
                timer: 1000,
                timerProgressBar: true
            }).then(() => {
                window.location.href = 'index.php';
            });
        </script>

    <?php } elseif ($alert == "error") { ?>

        <script>
            Swal.fire({
                icon: 'error',
                title: 'Incorrect Password',
                text: 'Please try again.',
                confirmButtonText: 'Retry'
            });
        </script>

    <?php } ?>
</body>

</html>