<?php
// ============================================================
//  AJAX HANDLERS — must be at top before any output/includes
// ============================================================
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if (empty($_SESSION['tutor_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
        exit;
    }

    $tutor_id = (int) $_SESSION['tutor_id'];
    header('Content-Type: application/json');

    // ── UPDATE BASIC INFO (name, email, phone) ────────────
    if ($_POST['action'] === 'update_basic') {
        $name = mysqli_real_escape_string($conn, trim($_POST['tutor_name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['tutor_email']));
        $phone = mysqli_real_escape_string($conn, trim($_POST['tutor_phone']));

        if (!$name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
            exit;
        }
        // Check email not taken by another tutor
        $chk = mysqli_query($conn, "SELECT tutor_id FROM tutor_tbl WHERE tutor_email = '$email' AND tutor_id != $tutor_id");
        if (mysqli_num_rows($chk) > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already in use by another account.']);
            exit;
        }
        $q = "UPDATE tutor_tbl SET tutor_name='$name', tutor_email='$email', tutor_phone='$phone' WHERE tutor_id=$tutor_id";
        echo mysqli_query($conn, $q)
            ? json_encode(['success' => true])
            : json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        exit;
    }

    // ── UPDATE PROFILE (bio, expertise, etc.) ─────────────
    if ($_POST['action'] === 'update_profile') {
        $bio = mysqli_real_escape_string($conn, trim($_POST['bio']));
        $expertise = mysqli_real_escape_string($conn, trim($_POST['expertise']));
        $education = mysqli_real_escape_string($conn, trim($_POST['education']));
        $experience = mysqli_real_escape_string($conn, trim($_POST['experience']));
        $achievements = mysqli_real_escape_string($conn, trim($_POST['achievements']));
        $country = mysqli_real_escape_string($conn, trim($_POST['country']));
        $languages = mysqli_real_escape_string($conn, trim($_POST['languages_known']));

        // Upsert tutor_profile_tbl
        $exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tutor_profile_id FROM tutor_profile_tbl WHERE tutor_id=$tutor_id"));
        if ($exists) {
            $q = "UPDATE tutor_profile_tbl SET
                    bio='$bio', expertise='$expertise', education='$education',
                    experience='$experience', achievements='$achievements',
                    country='$country', languages_known='$languages'
                  WHERE tutor_id=$tutor_id";
        } else {
            $q = "INSERT INTO tutor_profile_tbl
                    (tutor_id, bio, expertise, education, experience, achievements, country, languages_known)
                  VALUES
                    ($tutor_id,'$bio','$expertise','$education','$experience','$achievements','$country','$languages')";
        }
        echo mysqli_query($conn, $q)
            ? json_encode(['success' => true])
            : json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        exit;
    }

    // ── UPDATE PROFILE PICTURE ────────────────────────────
    if ($_POST['action'] === 'update_photo') {
        if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid image.']);
            exit;
        }
        $file = $_FILES['profile_pic'];
        $allowMime = ['image/jpeg', 'image/png', 'image/webp'];
        $allowExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);

        if (!in_array($ext, $allowExt) || !in_array($mime, $allowMime)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, WebP images allowed.']);
            exit;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Image must be under 3MB.']);
            exit;
        }

        $uploadDir = '../admin/assets/images/tutors/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);

        // Delete old photo
        $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT profile_pic FROM tutor_profile_tbl WHERE tutor_id=$tutor_id"));
        if ($old && !empty($old['profile_pic']) && file_exists($uploadDir . $old['profile_pic'])) {
            unlink($uploadDir . $old['profile_pic']);
        }

        $newName = 'tutor_' . $tutor_id . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {

            echo json_encode(['success' => false, 'message' => 'Failed to save image.']);
            exit;
        }

        $exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tutor_profile_id FROM tutor_profile_tbl WHERE tutor_id=$tutor_id"));
        if ($exists) {
            mysqli_query($conn, "UPDATE tutor_profile_tbl SET profile_pic='$newName' WHERE tutor_id=$tutor_id");
            $_SESSION['tutor_image'] = $newName;
        } else {
            mysqli_query($conn, "INSERT INTO tutor_profile_tbl (tutor_id, profile_pic) VALUES ($tutor_id,'$newName')");
        }
        echo json_encode(['success' => true, 'file' => $uploadDir . $newName]);
        exit;
    }

    // ── ADD / UPDATE EDUCATION ROW ────────────────────────
    if ($_POST['action'] === 'save_education') {
        $det_id = (int) $_POST['tutor_details_id'];
        $degree = mysqli_real_escape_string($conn, trim($_POST['degree_name']));
        $clg = mysqli_real_escape_string($conn, trim($_POST['clg_name']));
        $year = (int) $_POST['passing_year'];
        $cert = mysqli_real_escape_string($conn, trim($_POST['certificate_name']));
        $inst = mysqli_real_escape_string($conn, trim($_POST['institute_name']));

        // Handle degree image upload
        $degImgClause = '';
        $certImgClause = '';
        $uploadDir = 'assets/uploads/documents/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);

        if (isset($_FILES['degree_image']) && $_FILES['degree_image']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['degree_image'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $name = 'deg_' . $tutor_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], $uploadDir . $name)) {
                $degImgClause = ", degree_image='$name'";
            }
        }
        if (isset($_FILES['certificate_image']) && $_FILES['certificate_image']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['certificate_image'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $name = 'cert_' . $tutor_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], $uploadDir . $name)) {
                $certImgClause = ", certificate_image='$name'";
            }
        }

        if ($det_id > 0) {
            $q = "UPDATE tutor_details SET
                    degree_name='$degree', clg_name='$clg', passing_year=" . ($year ?: 'NULL') . ",
                    certificate_name='$cert', institute_name='$inst'
                    $degImgClause $certImgClause
                  WHERE tutor_details_id=$det_id AND tutor_id=$tutor_id";
        } else {
            $q = "INSERT INTO tutor_details
                    (tutor_id, degree_name, clg_name, passing_year, certificate_name, institute_name)
                  VALUES
                    ($tutor_id,'$degree','$clg'," . ($year ?: 'NULL') . ",'$cert','$inst')";
        }
        echo mysqli_query($conn, $q)
            ? json_encode(['success' => true, 'id' => mysqli_insert_id($conn)])
            : json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        exit;
    }

    // ── DELETE EDUCATION ROW ──────────────────────────────
    if ($_POST['action'] === 'delete_education') {
        $det_id = (int) $_POST['tutor_details_id'];
        echo mysqli_query($conn, "DELETE FROM tutor_details WHERE tutor_details_id=$det_id AND tutor_id=$tutor_id")
            ? json_encode(['success' => true])
            : json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}

