<?php
session_start();
include_once 'connection.php';

if (isset($_SESSION['tutor_id'])) {
    header('Location: index.php');
    exit();
}
// Initialize flags for alerts
$login_error = '';
$account_deactivated = false;
$account_not_verified = false;
$session_timeout = isset($_GET['timeout']) && $_GET['timeout'] == '1';
$logout_success = isset($_GET['logout']) && $_GET['logout'] == '1';

// If already logged in, redirect to tutor dashboard
if (!empty($_SESSION['tutor_logged']) && $_SESSION['tutor_logged'] === true) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Validation
    $errors = [];

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    if (empty($errors)) {
        // Use prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT tutor_tbl.*, tutor_profile_tbl.profile_pic FROM tutor_tbl INNER JOIN tutor_profile_tbl ON tutor_tbl.tutor_id = tutor_profile_tbl.tutor_id WHERE tutor_email = ?");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $tutor = mysqli_fetch_assoc($result);

                // Verify password - check if it's hashed or plain text
                $password_valid = false;

                // Check if password appears to be hashed (bcrypt hashes start with $2y$ and are 60 chars)
                if (strlen($tutor['password']) == 60 && substr($tutor['password'], 0, 4) === '$2y$') {
                    // Password is hashed - use password_verify
                    $password_valid = password_verify($password, $tutor['password']);
                } else {
                    // Password is plain text - direct comparison
                    $password_valid = ($password === $tutor['password']);
                }

                if ($password_valid) {

                    // Check verification status
                    if ($tutor['verification_status'] != 'approved' && $tutor['verification_status'] != 1) {
                        $account_not_verified = true;
                    }
                    // Check if account is active
                    elseif ($tutor['tutor_status'] != 1) {
                        $account_deactivated = true;
                    }
                    // Login successful
                    else {
                        // Regenerate session ID to prevent session fixation
                        session_regenerate_id(true);

                        $_SESSION['tutor_id'] = $tutor['tutor_id'];
                        $_SESSION['tutor_email'] = $tutor['tutor_email'];
                        $_SESSION['tutor_name'] = $tutor['tutor_name'];
                        $_SESSION['tutor_phone'] = $tutor['tutor_phone'];
                        $_SESSION['tutor_image'] = $tutor['profile_pic'];
                        $_SESSION['tutor_logged'] = true;
                        $_SESSION['login_time'] = time();

                        // Update last login time (optional - requires last_login column)
                        // $update_stmt = mysqli_prepare($conn, "UPDATE tutor_tbl SET last_login = NOW() WHERE tutor_id = ?");
                        // mysqli_stmt_bind_param($update_stmt, "i", $tutor['tutor_id']);
                        // mysqli_stmt_execute($update_stmt);

                        header('Location: index.php');
                        exit();
                    }
                } else {
                    $login_error = 'Invalid email or password';
                }
            } else {
                $login_error = 'Invalid email or password';
            }

            mysqli_stmt_close($stmt);
        } else {
            $login_error = 'Database error. Please try again later.';
        }
    } else {
        $login_error = implode(', ', $errors);
    }
}

