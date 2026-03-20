<!-- Recover_pass -->
<?php
session_start();
include_once 'connection.php';

// PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';

// // Include PHPMailer files
// require 'email/PHPMailer.php';
// require 'email/SMTP.php';
// require 'email/Exception.php';

// To store alert JS
$alert = "";

if (isset($_POST['email'])) {

    $email = $_POST['email'];

    // Check email in DB
    $query = "SELECT * FROM admin_tbl WHERE admin_email='$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {

        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expire'] = time() + 60; // valid for 1 minute

        // Create Mail Object
        $mail = new PHPMailer(true);

        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            //$mail->Username = "rampariyaprince@gmail.com";
            $mail->Username = "collegedekhoo.info@gmail.com";
            $mail->Password = "glhq ubkl hlqs uszl";
            //$mail->Password = "wkuqikxbhrqhghpw";
            $mail->SMTPSecure = "tls";
            $mail->Port = 587;

            // split it
            list($user, $domain) = explode('@', $email);
            $safeEmail = "<span>{$user}</span><span>@{$domain}</span>";

            // Sender and receiver
            $mail->setFrom("rampariyaprince@gmail.com", "College Dekho Admin");
            $mail->addAddress($email);
            $mail->addEmbeddedImage(__DIR__ . '/img/CollegeDekhoLogo(Color).png', 'companylogo');
            $mail->isHTML(true);
            // Content
            $mail->Subject = "Password Reset OTP Code";
            $mail->Body = '
                <div style="font-family: Arial; padding: 15px; border:1px solid #ddd; border-radius:5px;">
                    <div style="text-align:center;">
                        <img src="cid:companylogo" width="300" style="margin-bottom:10px;">
                    </div>
                    <h2 style="color:#333;">Dear <span style="text-decoration:none;color:#000;">' . $safeEmail . '</span>,</h2>
                    <p>Your verification code is: <b>' . $otp . '</b>. This code will expire in 1 minute.</p>
                    <p>Regards,<br><b>College Dekho Admin</b></p>
                </div>
                ';

            $mail->send();

            // Success Alert + Redirect
            $alert = "
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Sent Successfully!',
                        text: 'Redirecting...',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = 'login-pin.php';
                    });
                </script>
            ";
        } catch (Exception $e) {

            $alert = "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Mail Error',
                        text: 'Error sending email: " . addslashes($mail->ErrorInfo) . "'
                    });
                </script>
            ";
        }
    } else {

        // Email not found alert
        $alert = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Email Not Found',
                    text: 'This email does not exist.',
                    timer: 3000
                });
            </script>
        ";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Recover Password | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Theme Config -->
    <script src="assets/js/config.js"></script>

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- SweetAlert2 (LOAD HERE – FIXED!) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Website Theme overrides -->
    <link href="assets/css/website-theme.css" rel="stylesheet" type="text/css" />

</head>

<body class="h-100">

    <!-- SHOW ALERT IF EXISTS -->
    <?php if (!empty($alert)) echo $alert; ?>

    <div class="auth-bg d-flex min-vh-100">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xxl-3 col-lg-5 col-md-6">

                <a href="index.php" class="auth-brand d-flex justify-content-center mb-2">
                    <img src="../SkillRise_logo1.png" alt="dark logo" height="26" class="logo-dark">
                    <img src="../SkillRise_logo1.png" alt="logo light" height="26" class="logo-light">
                </a>

                <p class="fw-semibold mb-4 text-center text-muted fs-15">
                    SkillRise Academy Admin Panel
                </p>

                <div class="card overflow-hidden text-center p-xxl-4 p-3 mb-0">

                    <h4 class="fw-semibold mb-3 fs-18">Reset Your Password</h4>

                    <p class="text-muted mb-4">
                        Enter your email address and we will send you instructions to reset your password.
                    </p>

                    <form action="" method="post" class="text-start mb-3">
                        <div class="mb-3">
                            <label class="form-label" for="example-email">Email</label>
                            <input type="email" id="example-email" name="email" class="form-control"
                                placeholder="Enter your email" required>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary fw-semibold" type="submit">Reset Password</button>
                        </div>
                    </form>

                    <p class="text-muted fs-14 mb-0">
                        Back To <a href="login.php" class="fw-semibold text-danger ms-1">Login</a>
                    </p>

                </div>

                 <p class="mt-4 text-center mb-0">
                    <script>document.write(new Date().getFullYear())</script> © SkillRise Academy
                </p>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>

</body>

</html>