// ============================================================
//  PAGE RENDER
// ============================================================
require_once('includes/init.php');

$tutor_id = (int) $_SESSION['tutor_id'];

$tutor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tutor_tbl WHERE tutor_id=$tutor_id"));
$profile = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tutor_profile_tbl WHERE tutor_id=$tutor_id"));

$details_res = mysqli_query($conn, "SELECT * FROM tutor_details WHERE tutor_id=$tutor_id ORDER BY tutor_details_id");
$details_arr = [];
while ($d = mysqli_fetch_assoc($details_res))
    $details_arr[] = $d;

$avatar = (!empty($profile['profile_pic']))
    ? $tutor_profile_path . $profile['profile_pic']
    : 'assets/images/user.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php'; ?>
    <link rel="stylesheet" href="assets/css/edit_profile.css">
</head>

<body class="app">
    <?php include 'includes/header.php'; ?>

    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <div>
                        <a href="account.php" class="btn-back mb-2">
                            <i class="fa-solid fa-arrow-left"></i> Back to Account
                        </a>
                        <h1 class="page-title mb-0">Edit Profile</h1>
                    </div>
                </div>

                <!-- Tab Nav -->
                <div class="edit-tabs">
                    <button class="edit-tab active" data-tab="basic">
                        <i class="fa-solid fa-user"></i> Basic Info
                    </button>
                    <button class="edit-tab" data-tab="profile">
                        <i class="fa-solid fa-id-card"></i> Professional
                    </button>
                    <button class="edit-tab" data-tab="education" id="tab-education-btn">
                        <i class="fa-solid fa-graduation-cap"></i> Education
                    </button>
                    <button class="edit-tab" data-tab="photo" id="tab-photo-btn">
                        <i class="fa-solid fa-camera"></i> Photo
                    </button>
                </div>

                <!-- ══ TAB: BASIC INFO ══ -->
                <div class="tab-section active" id="tab-basic">
                    <div class="pcard">
                        <div class="pcard-body">
                            <div class="sec-heading">Basic Information</div>
                            <form id="formBasic">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name <span class="req">*</span></label>
                                        <input type="text" name="tutor_name" class="form-control"
                                            value="<?= htmlspecialchars($tutor['tutor_name'] ?? '') ?>"
                                            placeholder="Your full name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address <span class="req">*</span></label>
                                        <input type="email" name="tutor_email" class="form-control"
                                            value="<?= htmlspecialchars($tutor['tutor_email'] ?? '') ?>"
                                            placeholder="your@email.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="tutor_phone" class="form-control"
                                            value="<?= htmlspecialchars($tutor['tutor_phone'] ?? '') ?>"
                                            placeholder="+91 XXXXX XXXXX">
                                    </div>
                                </div>
                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn-save" id="btnBasic">
                                        <i class="fa-solid fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ══ TAB: PROFESSIONAL ══ -->
                <div class="tab-section" id="tab-profile">
                    <div class="pcard">
                        <div class="pcard-body">
                            <div class="sec-heading">Professional Details</div>
                            <form id="formProfile">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Bio</label>
                                        <textarea name="bio" class="form-control" rows="4"
                                            placeholder="Write a short bio about yourself…"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                                        <div class="field-hint">Max 500 characters recommended.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Expertise / Skills</label>
                                        <input type="text" name="expertise" class="form-control"
                                            value="<?= htmlspecialchars($profile['expertise'] ?? '') ?>"
                                            placeholder="e.g. Python, Web Development, Data Science">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Experience</label>
                                        <input type="text" name="experience" class="form-control"
                                            value="<?= htmlspecialchars($profile['experience'] ?? '') ?>"
                                            placeholder="e.g. 5 Years">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Education (summary)</label>
                                        <input type="text" name="education" class="form-control"
                                            value="<?= htmlspecialchars($profile['education'] ?? '') ?>"
                                            placeholder="e.g. B.Tech Computer Science">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Country</label>
                                        <input type="text" name="country" class="form-control"
                                            value="<?= htmlspecialchars($profile['country'] ?? '') ?>"
                                            placeholder="e.g. India">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Languages Known</label>
                                        <input type="text" name="languages_known" class="form-control"
                                            value="<?= htmlspecialchars($profile['languages_known'] ?? '') ?>"
                                            placeholder="e.g. English, Hindi, Tamil">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Achievements</label>
                                        <textarea name="achievements" class="form-control" rows="3"
                                            placeholder="List your key achievements…"><?= htmlspecialchars($profile['achievements'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn-save" id="btnProfile">
                                        <i class="fa-solid fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ══ TAB: EDUCATION & CERTS ══ -->
                <div class="tab-section" id="tab-education">
                    <div class="pcard">
                        <div class="pcard-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="sec-heading mb-0">Education &amp; Certifications</div>
                            </div>

                            <div id="eduList">
                                <?php foreach ($details_arr as $idx => $d): ?>
                                    <div class="edu-row" data-id="<?= $d['tutor_details_id'] ?>">
                                        <button class="btn-delete-edu"
                                            onclick="deleteEdu(this, <?= $d['tutor_details_id'] ?>)">
                                            <i class="fa-solid fa-trash me-1"></i> Remove
                                        </button>
                                        <div class="edu-row-title">
                                            <i class="fa-solid fa-graduation-cap"></i>
                                            Entry #<?= $idx + 1 ?>
                                        </div>
                                        <input type="hidden" class="edu-id" value="<?= $d['tutor_details_id'] ?>">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Degree Name</label>
                                                <input type="text" class="form-control edu-degree"
                                                    value="<?= htmlspecialchars($d['degree_name'] ?? '') ?>"
                                                    placeholder="e.g. B.Tech Computer Science">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">College / University</label>
                                                <input type="text" class="form-control edu-clg"
                                                    value="<?= htmlspecialchars($d['clg_name'] ?? '') ?>"
                                                    placeholder="e.g. IIT Bombay">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Passing Year</label>
                                                <input type="number" class="form-control edu-year"
                                                    value="<?= htmlspecialchars($d['passing_year'] ?? '') ?>"
                                                    placeholder="e.g. 2020" min="1980" max="2099">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Certificate Name</label>
                                                <input type="text" class="form-control edu-cert"
                                                    value="<?= htmlspecialchars($d['certificate_name'] ?? '') ?>"
                                                    placeholder="e.g. AWS Certified">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Institute Name</label>
                                                <input type="text" class="form-control edu-inst"
                                                    value="<?= htmlspecialchars($d['institute_name'] ?? '') ?>"
                                                    placeholder="e.g. Coursera">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn-save" style="padding:7px 18px; font-size:.82rem;"
                                                onclick="saveEdu(this)">
                                                <i class="fa-solid fa-save"></i> Save Entry
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (empty($details_arr)): ?>
                                    <div id="emptyEdu" class="text-center py-4 text-muted" style="font-size:.88rem;">
                                        <i class="fa-solid fa-file-circle-plus fa-2x mb-2 d-block"
                                            style="color:#d1d5db;"></i>
                                        No education entries yet. Click below to add one.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button class="btn-add-edu" onclick="addEduRow()">
                                <i class="fa-solid fa-plus me-2"></i> Add Education / Certificate
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ══ TAB: PHOTO ══ -->
                <div class="tab-section" id="tab-photo">
                    <div class="pcard" style="max-width: 480px;">
                        <div class="pcard-body">
                            <div class="sec-heading">Profile Photo</div>
                            <div class="d-flex align-items-center gap-4 mb-4">
                                <div class="avatar-edit-wrap" onclick="document.getElementById('photoInput').click()">
                                    <img src="<?= $avatar ?>" alt="Profile" id="avatarPreview">
                                    <div class="avatar-overlay">
                                        <i class="fa-solid fa-camera"></i>
                                    </div>
                                </div>
                                <div>
                                    <div style="font-weight:600; color:var(--ink); margin-bottom:4px;">
                                        <?= htmlspecialchars($tutor['tutor_name'] ?? '') ?>
                                    </div>
                                    <div style="font-size:.8rem; color:var(--muted);">
                                        Click photo to change
                                    </div>
                                    <div class="field-hint mt-1">JPG, PNG, WebP · Max 3MB</div>
                                </div>
                            </div>
                            <input type="file" id="photoInput" accept="image/jpeg,image/png,image/webp">
                            <div id="photoPreviewWrap" style="display:none; margin-bottom:16px;">
                                <img id="photoPreviewImg"
                                    style="width:100%; border-radius:12px; max-height:200px; object-fit:cover;">
                            </div>
                            <button class="btn-save" id="btnPhoto" style="display:none;" onclick="uploadPhoto()">
                                <i class="fa-solid fa-upload"></i> Upload Photo
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast-bar" id="toastBar">
        <i class="fa-solid fa-circle-check"></i>
        <span id="toastMsg">Saved successfully!</span>
    </div>

    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <?php include 'includes/script.php'; ?>

    <script>
        // ── Tab switching ───────────────────────────────────────────
        document.querySelectorAll('.edit-tab').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.edit-tab').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('tab-' + this.dataset.tab).classList.add('active');
            });
        });

        // Auto-open tab from URL hash
        const hash = location.hash.replace('#', '');
        if (hash) {
            const btn = document.querySelector(`.edit-tab[data-tab="${hash}"]`);
            if (btn) btn.click();
        }

        // ── Toast helper ────────────────────────────────────────────
        function showToast(msg, type = 'success') {
            const t = document.getElementById('toastBar');
            const i = t.querySelector('i');
            document.getElementById('toastMsg').textContent = msg;
            t.className = 'toast-bar show ' + type;
            i.className = type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark';
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        // ── AJAX helper ─────────────────────────────────────────────
        function ajaxPost(formData, btnEl, successMsg) {
            if (btnEl) { btnEl.disabled = true; btnEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…'; }
            fetch('edit_profile.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = '<i class="fa-solid fa-save"></i> Save Changes'; }
                    if (res.success) {
                        showToast(successMsg || 'Saved successfully!', 'success');
                    } else {
                        showToast(res.message || 'Something went wrong.', 'error');
                    }
                })
                .catch(() => {
                    if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = '<i class="fa-solid fa-save"></i> Save Changes'; }
                    showToast('Server error. Please try again.', 'error');
                });
        }

        // ── Basic info form ─────────────────────────────────────────
        document.getElementById('formBasic').addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'update_basic');
            ajaxPost(fd, document.getElementById('btnBasic'), 'Basic info updated!');
        });

        // ── Profile form ────────────────────────────────────────────
        document.getElementById('formProfile').addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'update_profile');
            ajaxPost(fd, document.getElementById('btnProfile'), 'Professional details saved!');
        });

        // ── Photo upload ────────────────────────────────────────────
        document.getElementById('photoInput').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('photoPreviewImg').src = e.target.result;
                document.getElementById('photoPreviewWrap').style.display = 'block';
                document.getElementById('btnPhoto').style.display = 'inline-flex';
            };
            reader.readAsDataURL(file);
        });

        function uploadPhoto() {
            const fileInput = document.getElementById('photoInput');
            if (!fileInput.files.length) return;
            const fd = new FormData();
            fd.append('action', 'update_photo');
            fd.append('profile_pic', fileInput.files[0]);
            const btn = document.getElementById('btnPhoto');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading…';
            fetch('edit_profile.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-upload"></i> Upload Photo';
                    if (res.success) {
                        document.getElementById('avatarPreview').src = res.file + '?t=' + Date.now();
                        showToast('Profile photo updated!', 'success');
                        document.getElementById('photoPreviewWrap').style.display = 'none';
                        btn.style.display = 'none';
                    } else {
                        showToast(res.message || 'Upload failed.', 'error');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-upload"></i> Upload Photo';
                    showToast('Server error.', 'error');
                });
        }

        // ── Education rows ──────────────────────────────────────────
        let eduCount = <?= count($details_arr) ?>;

        function addEduRow() {
            eduCount++;
            document.getElementById('emptyEdu')?.remove();
            const html = `
    <div class="edu-row" data-id="0">
        <button class="btn-delete-edu" onclick="this.closest('.edu-row').remove()">
            <i class="fa-solid fa-trash me-1"></i> Remove
        </button>
        <div class="edu-row-title"><i class="fa-solid fa-graduation-cap"></i> New Entry</div>
        <input type="hidden" class="edu-id" value="0">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Degree Name</label>
                <input type="text" class="form-control edu-degree" placeholder="e.g. B.Tech Computer Science">
            </div>
            <div class="col-md-6">
                <label class="form-label">College / University</label>
                <input type="text" class="form-control edu-clg" placeholder="e.g. IIT Bombay">
            </div>
            <div class="col-md-4">
                <label class="form-label">Passing Year</label>
                <input type="number" class="form-control edu-year" placeholder="e.g. 2020" min="1980" max="2099">
            </div>
            <div class="col-md-4">
                <label class="form-label">Certificate Name</label>
                <input type="text" class="form-control edu-cert" placeholder="e.g. AWS Certified">
            </div>
            <div class="col-md-4">
                <label class="form-label">Institute Name</label>
                <input type="text" class="form-control edu-inst" placeholder="e.g. Coursera">
            </div>
        </div>
        <div class="mt-3">
            <button class="btn-save" style="padding:7px 18px; font-size:.82rem;" onclick="saveEdu(this)">
                <i class="fa-solid fa-save"></i> Save Entry
            </button>
        </div>
    </div>`;
            document.getElementById('eduList').insertAdjacentHTML('beforeend', html);
        }

        function saveEdu(btn) {
            const row = btn.closest('.edu-row');
            const det_id = row.querySelector('.edu-id').value;
            const fd = new FormData();
            fd.append('action', 'save_education');
            fd.append('tutor_details_id', det_id);
            fd.append('degree_name', row.querySelector('.edu-degree').value);
            fd.append('clg_name', row.querySelector('.edu-clg').value);
            fd.append('passing_year', row.querySelector('.edu-year').value);
            fd.append('certificate_name', row.querySelector('.edu-cert').value);
            fd.append('institute_name', row.querySelector('.edu-inst').value);

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';

            fetch('edit_profile.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-save"></i> Save Entry';
                    if (res.success) {
                        // Update the hidden ID if this was a new row
                        if (det_id == 0 && res.id) {
                            row.querySelector('.edu-id').value = res.id;
                            row.dataset.id = res.id;
                            row.querySelector('.btn-delete-edu').setAttribute('onclick', `deleteEdu(this, ${res.id})`);
                        }
                        showToast('Education entry saved!', 'success');
                    } else {
                        showToast(res.message || 'Save failed.', 'error');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-save"></i> Save Entry';
                    showToast('Server error.', 'error');
                });
        }

        function deleteEdu(btn, detId) {
            if (!confirm('Remove this entry?')) return;
            if (detId == 0) { btn.closest('.edu-row').remove(); return; }
            const fd = new FormData();
            fd.append('action', 'delete_education');
            fd.append('tutor_details_id', detId);
            fetch('edit_profile.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        btn.closest('.edu-row').remove();
                        showToast('Entry removed.', 'success');
                    } else {
                        showToast(res.message || 'Delete failed.', 'error');
                    }
                });
        }
    </script>
</body>

</html>