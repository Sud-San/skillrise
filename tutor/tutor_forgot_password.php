<!-- Tutor Forgot Password with OTP -->
<?php
session_start();
include_once 'connection.php';

// Load Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// To store alert JS
$alert = "";

// If already logged in, redirect to dashboard
if (!empty($_SESSION['tutor_logged']) && $_SESSION['tutor_logged'] === true) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['email'])) {

    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alert = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address.',
                    confirmButtonColor: '#10b981'
                });
            </script>
        ";
    } else {
        // Check email in DB using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT tutor_id, tutor_name, tutor_email FROM tutor_tbl WHERE tutor_email = ?");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $tutor = mysqli_fetch_assoc($result);

                // Generate 6-digit OTP
                $otp = rand(100000, 999999);
                $_SESSION['tutor_reset_email'] = $email;
                $_SESSION['tutor_otp'] = $otp;
                $_SESSION['tutor_otp_expire'] = time() + 300; // valid for 5 minutes

                // Create Mail Object
                $mail = new PHPMailer(true);

                try {
                    // SMTP settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = "codezy03@gmail.com"; // Replace with your email
                    $mail->Password = "bjzmtwtfnadwxqbt"; // Replace with your app password
                    $mail->SMTPSecure = "ssl";
                    $mail->Port = 465;

                    // Sender and receiver
                    $mail->setFrom("codezy03@gmail.com", "SkillRise Support");
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = "Password Reset OTP - SkillRise";
                    $mail->Body = '
                        <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;border:1px solid #e2e8f0;border-radius:10px;">
                            <div style="background:linear-gradient(135deg,#10b981,#065f46);padding:30px;text-align:center;border-radius:10px 10px 0 0;">
                                <h1 style="color:white;margin:0;font-size:24px;">Verification Code</h1>
                            </div>
                            <div style="background:#f9f9f9;padding:30px;border-radius:0 0 10px 10px;">
                                <h2 style="color:#333;font-size:18px;">Hello ' . htmlspecialchars($tutor['tutor_name']) . ',</h2>
                                <p style="color:#666;line-height:1.6;">We received a request to reset your password. Use the code below to proceed:</p>
                                <div style="text-align:center;margin:30px 0;">
                                    <div style="display:inline-block;background:white;padding:20px 40px;border-radius:10px;border:2px dashed #10b981;">
                                        <span style="font-size:32px;font-weight:bold;color:#10b981;letter-spacing:5px;">' . $otp . '</span>
                                    </div>
                                </div>
                                <p style="color:#666;line-height:1.6;"><strong>⏱️ This code is valid for 5 minutes.</strong></p>
                                <div style="margin-top:20px;padding:15px;background:#fff3cd;border-left:4px solid #ffc107;border-radius:5px;">
                                    <p style="margin:0;color:#856404;font-size:14px;">⚠️ If you did not request this, please ignore this email.</p>
                                </div>
                                <p style="margin-top:30px;color:#666;">Best regards,<br><strong style="color:#10b981;">SkillRise Team</strong></p>
                            </div>
                        </div>
                    ';

                    $mail->send();

                    // Success Alert + Redirect
                    $alert = "
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'OTP Sent Successfully!',
                                text: 'Check your email for the verification code.',
                                timer: 2000,
                                timerProgressBar: true,
                                confirmButtonColor: '#10b981'
                            }).then(() => {
                                window.location.href = 'tutor_verify_otp.php';
                            });
                        </script>
                    ";
                } catch (Exception $e) {
                    $alert = "
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Mail Error',
                text: " . json_encode('Error: ' . $e->getMessage()) . ",
                confirmButtonColor: '#10b981'
            });
        </script>
    ";
                }
            } else {
                // Email not found - don't reveal this for security
                $alert = "
                    <script>
                        Swal.fire({
                            icon: 'info',
                            title: 'Check Your Email',
                            text: 'If an account exists with this email, you will receive an OTP code.',
                            timer: 3000,
                            confirmButtonColor: '#10b981'
                        });
                    </script>
                ";
            }

            mysqli_stmt_close($stmt);
        } else {
            $alert = "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Database Error',
                        text: 'Please try again later.',
                        confirmButtonColor: '#10b981'
                    });
                </script>
            ";
        }
    }
}

