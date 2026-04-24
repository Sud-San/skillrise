<!-- login-pin -->

<?php
session_start();
include_once("connection.php");

// Redirect if email missing
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

$email = $_SESSION['reset_email'];
$alert = "";

// Mask email
function maskEmail($email)
{
    $parts = explode("@", $email);
    return substr($parts[0], 0, 1) . str_repeat("*", strlen($parts[0]) - 1) . "@" . $parts[1];
}

/* -------------------- VERIFY OTP -------------------- */
if (isset($_POST['verify_otp'])) {

    $enteredOtp = implode("", $_POST['pin_code']);
    $sessionOtp = $_SESSION['otp'];
    $expire = $_SESSION['otp_expire'];

    if (time() > $expire) {
        // Expired OTP
        $alert = "
        <script>
            Swal.fire({
                icon: 'error',
                title: 'OTP Expired',
                text: 'Please resend OTP.'
            });
        </script>
        ";
    } elseif ($enteredOtp == $sessionOtp) {
        // Correct OTP
        $alert = "
        <script>
            Swal.fire({
                icon: 'success',
                title: 'OTP Verified!',
                text: 'Redirecting...',
                timer: 1500,
                timerProgressBar: true
            }).then(()=>{ 
                window.location.href='create_pass.php'; 
            });
        </script>
        ";
    } else {
        // Wrong OTP
        $alert = "
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Invalid OTP',
                text: 'Please try again.'
            });
        </script>
        ";
    }
}

/* -------------------- RESEND OTP -------------------- */
if (isset($_POST['resend_otp'])) {

    $otp = rand(100000, 999999);

    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expire'] = time() + 60;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "collegedekhoo.info@gmail.com";
        $mail->Password = "glhq ubkl hlqs uszl";
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        // split it
        list($user, $domain) = explode('@', $email);
        $safeEmail = "<span>{$user}</span><span>@{$domain}</span>";
        $mail->setFrom("collegedekhoo.info@gmail.com", "College Dekho Admin");
        $mail->addAddress($email);
        $mail->addEmbeddedImage(__DIR__ . '/img/CollegeDekhoLogo(Color).png', 'companylogo');
        $mail->isHTML(true);
        $mail->Subject = "Reset Password OTP Code";
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

        $alert = "
        <script>
            Swal.fire({
                icon: 'success',
                title: 'OTP Resent',
                text: 'Check your email.',
                timer: 2000
            });
        </script>
        ";
    } catch (Exception $e) {
        $alert = "
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Mail Error',
                text: 'Could not resend OTP.'
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
    <title>OTP Verification | <?php echo $company_name; ?></title>

    <link href="assets/css/vendor.min.css" rel="stylesheet" />
    <link href="assets/css/app.min.css" rel="stylesheet" />
    <link href="assets/css/icons.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="../SkillRise_logo1.png">
</head>

<body class="h-100">

    <div class="auth-bg d-flex min-vh-100">
        <div class="row g-0 justify-content-center w-100 m-4">
            <div class="col-xxl-3 col-lg-5 col-md-6">

                <a class="auth-brand d-flex justify-content-center mb-2">
                    <img src="assets/images/logo-dark.png" height="26">
                </a>

                <p class="text-center text-muted">Admin Panel</p>

                <div class="card text-center p-4" style="height: 490px;">

                    <h4 class="fw-semibold">Enter OTP</h4>
                    <p class="text-muted">
                        A 6-digit OTP was sent to <br>
                        <b><?= maskEmail($email); ?></b>
                    </p>

                    <!-- VERIFY OTP FORM -->
                    <form method="post" id="verifyForm">
                        <label class="form-label">Enter 6 Digit Code</label>

                        <div class="d-flex gap-2 justify-content-center mb-3">
                            <?php for ($i = 0; $i < 6; $i++): ?>
                                <input type="text" maxlength="1" name="pin_code[]"
                                    class="form-control text-center fs-4 otpBox" required>
                            <?php endfor; ?>
                        </div>

                        <button type="submit" name="verify_otp" class="btn btn-primary w-100 fw-semibold">
                            Verify OTP
                        </button>
                    </form>

                    <!-- RESEND OTP -->
                    <form method="post" class="mt-3 text-center">
                        <button id="resendBtn" type="submit" name="resend_otp" class="btn btn-link text-danger"
                            disabled>
                            Resend OTP
                        </button>
                        <p id="timerText" class="text-muted mt-1" style="font-size:14px;"></p>
                    </form>

                </div>

            </div>
        </div>
    </div>

    <!-- REAL-TIME EXPIRY CHECK -->
    <script>
        let expiryTimestamp = <?= $_SESSION['otp_expire']; ?>;

        document.querySelector("button[name='verify_otp']").addEventListener("click", function (e) {
            let now = Math.floor(Date.now() / 1000);

            if (now > expiryTimestamp) {
                e.preventDefault();

                Swal.fire({
                    icon: 'error',
                    title: 'OTP Expired',
                    text: 'Please resend OTP.'
                });

                return;
            }
        });
    </script>

    <!-- TIMER -->
    <script>
        let expiryTS = <?= $_SESSION['otp_expire']; ?>;
        let now = Math.floor(Date.now() / 1000);
        let remaining = expiryTS - now;

        if (remaining < 0) remaining = 0;

        const timerText = document.getElementById("timerText");
        const resendBtn = document.getElementById("resendBtn");

        let countdown = setInterval(() => {
            timerText.innerHTML = " OTP Expried in " + remaining + " seconds";

            if (remaining <= 0) {
                clearInterval(countdown);
                timerText.innerHTML = "You can now resend OTP.";
                resendBtn.disabled = false;
            }

            remaining--;
        }, 1000);
    </script>

    <!-- Auto move input -->
    <script>
        let boxes = document.querySelectorAll(".otpBox");

        boxes.forEach((box, i) => {
            box.addEventListener("input", () => {
                if (box.value.length === 1 && i < 5) {
                    boxes[i + 1].focus();
                }
            });

            box.addEventListener("keydown", (e) => {
                if (e.key === "Backspace" && box.value === "" && i > 0) {
                    boxes[i - 1].focus();
                }
            });
        });
    </script>

    <?= $alert ?>

</body>

</html>