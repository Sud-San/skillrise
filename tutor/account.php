<?php
require_once('includes/init.php');
include 'connection.php';

$tutor_id = (int) $_SESSION['tutor_id'];

// ── Fetch tutor core info ──────────────────────────────
$tutor_res = mysqli_query($conn, "SELECT * FROM tutor_tbl WHERE tutor_id = $tutor_id");
$tutor = mysqli_fetch_assoc($tutor_res);

// ── Fetch tutor profile ────────────────────────────────
$profile_res = mysqli_query($conn, "SELECT * FROM tutor_profile_tbl WHERE tutor_id = $tutor_id");
$profile = mysqli_fetch_assoc($profile_res);

// ── Fetch tutor details (education / certs) ────────────
$details_res = mysqli_query($conn, "SELECT * FROM tutor_details WHERE tutor_id = $tutor_id");

// ── Fetch active package ───────────────────────────────
// Only select from tutor_package_tbl to avoid unknown column errors
$pkg_res = mysqli_query($conn, "
    SELECT *
    FROM tutor_package_tbl
    WHERE tutor_id = $tutor_id AND payment_status = 1
    ORDER BY created_at DESC
    LIMIT 1
");
$package = mysqli_fetch_assoc($pkg_res);

// Helpers
$avatar = (!empty($profile['profile_pic']))
    ? $tutor_profile_path . $profile['profile_pic']
    : 'assets/images/user.png';
$full_name = htmlspecialchars($tutor['tutor_name'] ?? 'Tutor');
$email = htmlspecialchars($tutor['tutor_email'] ?? '—');
$phone = htmlspecialchars($tutor['tutor_phone'] ?? '—');
$bio = htmlspecialchars($profile['bio'] ?? '');
$expertise = htmlspecialchars($profile['expertise'] ?? '—');
$education = htmlspecialchars($profile['education'] ?? '—');
$experience = htmlspecialchars($profile['experience'] ?? '—');
$country = htmlspecialchars($profile['country'] ?? '—');
$languages = htmlspecialchars($profile['languages_known'] ?? '—');
$achievements = nl2br(htmlspecialchars($profile['achievements'] ?? ''));

$v_status = $tutor['verification_status'] ?? 'pending';

// FIX: Replace match() with PHP 7 compatible code
if ($v_status == 'approved') {
    $v_badge_cls = 'badge-verified';
    $v_icon = 'fa-circle-check';
} elseif ($v_status == 'rejected') {
    $v_badge_cls = 'badge-rejected';
    $v_icon = 'fa-circle-xmark';
} else {
    $v_badge_cls = 'badge-pending';
    $v_icon = 'fa-clock';
}

$pkg_days_left = 0;
if ($package) {
    $end = new DateTime($package['end_date']);
    $now = new DateTime();
    $diff = $now->diff($end);
    $pkg_days_left = ($diff->invert === 0) ? $diff->days : 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php'; ?>
    <link rel="stylesheet" href="assets/css/account.css">
</head>

<body class="app">
    <?php include 'includes/header.php'; ?>

    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">

                <!-- Page header -->
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <h1 class="page-title mb-0">My Account</h1>
                    <a href="edit_profile.php" class="btn-edit">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Profile
                    </a>
                </div>

                <div class="row g-4">

                    <!-- ══ COL 1: Profile + Contact ══ -->
                    <div class="col-12 col-lg-4">

                        <!-- Profile Hero Card -->
                        <div class="pcard mb-4">
                            <div class="hero-band"></div>
                            <div class="pcard-body pt-0">
                                <div class="d-flex align-items-flex-end gap-3 mb-3" style="margin-top:-8px;">
                                    <div class="avatar-wrap">
                                        <img src="<?= $avatar ?>" alt="<?= $full_name ?>">
                                        <a href="edit_profile.php#photo" class="edit-av">
                                            <i class="fa-solid fa-camera"></i>
                                        </a>
                                    </div>
                                    <div class="mt-2 pt-1">
                                        <h2 class="tutor-name"><?= $full_name ?></h2>
                                        <div class="tutor-email"><?= $email ?></div>
                                    </div>
                                </div>

                                <!-- Verification & status -->
                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    <span class="v-badge <?= $v_badge_cls ?>">
                                        <i class="fa-solid <?= $v_icon ?>"></i>
                                        <?= ucfirst($v_status) ?>
                                    </span>
                                    <?php if ($tutor['tutor_status'] == 1): ?>
                                        <span class="v-badge badge-verified">
                                            <i class="fa-solid fa-circle-dot"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="v-badge badge-rejected">
                                            <i class="fa-solid fa-circle-dot"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Bio -->
                                <?php if (!empty($bio)): ?>
                                    <div class="bio-box"><?= $bio ?></div>
                                <?php else: ?>
                                    <div class="bio-box empty">No bio added yet. <a href="edit_profile.php">Add one →</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Contact Card -->
                        <div class="pcard">
                            <div class="pcard-body">
                                <div class="sec-heading">Contact Info</div>
                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-envelope"></i> Email</div>
                                        <div class="info-value"><?= $email ?></div>
                                    </div>
                                    <div class="info-action"><a href="edit_profile.php">Change</a></div>
                                </div>
                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-phone"></i> Phone</div>
                                        <div class="info-value"><?= $phone ?></div>
                                    </div>
                                    <div class="info-action"><a href="edit_profile.php">Change</a></div>
                                </div>
                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-earth-asia"></i> Country</div>
                                        <div class="info-value"><?= $country ?></div>
                                    </div>
                                    <div class="info-action"><a href="edit_profile.php">Change</a></div>
                                </div>
                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-language"></i> Languages</div>
                                        <div class="info-value"><?= $languages ?></div>
                                    </div>
                                    <div class="info-action"><a href="edit_profile.php">Change</a></div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /col-1 -->


                    <!-- ══ COL 2: Professional Info + Education ══ -->
                    <div class="col-12 col-lg-5">

                        <!-- Professional -->
                        <div class="pcard mb-4">
                            <div class="pcard-body">
                                <div class="sec-heading">Professional Details</div>

                                <?php
                                // Stats row
                                $course_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM course_tbl WHERE tutor_id = $tutor_id"))['c'] ?? 0;
                                $note_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM course_notes as cn JOIN course_tbl as ct ON cn.course_id = ct.course_id WHERE ct.tutor_id = $tutor_id"))['c'] ?? 0;
                                ?>
                                <div class="stat-row">
                                    <div class="stat-chip">
                                        <div class="s-val"><?= $course_count ?></div>
                                        <div class="s-lbl">Courses</div>
                                    </div>
                                    <div class="stat-chip">
                                        <div class="s-val"><?= $note_count ?></div>
                                        <div class="s-lbl">Notes</div>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-star"></i> Expertise</div>
                                        <div class="info-value"><?= $expertise ?></div>
                                    </div>
                                    <div class="info-action"><a href="edit_profile.php">Edit</a></div>
                                </div>
                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-briefcase"></i> Experience</div>
                                        <div class="info-value"><?= $experience ?></div>
                                    </div>
                                    <div class="info-action"><a href="edit_profile.php">Edit</a></div>
                                </div>
                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-graduation-cap"></i> Education
                                        </div>
                                        <div class="info-value"><?= $education ?></div>
                                    </div>
                                    <div class="info-action"><a href="edit_profile.php">Edit</a></div>
                                </div>

                                <?php if (!empty($achievements)): ?>
                                    <div class="mt-3">
                                        <div class="info-label mb-2"><i class="fa-solid fa-trophy"></i> Achievements</div>
                                        <div class="ach-box"><?= $achievements ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Education & Certifications -->
                        <div class="pcard">
                            <div class="pcard-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="sec-heading mb-0">Education &amp; Certifications</div>
                                    <a href="edit_profile.php#education" class="btn-outline-edit"
                                        style="font-size:.75rem; padding:4px 12px;">
                                        <i class="fa-solid fa-plus"></i> Add
                                    </a>
                                </div>

                                <?php
                                $details_arr = [];
                                while ($d = mysqli_fetch_assoc($details_res))
                                    $details_arr[] = $d;

                                if (empty($details_arr)): ?>
                                    <div class="text-center py-3 text-muted" style="font-size:.85rem;">
                                        <i class="fa-solid fa-file-certificate fa-2x mb-2 d-block"
                                            style="color:#d1d5db;"></i>
                                        No education or certificates added yet.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($details_arr as $d): ?>

                                        <?php if (!empty($d['degree_name'])): ?>
                                            <div class="edu-item">
                                                <div class="edu-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                                                <div>
                                                    <div class="edu-title"><?= htmlspecialchars($d['degree_name']) ?></div>
                                                    <div class="edu-sub">
                                                        <?= htmlspecialchars($d['clg_name'] ?? '') ?>
                                                        <?php if (!empty($d['passing_year'])): ?>
                                                            · <?= htmlspecialchars($d['passing_year']) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($d['certificate_name'])): ?>
                                            <div class="edu-item">
                                                <div class="edu-icon"><i class="fa-solid fa-certificate"></i></div>
                                                <div>
                                                    <div class="edu-title"><?= htmlspecialchars($d['certificate_name']) ?></div>
                                                    <div class="edu-sub"><?= htmlspecialchars($d['institute_name'] ?? '') ?></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div><!-- /col-2 -->


                    <!-- ══ COL 3: Package + Security ══ -->
                    <div class="col-12 col-lg-3">

                        <!-- Active Package -->
                        <div class="pcard mb-4">
                            <div class="pcard-body">
                                <div class="sec-heading">Active Package</div>

                                <?php if ($package):
                                    $total_days = (new DateTime($package['start_date']))->diff(new DateTime($package['end_date']))->days;
                                    $used_days = max(0, $total_days - $pkg_days_left);
                                    $pct = $total_days > 0 ? min(100, round($used_days / $total_days * 100)) : 0;
                                    ?>
                                    <div class="pkg-card">
                                        <div class="pkg-name">Active Plan</div>
                                        <div class="pkg-amount">
                                            ₹<?= number_format($package['amount_paid'] ?? 0, 0) ?>
                                            <span>/ paid</span>
                                        </div>
                                        <div class="pkg-dates">
                                            <?= date('d M Y', strtotime($package['start_date'])) ?>
                                            → <?= date('d M Y', strtotime($package['end_date'])) ?>
                                        </div>
                                        <div class="pkg-bar-wrap">
                                            <div class="pkg-bar" style="width:<?= $pct ?>%"></div>
                                        </div>
                                        <div class="pkg-days">
                                            <?= $pkg_days_left > 0 ? "$pkg_days_left days remaining" : 'Expired' ?>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <a href="packages.php" class="btn-outline-edit">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Upgrade
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="no-pkg">
                                        <i class="fa-solid fa-box-open"></i>
                                        No active package.<br>
                                        <a href="packages.php" class="btn-edit mt-3 d-inline-flex">
                                            <i class="fa-solid fa-plus"></i> Get a Plan
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Security Card -->
                        <div class="pcard mb-4">
                            <div class="pcard-body">
                                <div class="sec-heading">Security</div>
                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-lock"></i> Password</div>
                                        <div class="info-value" style="letter-spacing:.12em;">••••••••</div>
                                    </div>
                                    <div class="info-action"><a href="change_password.php">Change</a></div>
                                </div>
                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-shield-halved"></i> 2FA</div>
                                        <div class="info-value" style="font-size:.82rem; color:#6b7280;">Not enabled
                                        </div>
                                    </div>
                                    <div class="info-action"><a href="security.php">Set up</a></div>
                                </div>
                                <div class="info-row">
                                    <div>
                                        <div class="info-label"><i class="fa-solid fa-calendar"></i> Joined</div>
                                        <div class="info-value">
                                            <?= !empty($tutor['created_at']) ? date('d M Y', strtotime($tutor['created_at'])) : '—' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick actions -->
                        <div class="pcard">
                            <div class="pcard-body">
                                <div class="sec-heading">Quick Actions</div>
                                <div class="d-flex flex-column gap-2">
                                    <a href="add_course.php" class="info-row text-decoration-none"
                                        style="padding:10px 0;">
                                        <div class="info-label"
                                            style="font-size:.85rem; color:var(--ink2); font-weight:600;">
                                            <i class="fa-solid fa-plus-circle"></i> Add New Course
                                        </div>
                                        <i class="fa-solid fa-chevron-right"
                                            style="color:#d1d5db; font-size:.75rem;"></i>
                                    </a>
                                    <a href="add_notes.php" class="info-row text-decoration-none"
                                        style="padding:10px 0;">
                                        <div class="info-label"
                                            style="font-size:.85rem; color:var(--ink2); font-weight:600;">
                                            <i class="fa-solid fa-file-arrow-up"></i> Upload Note
                                        </div>
                                        <i class="fa-solid fa-chevron-right"
                                            style="color:#d1d5db; font-size:.75rem;"></i>
                                    </a>
                                    <a href="add_video.php" class="info-row text-decoration-none"
                                        style="padding:10px 0; border-bottom:none;">
                                        <div class="info-label"
                                            style="font-size:.85rem; color:var(--ink2); font-weight:600;">
                                            <i class="fa-solid fa-video"></i> Upload Video
                                        </div>
                                        <i class="fa-solid fa-chevron-right"
                                            style="color:#d1d5db; font-size:.75rem;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div><!-- /col-3 -->

                </div><!-- /row -->
            </div>
        </div>
    </div>

    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>
    <?php include 'includes/script.php'; ?>
</body>

</html>