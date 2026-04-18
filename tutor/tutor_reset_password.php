<?php
session_start();
include_once 'connection.php';

$alert = "";

// If already logged in, redirect to dashboard
if (!empty($_SESSION['tutor_logged']) && $_SESSION['tutor_logged'] === true) {
    header("Location: index.php");
    exit();
}

// Check if OTP was verified
if (empty($_SESSION['otp_verified']) || empty($_SESSION['tutor_reset_email'])) {
    header("Location: tutor_forgot_password.php");
    exit();
}

if (isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($new_password) || empty($confirm_password)) {
        $alert = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields',
                    text: 'Please fill in both password fields.',
                    confirmButtonColor: '#10b981'
                });
            </script>
        ";
    } elseif (strlen($new_password) < 6) {
        $alert = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must be at least 6 characters long.',
                    confirmButtonColor: '#10b981'
                });
            </script>
        ";
    } elseif ($new_password !== $confirm_password) {
        $alert = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Passwords do not match.',
                    confirmButtonColor: '#10b981'
                });
            </script>
        ";
    } else {
        // Hash the new password
        $email = $_SESSION['tutor_reset_email'];
        $new_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password in database (plain text)
        $stmt = mysqli_prepare($conn, "UPDATE tutor_tbl SET password = ? WHERE tutor_email = ?");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $new_password, $email);

            if (mysqli_stmt_execute($stmt)) {
                // Clear all session variables
                session_unset();
                session_destroy();

                $alert = "
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Reset Successful!',
                            text: 'You can now login with your new password.',
                            timer: 2000,
                            timerProgressBar: true,
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            window.location.href = 'login.php';
                        });
                    </script>
                ";
            } else {
                $alert = "
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: 'Failed to update password. Please try again.',
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
    <title>Reset Password | <?php echo htmlspecialchars($company_name); ?></title>
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

        .reset-card {
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

        .toggle-pw {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            cursor: pointer;
            color: var(--text-muted);
            transition: color .2s;
        }

        .toggle-pw:hover {
            color: var(--emerald);
        }

        .password-strength {
            margin-top: 8px;
            font-size: 11px;
        }

        .strength-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 4px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: width 0.3s, background 0.3s;
            width: 0%;
            background: #94a3b8;
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

        .password-requirements {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 8px;
        }

        .password-requirements ul {
            margin: 5px 0 0 20px;
        }

        .password-requirements li {
            margin: 3px 0;
        }
    </style>
</head>

<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="noise"></div>

    <div class="container">
        <div class="reset-card">

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
                <h1>Set New Password 🔑</h1>
                <p>Choose a strong password for your account.</p>
            </div>

            <form action="" method="POST" id="resetForm">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </span>
                        <input type="password" id="new_password" name="new_password" class="form-input"
                            placeholder="Enter new password" required minlength="6" />
                        <span class="toggle-pw" onclick="togglePassword('new_password', 'eye1')">
                            <svg id="eye1" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </span>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthBar"></div>
                        </div>
                        <span id="strengthText"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </span>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                            placeholder="Confirm new password" required minlength="6" />
                        <span class="toggle-pw" onclick="togglePassword('confirm_password', 'eye2')">
                            <svg id="eye2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="password-requirements">
                    <strong>Password must:</strong>
                    <ul>
                        <li>Be at least 6 characters long</li>
                        <li>Contain uppercase and lowercase letters (recommended)</li>
                        <li>Include numbers and special characters (recommended)</li>
                    </ul>
                </div>

                <button class="btn-submit" type="submit" style="margin-top: 1.5rem;">Reset Password</button>
            </form>

        </div>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ALERTS (must be after SweetAlert2 is loaded) -->
    <?php if (!empty($alert))
        echo $alert; ?>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            icon.innerHTML = isText
                ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
                : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
        }

        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function (e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            const percentage = (strength / 5) * 100;
            strengthBar.style.width = percentage + '%';

            if (strength <= 1) {
                strengthBar.style.background = '#ef4444';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#ef4444';
            } else if (strength <= 3) {
                strengthBar.style.background = '#f59e0b';
                strengthText.textContent = 'Medium';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthBar.style.background = '#10b981';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#10b981';
            }
        });

        // Password match validation
        document.getElementById('resetForm').addEventListener('submit', function (e) {
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (password !== confirm) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords Do Not Match',
                    text: 'Please make sure both passwords are the same.',
                    confirmButtonColor: '#10b981'
                });
            }
        });
    </script>

</body>

</html>