$company_name = isset($company_name) ? $company_name : 'SkillRise';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password | <?php echo htmlspecialchars($company_name); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="shortcut icon" href="codez3.png">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --emerald: #10b981;
            --emerald-mid: #059669;
            --forest: #065f46;
            --forest-deep: #022c22;
            --mint-light: #ecfdf5;
            --white: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
        }

        html,
        body {
            height: 100%;
            min-height: 100vh;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(150deg, var(--forest-deep) 0%, #044533 38%, #047a55 70%, var(--emerald-mid) 100%);
            color: var(--text-primary);
            overflow: hidden;
            position: relative;
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.16;
            animation: drift 10s ease-in-out infinite alternate;
            pointer-events: none;
            z-index: 0;
        }

        .orb-1 {
            width: 550px;
            height: 550px;
            background: #10b981;
            top: -180px;
            left: -150px;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: #34d399;
            bottom: -120px;
            right: -120px;
            animation-delay: 3s;
        }

        @keyframes drift {
            from {
                transform: translate(0, 0) scale(1);
            }

            to {
                transform: translate(22px, 30px) scale(1.08);
            }
        }

        .noise {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .container {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 100vh;
            padding: 2rem 1.5rem;
        }

        .forgot-card {
            background: var(--white);
            border-radius: 26px;
            padding: 2.6rem 2.4rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 30px 80px rgba(2, 44, 34, .55), 0 4px 20px rgba(0, 0, 0, .12);
            border: 1px solid rgba(255, 255, 255, .07);
            animation: cardIn .9s cubic-bezier(.22, 1, .36, 1) .1s both;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(26px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 1.8rem;
        }

        .logo-mark {
            width: 41px;
            height: 41px;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--emerald) 0%, var(--forest) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(16, 185, 129, .38);
            flex-shrink: 0;
        }

        .logo-mark svg {
            width: 20px;
            height: 20px;
        }

        .logo-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
            letter-spacing: -0.01em;
        }

        .logo-tag {
            font-size: 9.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--emerald);
            margin-top: 2px;
        }

        .card-heading h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.15;
            letter-spacing: -0.025em;
            margin-bottom: 0.3rem;
        }

        .card-heading p {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1.6rem;
        }

        .form-group {
            margin-bottom: 1.35rem;
        }

        .form-group label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
            letter-spacing: 0.01em;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            transition: color .2s;
            pointer-events: none;
            display: flex;
        }

        .form-input {
            width: 100%;
            padding: 11px 13px 11px 39px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            color: var(--text-primary);
            background: #f8fafc;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:hover {
            border-color: #cbd5e1;
            background: #fff;
        }

        .form-input:focus {
            border-color: var(--emerald);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
        }

        .input-wrap:focus-within .input-icon {
            color: var(--emerald);
        }

        .btn-submit {
            width: 100%;
            padding: 12.5px;
            border: none;
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, var(--emerald) 0%, var(--forest) 100%);
            box-shadow: 0 4px 18px rgba(16, 185, 129, .38);
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            letter-spacing: 0.01em;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, .18) 0%, transparent 60%);
            opacity: 0;
            transition: opacity .25s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(16, 185, 129, .48);
        }

        .btn-submit:hover::before {
            opacity: 1;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .card-footer {
            text-align: center;
            margin-top: 1.35rem;
            font-size: 12px;
            color: var(--text-muted);
        }

        .card-footer a {
            color: var(--emerald-mid);
            font-weight: 600;
            text-decoration: none;
            transition: color .2s;
        }

        .card-footer a:hover {
            color: var(--forest);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--emerald-mid);
            text-decoration: none;
            margin-bottom: 1.2rem;
            transition: color .2s;
        }

        .back-link:hover {
            color: var(--forest);
        }

        .back-link svg {
            width: 14px;
            height: 14px;
        }
    </style>
</head>

<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="noise"></div>

    <div class="container">
        <div class="forgot-card">

            <a href="login.php" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back to Login
            </a>

            <div class="logo-area">
                <div class="logo-mark">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3L2 8l10 5 10-5-10-5Z" fill="white" opacity=".9" />
                        <path d="M2 13l10 5 10-5" stroke="white" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round" opacity=".7" />
                        <path d="M2 17.5l10 5 10-5" stroke="white" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round" opacity=".4" />
                    </svg>
                </div>
                <div>
                    <div class="logo-name">SkillRise</div>
                    <div class="logo-tag">Education Excellence</div>
                </div>
            </div>

            <div class="card-heading">
                <h1>Forgot Password? 🔐</h1>
                <p>Enter your email address and we'll send you a verification code to reset your password.</p>
            </div>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                        </span>
                        <input type="email" id="email" name="email" class="form-input" placeholder="you@institution.edu"
                            required />
                    </div>
                </div>

                <button class="btn-submit" type="submit">Send Verification Code</button>
            </form>

            <p class="card-footer">
                Remember your password? <a href="login.php">Sign In</a>
            </p>

        </div>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ALERTS (must be after SweetAlert2 is loaded) -->
    <?php if (!empty($alert))
        echo $alert; ?>

</body>

</html>