// Get company name from connection.php if it exists
$company_name = isset($company_name) ? $company_name : 'TutorPanel';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tutor Login | <?php echo htmlspecialchars($company_name); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="shortcut icon" href="codez3.png">

    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="noise"></div>

    <svg class="dots dots-bl" width="100" height="100" viewBox="0 0 100 100">
        <pattern id="dp1" x="0" y="0" width="14" height="14" patternUnits="userSpaceOnUse">
            <circle cx="2" cy="2" r="1.5" fill="white" />
        </pattern>
        <rect width="100" height="100" fill="url(#dp1)" />
    </svg>
    <svg class="dots dots-tr" width="100" height="100" viewBox="0 0 100 100">
        <pattern id="dp2" x="0" y="0" width="14" height="14" patternUnits="userSpaceOnUse">
            <circle cx="2" cy="2" r="1.5" fill="white" />
        </pattern>
        <rect width="100" height="100" fill="url(#dp2)" />
    </svg>

    <div class="layout">

        <!-- ════ LEFT PANEL — Sessions ════ -->
        <div class="side-panel lp">
            <div class="side-inner">

                <div class="illus-stage">
                    <!-- Badges -->
                    <div class="badge badge-sessions">
                        <div class="bdot" style="background:#60a5fa;"></div>
                        <span>12 Sessions Today</span>
                    </div>
                    <div class="badge badge-tutors">
                        <div class="bdot" style="background:#34d399;"></div>
                        <span>48 Active Tutors</span>
                    </div>

                    <!-- THE ILLUSTRATION -->
                    <svg viewBox="0 0 300 340" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Glow rings -->
                        <circle cx="150" cy="170" r="118" fill="rgba(16,185,129,.06)" />
                        <circle cx="150" cy="170" r="148" stroke="rgba(255,255,255,.045)" stroke-width="1"
                            stroke-dasharray="5 4" />
                        <circle cx="150" cy="170" r="175" stroke="rgba(255,255,255,.03)" stroke-width="1"
                            stroke-dasharray="3 6" />

                        <!-- Shadow under desk -->
                        <ellipse cx="150" cy="298" rx="105" ry="11" fill="rgba(0,0,0,.22)" />

                        <!-- ── BOOK STACK LEFT ── -->
                        <rect x="48" y="234" width="78" height="12" rx="3.5" fill="#059669" opacity=".92" />
                        <rect x="52" y="222" width="72" height="12" rx="3.5" fill="#34d399" opacity=".8" />
                        <rect x="50" y="210" width="75" height="12" rx="3.5" fill="#10b981" opacity=".95" />
                        <!-- spine lines -->
                        <line x1="62" y1="210" x2="62" y2="246" stroke="rgba(255,255,255,.2)" stroke-width="1.2" />
                        <line x1="76" y1="210" x2="76" y2="246" stroke="rgba(255,255,255,.13)" stroke-width="1.2" />
                        <line x1="90" y1="210" x2="90" y2="246" stroke="rgba(255,255,255,.09)" stroke-width="1.2" />

                        <!-- ── BOOK STACK RIGHT ── -->
                        <rect x="178" y="240" width="66" height="10" rx="3" fill="#047857" opacity=".87" />
                        <rect x="182" y="230" width="60" height="10" rx="3" fill="#34d399" opacity=".73" />
                        <rect x="180" y="220" width="64" height="10" rx="3" fill="#6ee7b7" opacity=".64" />

                        <!-- ── OPEN BOOK CENTER ── -->
                        <path d="M88 200 Q150 182 212 200 L208 268 Q150 250 92 268 Z" fill="rgba(255,255,255,.1)"
                            stroke="rgba(255,255,255,.22)" stroke-width="1.4" />
                        <!-- spine -->
                        <line x1="150" y1="184" x2="150" y2="266" stroke="rgba(255,255,255,.3)" stroke-width="1.5" />
                        <!-- left page lines -->
                        <line x1="104" y1="212" x2="142" y2="208" stroke="rgba(255,255,255,.19)" stroke-width="1.2" />
                        <line x1="103" y1="223" x2="141" y2="219" stroke="rgba(255,255,255,.14)" stroke-width="1.2" />
                        <line x1="103" y1="234" x2="141" y2="230" stroke="rgba(255,255,255,.11)" stroke-width="1.2" />
                        <line x1="104" y1="245" x2="142" y2="241" stroke="rgba(255,255,255,.08)" stroke-width="1.2" />
                        <!-- right page lines -->
                        <line x1="158" y1="208" x2="196" y2="212" stroke="rgba(255,255,255,.19)" stroke-width="1.2" />
                        <line x1="159" y1="219" x2="197" y2="223" stroke="rgba(255,255,255,.14)" stroke-width="1.2" />
                        <line x1="159" y1="230" x2="197" y2="234" stroke="rgba(255,255,255,.11)" stroke-width="1.2" />
                        <line x1="159" y1="241" x2="197" y2="245" stroke="rgba(255,255,255,.08)" stroke-width="1.2" />

                        <!-- ── GRADUATION CAP ── -->
                        <!-- body -->
                        <rect x="110" y="98" width="80" height="56" rx="7" fill="rgba(255,255,255,.12)"
                            stroke="rgba(255,255,255,.23)" stroke-width="1.5" />
                        <!-- brim -->
                        <rect x="94" y="90" width="112" height="15" rx="5" fill="rgba(255,255,255,.2)"
                            stroke="rgba(255,255,255,.3)" stroke-width="1.3" />
                        <!-- diamond top -->
                        <polygon points="150,62 167,84 150,92 133,84" fill="rgba(255,255,255,.26)"
                            stroke="rgba(255,255,255,.34)" stroke-width="1.3" />
                        <!-- tassel cord -->
                        <line x1="167" y1="84" x2="190" y2="106" stroke="rgba(255,255,255,.44)" stroke-width="2"
                            stroke-linecap="round" />
                        <!-- tassel ball -->
                        <circle cx="190" cy="106" r="5.5" fill="#fbbf24" opacity=".95" />
                        <!-- tassel strands -->
                        <line x1="190" y1="112" x2="185" y2="130" stroke="#fbbf24" stroke-width="1.6" opacity=".88"
                            stroke-linecap="round" />
                        <line x1="190" y1="112" x2="192" y2="132" stroke="#fbbf24" stroke-width="1.6" opacity=".74"
                            stroke-linecap="round" />
                        <line x1="190" y1="112" x2="197" y2="128" stroke="#fbbf24" stroke-width="1.6" opacity=".6"
                            stroke-linecap="round" />

                        <!-- ── PENCIL ── -->
                        <g transform="rotate(21, 248, 210)">
                            <rect x="242" y="174" width="9" height="58" rx="2.5" fill="#fbbf24" opacity=".9" />
                            <polygon points="246.5,174 242,174 244,163" fill="#f59e0b" />
                            <rect x="242" y="228" width="9" height="7" rx="1.5" fill="#f87171" opacity=".82" />
                            <rect x="242" y="235" width="9" height="3.5" rx="0.5" fill="rgba(0,0,0,.28)" />
                        </g>

                        <!-- ── FLOATING FORMULAS ── -->
                        <text x="30" y="158" font-size="13" fill="rgba(255,255,255,.36)" font-family="monospace"
                            font-style="italic">E=mc²</text>
                        <text x="212" y="182" font-size="11" fill="rgba(255,255,255,.26)" font-family="monospace">∑ π
                            √</text>

                        <!-- ── TWINKLING STARS ── -->
                        <circle cx="66" cy="84" r="2.8" fill="rgba(255,255,255,.55)">
                            <animate attributeName="opacity" values=".55;1;.55" dur="2.6s" repeatCount="indefinite" />
                        </circle>
                        <circle cx="244" cy="108" r="2.2" fill="rgba(255,255,255,.45)">
                            <animate attributeName="opacity" values=".45;.95;.45" dur="3.4s" repeatCount="indefinite" />
                        </circle>
                        <circle cx="78" cy="178" r="2.2" fill="rgba(255,255,255,.36)">
                            <animate attributeName="opacity" values=".36;.82;.36" dur="2.1s" repeatCount="indefinite" />
                        </circle>
                        <circle cx="260" cy="76" r="1.8" fill="rgba(255,255,255,.42)">
                            <animate attributeName="opacity" values=".42;.92;.42" dur="4.2s" repeatCount="indefinite" />
                        </circle>
                        <circle cx="40" cy="240" r="1.6" fill="rgba(255,255,255,.3)">
                            <animate attributeName="opacity" values=".3;.72;.3" dur="3.6s" repeatCount="indefinite" />
                        </circle>
                    </svg>
                </div>

                <div class="tagline">
                    <h2>Every Session<br>Counts</h2>
                    <p>Track live sessions and<br>tutor activity in real‑time</p>
                </div>

            </div>
        </div>

        <div class="vdiv"></div>

        <!-- ════ CENTER — Login ════ -->
        <div class="center-col">
            <div class="login-card">

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
                        <div class="logo-name">TutorPanel</div>
                        <div class="logo-tag">Tutor Portal</div>
                    </div>
                </div>

                <div class="card-heading">
                    <h1>Welcome Back 👋</h1>
                    <p>Login to access your tutor dashboard</p>
                </div>

                <form action="" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                    <polyline points="22,6 12,13 2,6" />
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" class="form-input"
                                placeholder="you@institution.edu" required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" class="form-input"
                                placeholder="Enter your password" required />
                            <button class="toggle-pass" onclick="togglePass(event)" type="button">
                                <svg id="eye-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <!-- <label class="remember">
                            <input type="checkbox" id="remember" name="remember">
                            <div class="check-box">
                                <svg class="check-icon" width="10" height="8" viewBox="0 0 10 8" fill="none">
                                    <path d="M1 4l3 3 5-6" stroke="white" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <span>Remember me</span>
                        </label> -->
                        <a href="tutor_forgot_password.php" class="forgot-link">Forgot password?</a>
                    </div>

                    <button class="btn-login" type="submit">Sign In to Dashboard</button>
                </form>

                <!-- <p class="card-footer">
                    Don't have an account? <a href="tutor_register.php">Request Access</a>
                </p> -->

            </div>
        </div>

        <div class="vdiv"></div>

        <!-- ════ RIGHT PANEL — Students ════ -->
        <div class="side-panel rp">
            <div class="side-inner">

                <div class="illus-stage">
                    <!-- Badges -->
                    <div class="badge badge-rating">
                        <div class="bdot" style="background:#fbbf24;"></div>
                        <span>95% Student Rating</span>
                    </div>
                    <div class="badge badge-tutors2">
                        <div class="bdot" style="background:#34d399;"></div>
                        <span>2,847 Students</span>
                    </div>

                    <!-- SAME ILLUSTRATION mirrored -->
                    <svg viewBox="0 0 300 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                        style="transform:scaleX(-1)">
                        <circle cx="150" cy="170" r="118" fill="rgba(16,185,129,.06)" />
                        <circle cx="150" cy="170" r="148" stroke="rgba(255,255,255,.045)" stroke-width="1"
                            stroke-dasharray="5 4" />
                        <circle cx="150" cy="170" r="175" stroke="rgba(255,255,255,.03)" stroke-width="1"
                            stroke-dasharray="3 6" />

                        <ellipse cx="150" cy="298" rx="105" ry="11" fill="rgba(0,0,0,.22)" />

                        <rect x="48" y="234" width="78" height="12" rx="3.5" fill="#059669" opacity=".92" />
                        <rect x="52" y="222" width="72" height="12" rx="3.5" fill="#34d399" opacity=".8" />
                        <rect x="50" y="210" width="75" height="12" rx="3.5" fill="#10b981" opacity=".95" />
                        <line x1="62" y1="210" x2="62" y2="246" stroke="rgba(255,255,255,.2)" stroke-width="1.2" />
                        <line x1="76" y1="210" x2="76" y2="246" stroke="rgba(255,255,255,.13)" stroke-width="1.2" />
                        <line x1="90" y1="210" x2="90" y2="246" stroke="rgba(255,255,255,.09)" stroke-width="1.2" />

                        <rect x="178" y="240" width="66" height="10" rx="3" fill="#047857" opacity=".87" />
                        <rect x="182" y="230" width="60" height="10" rx="3" fill="#34d399" opacity=".73" />
                        <rect x="180" y="220" width="64" height="10" rx="3" fill="#6ee7b7" opacity=".64" />

                        <path d="M88 200 Q150 182 212 200 L208 268 Q150 250 92 268 Z" fill="rgba(255,255,255,.1)"
                            stroke="rgba(255,255,255,.22)" stroke-width="1.4" />
                        <line x1="150" y1="184" x2="150" y2="266" stroke="rgba(255,255,255,.3)" stroke-width="1.5" />
                        <line x1="104" y1="212" x2="142" y2="208" stroke="rgba(255,255,255,.19)" stroke-width="1.2" />
                        <line x1="103" y1="223" x2="141" y2="219" stroke="rgba(255,255,255,.14)" stroke-width="1.2" />
                        <line x1="103" y1="234" x2="141" y2="230" stroke="rgba(255,255,255,.11)" stroke-width="1.2" />
                        <line x1="104" y1="245" x2="142" y2="241" stroke="rgba(255,255,255,.08)" stroke-width="1.2" />
                        <line x1="158" y1="208" x2="196" y2="212" stroke="rgba(255,255,255,.19)" stroke-width="1.2" />
                        <line x1="159" y1="219" x2="197" y2="223" stroke="rgba(255,255,255,.14)" stroke-width="1.2" />
                        <line x1="159" y1="230" x2="197" y2="234" stroke="rgba(255,255,255,.11)" stroke-width="1.2" />
                        <line x1="159" y1="241" x2="197" y2="245" stroke="rgba(255,255,255,.08)" stroke-width="1.2" />

                        <rect x="110" y="98" width="80" height="56" rx="7" fill="rgba(255,255,255,.12)"
                            stroke="rgba(255,255,255,.23)" stroke-width="1.5" />
                        <rect x="94" y="90" width="112" height="15" rx="5" fill="rgba(255,255,255,.2)"
                            stroke="rgba(255,255,255,.3)" stroke-width="1.3" />
                        <polygon points="150,62 167,84 150,92 133,84" fill="rgba(255,255,255,.26)"
                            stroke="rgba(255,255,255,.34)" stroke-width="1.3" />
                        <line x1="167" y1="84" x2="190" y2="106" stroke="rgba(255,255,255,.44)" stroke-width="2"
                            stroke-linecap="round" />
                        <circle cx="190" cy="106" r="5.5" fill="#fbbf24" opacity=".95" />
                        <line x1="190" y1="112" x2="185" y2="130" stroke="#fbbf24" stroke-width="1.6" opacity=".88"
                            stroke-linecap="round" />
                        <line x1="190" y1="112" x2="192" y2="132" stroke="#fbbf24" stroke-width="1.6" opacity=".74"
                            stroke-linecap="round" />
                        <line x1="190" y1="112" x2="197" y2="128" stroke="#fbbf24" stroke-width="1.6" opacity=".6"
                            stroke-linecap="round" />

                        <g transform="rotate(21, 248, 210)">
                            <rect x="242" y="174" width="9" height="58" rx="2.5" fill="#fbbf24" opacity=".9" />
                            <polygon points="246.5,174 242,174 244,163" fill="#f59e0b" />
                            <rect x="242" y="228" width="9" height="7" rx="1.5" fill="#f87171" opacity=".82" />
                            <rect x="242" y="235" width="9" height="3.5" rx="0.5" fill="rgba(0,0,0,.28)" />
                        </g>

                        <text x="30" y="158" font-size="13" fill="rgba(255,255,255,.36)" font-family="monospace"
                            font-style="italic">E=mc²</text>
                        <text x="212" y="182" font-size="11" fill="rgba(255,255,255,.26)" font-family="monospace">∑ π
                            √</text>

                        <circle cx="66" cy="84" r="2.8" fill="rgba(255,255,255,.55)">
                            <animate attributeName="opacity" values=".55;1;.55" dur="2.6s" repeatCount="indefinite" />
                        </circle>
                        <circle cx="244" cy="108" r="2.2" fill="rgba(255,255,255,.45)">
                            <animate attributeName="opacity" values=".45;.95;.45" dur="3.4s" repeatCount="indefinite" />
                        </circle>
                        <circle cx="78" cy="178" r="2.2" fill="rgba(255,255,255,.36)">
                            <animate attributeName="opacity" values=".36;.82;.36" dur="2.1s" repeatCount="indefinite" />
                        </circle>
                        <circle cx="260" cy="76" r="1.8" fill="rgba(255,255,255,.42)">
                            <animate attributeName="opacity" values=".42;.92;.42" dur="4.2s" repeatCount="indefinite" />
                        </circle>
                        <circle cx="40" cy="240" r="1.6" fill="rgba(255,255,255,.3)">
                            <animate attributeName="opacity" values=".3;.72;.3" dur="3.6s" repeatCount="indefinite" />
                        </circle>
                    </svg>
                </div>

                <div class="tagline">
                    <h2>Empower Every<br>Learning Journey</h2>
                    <p>Manage students, track progress,<br>and elevate outcomes</p>
                </div>

            </div>
        </div>

    </div><!-- /layout -->

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function togglePass(event) {
            event.preventDefault();
            const pw = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            const isText = pw.type === 'text';
            pw.type = isText ? 'password' : 'text';
            icon.innerHTML = isText
                ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
                : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
        }

        // Card animation
        const card = document.querySelector('.login-card');
        [...card.children].forEach((el, i) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(13px)';
            el.style.transition = `opacity .5s ease ${.3 + i * .07}s, transform .5s ease ${.3 + i * .07}s`;
            requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
        });
    </script>

    <?php if (!empty($login_error)): ?>
        <script>
            Swal.fire({
                title: 'Login Failed',
                text: '<?php echo addslashes($login_error); ?>',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981'
            });
        </script>
    <?php endif; ?>

    <?php if ($account_deactivated): ?>
        <script>
            Swal.fire({
                title: 'Account Deactivated',
                text: 'Your tutor account has been deactivated. Please contact support.',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981'
            });
        </script>
    <?php endif; ?>

    <?php if ($account_not_verified): ?>
        <script>
            Swal.fire({
                title: 'Account Not Verified',
                text: 'Your account is pending verification. Please check your email or contact support.',
                icon: 'info',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981'
            });
        </script>
    <?php endif; ?>

    <?php if ($session_timeout): ?>
        <script>
            Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired due to inactivity. Please login again.',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981'
            });
        </script>
    <?php endif; ?>

    <?php if ($logout_success): ?>
        <script>
            Swal.fire({
                title: 'Logged Out',
                text: 'You have been successfully logged out.',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981',
                timer: 2000
            });
        </script>
    <?php endif; ?>

</body>

</html>