<!-- create_pass -->

<?php
session_start();
include_once "connection.php";

// If user enters create_pass.php directly
if (!isset($_SESSION['reset_email'])) {
    header("Location: recover_pass.php");
    exit();
}
// PHPMailer namespace
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$alert = "";

if (isset($_POST['new-password']) && isset($_POST['re-password'])) {

    $new_password = trim($_POST['new-password']);
    $re_password  = trim($_POST['re-password']);
    $email        = $_SESSION['reset_email'];

    // Basic validations
    if (empty($new_password) || empty($re_password)) {
        $alert = "<script>
            Swal.fire({
                icon:'error',
                title:'Empty Fields!',
                text:'Please enter password in both fields'
            });
        </script>";
    } elseif (strlen($new_password) < 6) {
        $alert = "<script>
            Swal.fire({
                icon:'error',
                title:'Weak Password',
                text:'Password must be at least 6 characters long.'
            });
        </script>";
    } elseif ($new_password !== $re_password) {
        $alert = "<script>
            Swal.fire({
                icon:'error',
                title:'Password Mismatch',
                text:'Both passwords must match!'
            });
        </script>";
    } else {
        // HASH the new password
        $plain = $new_password;


        $query = "UPDATE admin_tbl SET admin_password='$plain' WHERE admin_email='$email'";
        $run   = mysqli_query($conn, $query);

        if ($run) {

            unset($_SESSION['reset_email']);
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expire']);
            try {
                $mail->isSMTP();
                $mail->Host       = "smtp.gmail.com";
                $mail->SMTPAuth   = true;
                $mail->Username   = "collegedekhoo.info@gmail.com";
                $mail->Password   = "glhq ubkl hlqs uszl";
                $mail->SMTPSecure = "tls";
                $mail->Port       = 587;

                $mail->setFrom("collegedekhoo.info@gmail.com", "College Dekho Admin");
                $mail->addAddress($email);
                $mail->Subject = "Password Updated Successfully";
                $mail->Body    = "Your password has been updated successfully.";

                $mail->send();
            } catch (Exception $e) {
                // Handle email sending error if needed
            }
            $alert = "<script>
                Swal.fire({
                    icon:'success',
                    title:'Password Updated!',
                    text:'Your new password has been created successfully.',
                    timer:3000,
                    timerProgressBar:true
                }).then(() => {
                    window.location.href='login.php';
                });
            </script>";
        } else {
            $alert = "<script>
                Swal.fire({
                    icon:'error',
                    title:'Database Error',
                    text:'Could not update password. Try again later.'
                });
            </script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-layout="">

<head>
    <meta charset="utf-8" />
    <title>Reset Password | <?php echo $company_name; ?></title>

    <link rel="stylesheet" href="assets/css/vendor.min.css" />
    <link rel="stylesheet" href="assets/css/app.min.css" />
    <link rel="stylesheet" href="assets/css/icons.min.css" />

    <!-- Website Theme overrides -->
    <link href="assets/css/website-theme.css" rel="stylesheet" type="text/css" />

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="h-100">

    <?= $alert ?>

    <div class="auth-bg d-flex min-vh-100">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xxl-3 col-lg-5 col-md-6">

                <a href="index.php" class="auth-brand d-flex justify-content-center mb-2">
                    <img src="../SkillRise_logo1.png" height="26">
                </a>

                <p class="fw-semibold mb-4 text-center text-muted fs-15">SkillRise Academy Admin Panel</p>

                <div class="card overflow-hidden text-center p-xxl-4 p-3 mb-0">

                    <h4 class="fw-semibold mb-2 fs-20">Create New Password</h4>
                    <p class="text-muted mb-3">Please create your new password.</p>

                    <form action="" method="post" class="text-start mb-3">

                        <div class="mb-3">
                            <label class="form-label">Create New Password</label>
                            <input type="password" name="new-password" class="form-control" placeholder="New Password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Re-enter Password</label>
                            <input type="password" name="re-password" class="form-control" placeholder="Re-enter Password" required>
                        </div>

                        <div class="mb-2 d-grid">
                            <button class="btn btn-primary fw-semibold" type="submit">Create Password</button>
                        </div>
                    </form>

                </div>

                <p class="mt-4 text-center mb-0">
                    <script>document.write(new Date().getFullYear())</script> © SkillRise Academy
                </p>

            </div>
        </div>
    </div>

</body>

</html>