<?php
require_once('includes/init.php');
include 'connection.php';

$tutor_id = (int) $_SESSION['tutor_id'];

// ── Fetch tutor core info ──────────────────────────────
$t_res = mysqli_query($conn, "SELECT * FROM tutor_tbl WHERE tutor_id = $tutor_id");
$tutor = mysqli_fetch_assoc($t_res);

// ── Fetch tutor profile ────────────────────────────────
$p_res = mysqli_query($conn, "SELECT * FROM tutor_profile_tbl WHERE tutor_id = $tutor_id");
$profile = mysqli_fetch_assoc($p_res);

// ── Fetch active package ───────────────────────────────
$pkg_res = mysqli_query($conn, "
    SELECT tp.*, p.package_name, p.price, p.valid_months
    FROM tutor_package_tbl tp
    JOIN package_tbl p ON tp.package_id = p.package_id
    WHERE tp.tutor_id = $tutor_id AND tp.payment_status = 1
    ORDER BY tp.created_at DESC LIMIT 1
");
$pkg = mysqli_fetch_assoc($pkg_res);

$pkg_days_left = 0;
$pkg_status = 'inactive';
if ($pkg) {
    $end = new DateTime($pkg['end_date']);
    $now = new DateTime();
    $diff = $now->diff($end);
    $pkg_days_left = ($diff->invert === 0) ? $diff->days : 0;
    $pkg_status = $pkg_days_left > 0 ? 'active' : 'expired';
}

// ── Handle POST actions ────────────────────────────────
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Save General Info ──────────────────────────────
    if ($action === 'general') {
        $name = mysqli_real_escape_string($conn, trim($_POST['tutor_name'] ?? ''));
        $email = mysqli_real_escape_string($conn, trim($_POST['tutor_email'] ?? ''));
        $phone = mysqli_real_escape_string($conn, trim($_POST['tutor_phone'] ?? ''));

        if (empty($name) || empty($email)) {
            $error = 'Name and email are required.';
        } else {
            // Check email uniqueness
            $chk = mysqli_query($conn, "SELECT tutor_id FROM tutor_tbl WHERE tutor_email = '$email' AND tutor_id != $tutor_id");
            if (mysqli_num_rows($chk) > 0) {
                $error = 'This email is already used by another account.';
            } else {
                mysqli_query($conn, "
                    UPDATE tutor_tbl
                    SET tutor_name = '$name', tutor_email = '$email', tutor_phone = '$phone'
                    WHERE tutor_id = $tutor_id
                ");
                $success = 'General information updated successfully.';
                // Re-fetch
                $t_res = mysqli_query($conn, "SELECT * FROM tutor_tbl WHERE tutor_id = $tutor_id");
                $tutor = mysqli_fetch_assoc($t_res);
            }
        }
    }

    // ── Save Profile Details ───────────────────────────
    if ($action === 'profile') {
        $bio = mysqli_real_escape_string($conn, trim($_POST['bio'] ?? ''));
        $country = mysqli_real_escape_string($conn, trim($_POST['country'] ?? ''));
        $langs = mysqli_real_escape_string($conn, trim($_POST['languages'] ?? ''));
        $expertise = mysqli_real_escape_string($conn, trim($_POST['expertise'] ?? ''));

        // Upsert profile
        if ($profile) {
            mysqli_query($conn, "
                UPDATE tutor_profile_tbl
                SET bio = '$bio', country = '$country', languages_known = '$langs', expertise = '$expertise'
                WHERE tutor_id = $tutor_id
            ");
        } else {
            mysqli_query($conn, "
                INSERT INTO tutor_profile_tbl (tutor_id, bio, country, languages_known, expertise)
                VALUES ($tutor_id, '$bio', '$country', '$langs', '$expertise')
            ");
        }
        $success = 'Profile details updated successfully.';
        $p_res = mysqli_query($conn, "SELECT * FROM tutor_profile_tbl WHERE tutor_id = $tutor_id");
        $profile = mysqli_fetch_assoc($p_res);
    }

    // ── Change Password ────────────────────────────────
    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new_pass) || empty($confirm)) {
            $error = 'All password fields are required.';
        } elseif ($new_pass !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_pass) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            // Verify current password (adjust hash method to match your system)
            $stored = $tutor['password'] ?? '';
            $valid = password_verify($current, $stored) || ($stored === md5($current)) || ($stored === $current);

            if (!$valid) {
                $error = 'Current password is incorrect.';
            } else {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $hashed_esc = mysqli_real_escape_string($conn, $hashed);
                mysqli_query($conn, "UPDATE tutor_tbl SET password = '$hashed_esc' WHERE tutor_id = $tutor_id");
                $success = 'Password changed successfully.';
                include_once "logout.php";
            }
        }
    }

    // ── Save Notification Preferences ─────────────────
    if ($action === 'notifications') {
        $n_email = isset($_POST['n_email']) ? 1 : 0;
        $n_sms = isset($_POST['n_sms']) ? 1 : 0;
        $n_new_stud = isset($_POST['n_new_stud']) ? 1 : 0;
        $n_payment = isset($_POST['n_payment']) ? 1 : 0;
        $n_review = isset($_POST['n_review']) ? 1 : 0;

        // Store in tutor_profile_tbl if columns exist, else use session for demo
        // Using profile table — add these columns if needed, otherwise store as JSON in a meta field
        // For now we'll store in session as a lightweight approach
        $_SESSION['notif_prefs'] = compact('n_email', 'n_sms', 'n_new_stud', 'n_payment', 'n_review');
        $success = 'Notification preferences saved.';
    }
}

// Load notification prefs from session (or defaults)
$notif = $_SESSION['notif_prefs'] ?? [
    'n_email' => 1,
    'n_sms' => 0,
    'n_new_stud' => 1,
    'n_payment' => 1,
    'n_review' => 1,
];

// Avatar
$avatar = (!empty($profile['profile_pic']))
    ? $tutor_profile_path . htmlspecialchars($profile['profile_pic'])
    : 'assets/images/user.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php'; ?>
    <link rel="stylesheet" href="assets/css/settings.css">
</head>

<body class="app">
    <?php include 'includes/header.php'; ?>

    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">

                <!-- Page Header -->
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title">Settings</h1>
                        <div class="page-sub">Manage your account, plan and preferences</div>
                    </div>
                    <a href="account.php" class="d-inline-flex align-items-center gap-2"
                        style="font-size:.85rem;font-weight:600;color:var(--muted);text-decoration:none;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Account
                    </a>
                </div>

                <!-- Flash messages replaced by SweetAlert2 -->

                <div class="row g-4">

                    <!-- ══ Sidebar Nav ══ -->
                    <div class="col-12 col-md-3">
                        <div class="settings-nav">
                            <div class="nav-header">Settings</div>
                            <nav>
                                <div class="nav-item">
                                    <a href="#general" class="nav-link active" onclick="setActive(this)">
                                        <i class="fa-solid fa-user"></i> General
                                    </a>
                                </div>
                                <div class="nav-item">
                                    <a href="#profile" class="nav-link" onclick="setActive(this)">
                                        <i class="fa-solid fa-id-card"></i> Profile Details
                                    </a>
                                </div>
                                <div class="nav-item">
                                    <a href="#plan" class="nav-link" onclick="setActive(this)">
                                        <i class="fa-solid fa-box"></i> Plan
                                    </a>
                                </div>
                                <div class="nav-item">
                                    <a href="#security" class="nav-link" onclick="setActive(this)">
                                        <i class="fa-solid fa-lock"></i> Security
                                    </a>
                                </div>
                                <!-- <div class="nav-item">
                                    <a href="#notifications" class="nav-link" onclick="setActive(this)">
                                        <i class="fa-solid fa-bell"></i> Notifications
                                    </a>
                                </div> -->
                                <div class="nav-item">
                                    <a href="#danger" class="nav-link" onclick="setActive(this)" style="color:#ef4444;">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Danger Zone
                                    </a>
                                </div>
                            </nav>
                        </div>
                    </div>

                    <!-- ══ Settings Sections ══ -->
                    <div class="col-12 col-md-9">

                        <!-- ── General ── -->
                        <div class="settings-card section-anchor" id="general">
                            <div class="settings-card-header">
                                <div class="s-icon"><i class="fa-solid fa-user"></i></div>
                                <div>
                                    <div class="s-title">General Information</div>
                                    <div class="s-sub">Update your name, email and contact details</div>
                                </div>
                            </div>
                            <div class="settings-card-body">
                                <!-- Avatar row -->
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <img src="<?= $avatar ?>" class="avatar-settings" alt="Avatar">
                                    <div>
                                        <div style="font-size:.85rem;font-weight:700;color:var(--ink);">Profile Photo
                                        </div>
                                        <div style="font-size:.75rem;color:var(--muted);margin-top:2px;">Change your
                                            photo from <a href="edit_profile.php#photo" style="color:var(--g600);">Edit
                                                Profile</a></div>
                                    </div>
                                </div>

                                <form method="POST">
                                    <input type="hidden" name="action" value="general">
                                    <div class="row g-3">
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Full Name <span
                                                    style="color:#ef4444;">*</span></label>
                                            <input type="text" name="tutor_name" class="form-control"
                                                value="<?= htmlspecialchars($tutor['tutor_name'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Email Address <span
                                                    style="color:#ef4444;">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"
                                                    style="border:1.5px solid var(--border);border-right:none;border-radius:9px 0 0 9px;background:#f9fafb;">
                                                    <i class="fa-solid fa-envelope"
                                                        style="color:var(--muted);font-size:.8rem;"></i>
                                                </span>
                                                <input type="email" name="tutor_email" class="form-control"
                                                    value="<?= htmlspecialchars($tutor['tutor_email'] ?? '') ?>"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text"
                                                    style="border:1.5px solid var(--border);border-right:none;border-radius:9px 0 0 9px;background:#f9fafb;">
                                                    <i class="fa-solid fa-phone"
                                                        style="color:var(--muted);font-size:.8rem;"></i>
                                                </span>
                                                <input type="text" name="tutor_phone" class="form-control"
                                                    style="border-radius:0 9px 9px 0;border-left:none;"
                                                    value="<?= htmlspecialchars($tutor['tutor_phone'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Member Since</label>
                                            <input type="text" class="form-control" readonly
                                                style="background:#f9fafb;color:var(--muted);"
                                                value="<?= !empty($tutor['created_at']) ? date('d M Y', strtotime($tutor['created_at'])) : '—' ?>">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn-save">
                                                <i class="fa-solid fa-floppy-disk"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- ── Profile Details ── -->
                        <div class="settings-card section-anchor" id="profile">
                            <div class="settings-card-header">
                                <div class="s-icon"><i class="fa-solid fa-id-card"></i></div>
                                <div>
                                    <div class="s-title">Profile Details</div>
                                    <div class="s-sub">Visible to students on your public profile</div>
                                </div>
                            </div>
                            <div class="settings-card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="profile">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Bio</label>
                                            <textarea name="bio" class="form-control" rows="3"
                                                placeholder="Tell students about yourself…"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Expertise / Subject</label>
                                            <input type="text" name="expertise" class="form-control"
                                                placeholder="e.g. Mathematics, Physics"
                                                value="<?= htmlspecialchars($profile['expertise'] ?? '') ?>">
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Country</label>
                                            <input type="text" name="country" class="form-control"
                                                placeholder="e.g. India"
                                                value="<?= htmlspecialchars($profile['country'] ?? '') ?>">
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Languages Known</label>
                                            <input type="text" name="languages" class="form-control"
                                                placeholder="e.g. English, Hindi"
                                                value="<?= htmlspecialchars($profile['languages_known'] ?? '') ?>">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn-save">
                                                <i class="fa-solid fa-floppy-disk"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- ── Plan ── -->
                        <div class="settings-card section-anchor" id="plan">
                            <div class="settings-card-header">
                                <div class="s-icon"><i class="fa-solid fa-box"></i></div>
                                <div>
                                    <div class="s-title">Current Plan</div>
                                    <div class="s-sub">Manage your subscription and billing</div>
                                </div>
                            </div>
                            <div class="settings-card-body">
                                <?php if ($pkg && $pkg_days_left > 0):
                                    $total = (new DateTime($pkg['start_date']))->diff(new DateTime($pkg['end_date']))->days;
                                    $used = max(0, $total - $pkg_days_left);
                                    $pct = $total > 0 ? min(100, round($used / $total * 100)) : 0;
                                    ?>
                                    <div class="plan-banner mb-3">
                                        <div style="position:relative;z-index:1;">
                                            <div
                                                style="font-size:.7rem;opacity:.75;text-transform:uppercase;letter-spacing:.1em;">
                                                Active Plan</div>
                                            <div class="pb-name"><?= htmlspecialchars($pkg['package_name']) ?></div>
                                            <div class="pb-dates">
                                                <i class="fa-solid fa-calendar me-1"></i>
                                                <?= date('d M Y', strtotime($pkg['start_date'])) ?>
                                                → <?= date('d M Y', strtotime($pkg['end_date'])) ?>
                                            </div>
                                            <div
                                                style="background:rgba(255,255,255,.2);border-radius:99px;height:5px;margin-top:10px;width:200px;max-width:100%;">
                                                <div
                                                    style="height:5px;border-radius:99px;background:#fff;width:<?= $pct ?>%;">
                                                </div>
                                            </div>
                                            <div style="font-size:.7rem;opacity:.7;margin-top:4px;"><?= $pct ?>% used</div>
                                        </div>
                                        <div class="pb-days" style="position:relative;z-index:1;">
                                            <div class="n"><?= $pkg_days_left ?></div>
                                            <div class="l">Days Left</div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3" style="font-size:.85rem;">
                                        <div class="col-6 col-sm-3">
                                            <div
                                                style="color:var(--muted);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                                                Amount Paid</div>
                                            <div style="font-weight:700;color:var(--ink);font-family:'Sora',sans-serif;">
                                                ₹<?= number_format($pkg['amount_paid'] ?? 0, 0) ?></div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div
                                                style="color:var(--muted);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                                                Duration</div>
                                            <div style="font-weight:700;color:var(--ink);"><?= $pkg['valid_months'] ?>
                                                months</div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div
                                                style="color:var(--muted);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                                                Status</div>
                                            <span class="plan-pill active"><i class="fa-solid fa-circle-dot"></i>
                                                Active</span>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div
                                                style="color:var(--muted);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                                                Razorpay ID</div>
                                            <div style="font-size:.75rem;color:var(--muted);font-family:monospace;">
                                                <?= htmlspecialchars($pkg['razorpay_id'] ?? '—') ?>
                                            </div>
                                        </div>
                                    </div>

                                <?php elseif ($pkg && $pkg_days_left === 0): ?>
                                    <div
                                        style="padding:16px;background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;margin-bottom:16px;font-size:.875rem;color:#b91c1c;font-weight:600;">
                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                        Your <strong><?= htmlspecialchars($pkg['package_name']) ?></strong> plan has
                                        expired.
                                    </div>
                                <?php else: ?>
                                    <div style="padding:20px;text-align:center;color:var(--muted);font-size:.875rem;">
                                        <i class="fa-solid fa-box-open fa-2x d-block mb-2" style="color:#d1d5db;"></i>
                                        No active plan. Get started with a plan below.
                                    </div>
                                <?php endif; ?>

                                <div class="plan-action-row">
                                    <a href="packages.php" class="btn-plan-upgrade">
                                        <i class="fa-solid fa-arrow-up"></i>
                                        <?= $pkg && $pkg_days_left > 0 ? 'Upgrade / Renew' : 'Get a Plan' ?>
                                    </a>
                                    <a href="invoice.php" class="btn-plan-secondary">
                                        <i class="fa-solid fa-receipt"></i> View Invoices
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- ── Security ── -->
                        <div class="settings-card section-anchor" id="security">
                            <div class="settings-card-header">
                                <div class="s-icon"><i class="fa-solid fa-lock"></i></div>
                                <div>
                                    <div class="s-title">Security</div>
                                    <div class="s-sub">Update your password to keep your account safe</div>
                                </div>
                            </div>
                            <div class="settings-card-body">
                                <form method="POST" id="pw-form">
                                    <input type="hidden" name="action" value="password">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Current Password</label>
                                            <div class="input-group">
                                                <input type="password" name="current_password" id="pw-current"
                                                    class="form-control"
                                                    style="border-radius:9px 0 0 9px;border-right:none;" required>
                                                <button type="button" class="input-group-text"
                                                    onclick="togglePw('pw-current',this)"
                                                    style="border:1.5px solid var(--border);border-left:none;border-radius:0 9px 9px 0;background:#f9fafb;cursor:pointer;">
                                                    <i class="fa-solid fa-eye"
                                                        style="color:var(--muted);font-size:.8rem;"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">New Password</label>
                                            <div class="input-group">
                                                <input type="password" name="new_password" id="pw-new"
                                                    class="form-control"
                                                    style="border-radius:9px 0 0 9px;border-right:none;"
                                                    oninput="checkStrength(this.value)" required>
                                                <button type="button" class="input-group-text"
                                                    onclick="togglePw('pw-new',this)"
                                                    style="border:1.5px solid var(--border);border-left:none;border-radius:0 9px 9px 0;background:#f9fafb;cursor:pointer;">
                                                    <i class="fa-solid fa-eye"
                                                        style="color:var(--muted);font-size:.8rem;"></i>
                                                </button>
                                            </div>
                                            <div id="pw-strength" class="pw-strength mt-1"
                                                style="width:0%;background:var(--border);"></div>
                                            <div id="pw-strength-label"
                                                style="font-size:.72rem;color:var(--muted);margin-top:3px;"></div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Confirm New Password</label>
                                            <div class="input-group">
                                                <input type="password" name="confirm_password" id="pw-confirm"
                                                    class="form-control"
                                                    style="border-radius:9px 0 0 9px;border-right:none;" required>
                                                <button type="button" class="input-group-text"
                                                    onclick="togglePw('pw-confirm',this)"
                                                    style="border:1.5px solid var(--border);border-left:none;border-radius:0 9px 9px 0;background:#f9fafb;cursor:pointer;">
                                                    <i class="fa-solid fa-eye"
                                                        style="color:var(--muted);font-size:.8rem;"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn-save">
                                                <i class="fa-solid fa-key"></i> Change Password
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- ── Notifications ── -->
                        <!-- <div class="settings-card section-anchor" id="notifications">
                            <div class="settings-card-header">
                                <div class="s-icon"><i class="fa-solid fa-bell"></i></div>
                                <div>
                                    <div class="s-title">Notification Preferences</div>
                                    <div class="s-sub">Choose when and how you want to be notified</div>
                                </div>
                            </div>
                            <div class="settings-card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="notifications">

                                    <div
                                        style="font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:12px;">
                                        Channels</div>
                                    <div class="notif-row">
                                        <div>
                                            <div class="notif-label"><i class="fa-solid fa-envelope me-2"
                                                    style="color:var(--g600);"></i>Email Notifications</div>
                                            <div class="notif-sub">Receive updates to your registered email</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="n_email" id="n_email"
                                                role="switch" <?= $notif['n_email'] ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="notif-row">
                                        <div>
                                            <div class="notif-label"><i class="fa-solid fa-mobile-screen me-2"
                                                    style="color:var(--g600);"></i>SMS Notifications</div>
                                            <div class="notif-sub">Receive text messages on your phone</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="n_sms" id="n_sms"
                                                role="switch" <?= $notif['n_sms'] ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <hr class="form-divider">
                                    <div
                                        style="font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:12px;">
                                        Events</div>

                                    <div class="notif-row">
                                        <div>
                                            <div class="notif-label"><i class="fa-solid fa-user-plus me-2"
                                                    style="color:var(--g600);"></i>New Student Enrollment</div>
                                            <div class="notif-sub">When a student buys your course</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="n_new_stud"
                                                id="n_new_stud" role="switch" <?= $notif['n_new_stud'] ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="notif-row">
                                        <div>
                                            <div class="notif-label"><i class="fa-solid fa-indian-rupee-sign me-2"
                                                    style="color:var(--g600);"></i>Payment Received</div>
                                            <div class="notif-sub">When a payment is credited to your account</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="n_payment"
                                                id="n_payment" role="switch" <?= $notif['n_payment'] ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="notif-row">
                                        <div>
                                            <div class="notif-label"><i class="fa-solid fa-star me-2"
                                                    style="color:var(--g600);"></i>New Review</div>
                                            <div class="notif-sub">When a student leaves a review</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="n_review"
                                                id="n_review" role="switch" <?= $notif['n_review'] ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn-save">
                                            <i class="fa-solid fa-floppy-disk"></i> Save Preferences
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div> -->

                        <!-- ── Danger Zone ── -->
                        <div class="settings-card section-anchor" id="danger" style="border-color:#fca5a5;">
                            <div class="settings-card-header" style="border-bottom-color:#fee2e2;">
                                <div class="s-icon" style="background:#fef2f2;border-color:#fca5a5;">
                                    <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i>
                                </div>
                                <div>
                                    <div class="s-title" style="color:#b91c1c;">Danger Zone</div>
                                    <div class="s-sub">Irreversible account actions</div>
                                </div>
                            </div>
                            <div class="settings-card-body">
                                <div
                                    style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:14px 0;border-bottom:1px solid #fee2e2;">
                                    <div>
                                        <div style="font-size:.875rem;font-weight:700;color:var(--ink);">Deactivate
                                            Account</div>
                                        <div style="font-size:.78rem;color:var(--muted);margin-top:2px;">Temporarily
                                            disable your account. You can reactivate anytime.</div>
                                    </div>
                                    <button type="button" class="btn-danger-outline"
                                        onclick="confirmAction('deactivate')">
                                        <i class="fa-solid fa-power-off"></i> Deactivate
                                    </button>
                                </div>
                                <div
                                    style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:14px 0;">
                                    <div>
                                        <div style="font-size:.875rem;font-weight:700;color:#b91c1c;">Delete Account
                                        </div>
                                        <div style="font-size:.78rem;color:var(--muted);margin-top:2px;">Permanently
                                            delete your account and all data. This cannot be undone.</div>
                                    </div>
                                    <button type="button" class="btn-danger-outline" onclick="confirmAction('delete')"
                                        style="border-color:#ef4444;color:#ef4444;">
                                        <i class="fa-solid fa-trash"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div><!-- /col-9 -->
                </div><!-- /row -->
            </div>
        </div>
    </div>

    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/app.js"></script>
    <?php include 'includes/script.php'; ?>
    <script>
        // ── Sidebar active nav ──────────────────────────────────
        function setActive(el) {
            document.querySelectorAll('.settings-nav .nav-link').forEach(l => l.classList.remove('active'));
            el.classList.add('active');
        }

        // Highlight nav on scroll
        const sections = document.querySelectorAll('.section-anchor');
        const navLinks = document.querySelectorAll('.settings-nav .nav-link');
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(sec => {
                if (window.scrollY >= sec.offsetTop - 100) current = sec.id;
            });
            navLinks.forEach(l => {
                l.classList.toggle('active', l.getAttribute('href') === '#' + current);
            });
        }, { passive: true });

        // ── Password visibility toggle ──────────────────────────
        function togglePw(id, btn) {
            const inp = document.getElementById(id);
            const ico = btn.querySelector('i');
            if (inp.type === 'password') {
                inp.type = 'text';
                ico.className = 'fa-solid fa-eye-slash';
                ico.style.color = 'var(--g600)';
            } else {
                inp.type = 'password';
                ico.className = 'fa-solid fa-eye';
                ico.style.color = 'var(--muted)';
            }
        }

        // ── Password strength ────────────────────────────────────
        function checkStrength(val) {
            const bar = document.getElementById('pw-strength');
            const label = document.getElementById('pw-strength-label');
            let score = 0;
            if (val.length >= 6) score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { w: '20%', bg: '#ef4444', txt: 'Very weak' },
                { w: '40%', bg: '#f97316', txt: 'Weak' },
                { w: '60%', bg: '#eab308', txt: 'Fair' },
                { w: '80%', bg: '#22c55e', txt: 'Strong' },
                { w: '100%', bg: '#16a34a', txt: 'Very strong' },
            ];
            const lvl = levels[Math.max(0, score - 1)] || levels[0];
            bar.style.width = val.length ? lvl.w : '0%';
            bar.style.background = val.length ? lvl.bg : 'var(--border)';
            label.textContent = val.length ? lvl.txt : '';
            label.style.color = val.length ? lvl.bg : '';
        }

        // ── Auto-scroll to section on page load if hash ──────────
        if (window.location.hash) {
            const el = document.querySelector(window.location.hash);
            if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
        }

        // ── SweetAlert2 Notifications ──────────────────────────
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= addslashes($success) ?>',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                timerProgressBar: true
            });
        <?php endif; ?>

        <?php if ($error): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= addslashes($error) ?>',
                confirmButtonColor: '#ef4444'
            });
        <?php endif; ?>

        // ── Danger Zone Confirmations ──────────────────────────
        function confirmAction(type) {
            const config = {
                deactivate: {
                    title: 'Deactivate Account?',
                    text: 'Temporarily disable your account. You can reactivate anytime.',
                    icon: 'warning',
                    confirmText: 'Yes, deactivate',
                    confirmColor: '#ef4444'
                },
                delete: {
                    title: 'Delete Account Permanently?',
                    text: 'This will permanently delete your account and ALL data. This cannot be undone!',
                    icon: 'error',
                    confirmText: 'Yes, delete everything',
                    confirmColor: '#b91c1c'
                }
            }[type];

            Swal.fire({
                title: config.title,
                text: config.text,
                icon: config.icon,
                showCancelButton: true,
                confirmButtonColor: config.confirmColor,
                cancelButtonColor: '#6b7280',
                confirmButtonText: config.confirmText,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Redirecting...',
                        text: 'Please wait.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    // For demo/prototype, we just redirect to a placeholder or stay here
                    // In real app, you'd submit a form or hit an endpoint
                    setTimeout(() => {
                        window.location.href = 'logout.php?action=' + type;
                    }, 1000);
                }
            });
        }
    </script>
</body>

</html>