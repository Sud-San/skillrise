<?php
require_once('includes/init.php');
include 'connection.php'; // Your DB connection
include 'includes/headtag.php';

$insertSuccess = false;
$insertError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $category_id = $_POST['category_id'];
    $tutor_id = $_SESSION['tutor_id'] ?? 0;   // always read from session — disabled fields are not submitted
    $course_title = $_POST['course_title'];
    $course_slug = $_POST['course_slug'];
    $course_desc = $_POST['course_description'];
    $course_level = $_POST['course_level'];
    $price = $_POST['price'];
    $total_lesson = $_POST['total_lesson'];

    /* ---------- THUMBNAIL UPLOAD ---------- */
    $uploadDir = '../assets/images/thumbnail/';

    if (!is_dir($uploadDir)) {
        $insertError = 'Upload folder not found.';
    } else {
        $fileName = time() . '_' . basename($_FILES['course_thumbnail']['name']);
        $tmpName = $_FILES['course_thumbnail']['tmp_name'];
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            $insertError = 'Image upload failed. Please try again.';
        } else {
            /* ---------- SLUG DUPLICATE CHECK ---------- */
            $slug = mysqli_real_escape_string($conn, $_POST['course_slug']);
            $check = mysqli_query($conn, "SELECT course_id FROM course_tbl WHERE course_slug='$slug'");

            if (mysqli_num_rows($check) > 0) {
                $insertError = 'Course slug already exists. Please use a unique slug.';
            } else {
                /* ---------- INSERT ---------- */
                $query = "INSERT INTO course_tbl 
                    (tutor_id, category_id, course_title, course_slug, course_description, 
                     course_thumbnail, course_level, price, total_lesson, course_status, created_at)
                    VALUES
                    ('$tutor_id', '$category_id', '$course_title', '$slug', '$course_desc',
                     '$fileName', '$course_level', '$price', '$total_lesson', 1, NOW())";

                if (mysqli_query($conn, $query)) {
                    $insertSuccess = true;
                } else {
                    $insertError = 'Database error: ' . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <!-- SweetAlert2 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <link rel="stylesheet" href="assets/css/add_course.css">

</head>

<body class="app">
    <?php include 'includes/header.php'; ?>

    <div class="app-wrapper">
        <div class="app-content pt-4 p-md-3 p-lg-4">
            <div class="container-xl">
                <div class="card shadow-sm border-0 rounded-4">

                    <div class="card-header bg-white">
                        <h4 class="h3 mb-0">
                            <i class="fa-solid fa-plus-circle me-2" style="color:#3a7d44;"></i>
                            Add New Course
                        </h4>
                    </div>

                    <div class="card-body px-4 py-4">
                        <form id="addCourseForm" method="POST" enctype="multipart/form-data">
                            <div class="row g-4">

                                <!-- ── COURSE META ─────────────────────────── -->
                                <div class="col-12">
                                    <div class="section-label">Course Information</div>
                                </div>

                                <!-- Category -->
                                <div class="col-md-6">
                                    <label for="categoryId" class="form-label">
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="categoryId" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php
                                        $catQuery = mysqli_query($conn, "SELECT category_id, category_name FROM category_tbl");
                                        while ($cat = mysqli_fetch_assoc($catQuery)) {
                                            echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Tutor -->
                                <div class="col-md-6">
                                    <label for="tutorId" class="form-label">
                                        Tutor <span class="text-danger">*</span>
                                    </label>
                                    <input type="hidden" name="tutor_id"
                                        value="<?php echo intval($_SESSION['tutor_id']); ?>">
                                    <select class="form-select" id="tutorId" disabled>
                                        <!-- <option value="">Select Tutor</option> -->
                                        <?php
                                        $tutorQuery = mysqli_query($conn, "SELECT tutor_id, tutor_name FROM tutor_tbl where tutor_status = 1 and tutor_id = {$_SESSION['tutor_id']}");
                                        while ($t = mysqli_fetch_assoc($tutorQuery)) {
                                            echo "<option selected value='{$t['tutor_id']}'>{$t['tutor_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Course Title -->
                                <div class="col-md-6">
                                    <label for="courseTitle" class="form-label">
                                        Course Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="courseTitle" name="course_title"
                                        placeholder="e.g. Introduction to Python" required>
                                </div>

                                <!-- Course Slug -->
                                <div class="col-md-6">
                                    <label for="courseSlug" class="form-label">
                                        Course Slug <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="courseSlug" name="course_slug"
                                        placeholder="e.g. intro-to-python" required>
                                    <div class="form-text text-muted" style="font-size:0.78rem;">
                                        Must be unique. Use lowercase letters and hyphens only.
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-md-12">
                                    <label for="courseDescription" class="form-label">
                                        Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="courseDescription" name="course_description"
                                        rows="4" placeholder="Write a short but compelling course description…"
                                        required></textarea>
                                </div>

                                <!-- ── MEDIA & SETTINGS ────────────────────── -->
                                <div class="col-12 mt-2">
                                    <div class="section-label">Media &amp; Settings</div>
                                </div>

                                <!-- Thumbnail -->
                                <div class="col-md-12">
                                    <label class="form-label">Thumbnail Image</label>
                                    <input type="file" name="course_thumbnail" class="form-control" accept="image/*">
                                    <div class="form-text text-muted" style="font-size:0.78rem;">
                                        Recommended size: 1280×720px (JPG, PNG, WebP)
                                    </div>
                                </div>

                                <!-- Level -->
                                <div class="col-md-4">
                                    <label for="courseLevel" class="form-label">Level</label>
                                    <select class="form-select" id="courseLevel" name="course_level">
                                        <option value="">Select Level</option>
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>

                                <!-- Price -->
                                <div class="col-md-4">
                                    <label for="coursePrice" class="form-label">Price (₹)</label>
                                    <input type="number" class="form-control" id="coursePrice" name="price" min="0"
                                        step="1" placeholder="0">
                                </div>

                                <!-- Total Lessons -->
                                <div class="col-md-4">
                                    <label for="totalLessons" class="form-label">Total Lessons</label>
                                    <input type="number" class="form-control" id="totalLessons" name="total_lesson"
                                        min="0" placeholder="0">
                                </div>

                            </div><!-- /row -->

                            <!-- ── ACTION BUTTONS ──────────────────────────── -->
                            <div class="mt-5 d-flex gap-3">
                                <button type="submit" class="btn-save">
                                    <i class="fa-solid fa-save"></i> Add Course
                                </button>
                                <a href="coursedetail.php" class="btn-cancel">
                                    <i class="fa-solid fa-times"></i> Cancel
                                </a>
                            </div>

                        </form>
                    </div><!-- /card-body -->
                </div><!-- /card -->
            </div>
        </div>
    </div>
    <!-- Javascript -->
    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <?php include 'includes/script.php'; ?>

    <script>
        // ─── SweetAlert2 helpers — decent, eye-comfortable colours ────────

        function showToastSuccess(msg) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: msg,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                background: '#fff',
                iconColor: '#3a7d44',           /* decent muted green — not glaring */
                customClass: { popup: 'shadow-lg' }
            });
        }

        function showToastError(msg) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: msg,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true,
                background: '#fff',
                iconColor: '#c0392b',           /* decent muted red */
                customClass: { popup: 'shadow-lg' }
            });
        }

        function showToastWarning(msg) {
            Swal.fire({
                icon: 'warning',
                title: 'Hold on!',
                text: msg,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#fff',
                iconColor: '#d68910',           /* decent muted amber */
                customClass: { popup: 'shadow-lg' }
            });
        }

        // ─── PHP-driven alerts ─────────────────────────────────────────────
        <?php if ($insertSuccess): ?>
            Swal.fire({
                icon: 'success',
                title: '🎉 Course Added!',
                html: '<p style="color:#4a5568;font-size:0.95rem;">The course has been successfully added and is now <strong style="color:#3a7d44;">live</strong>.</p>',
                confirmButtonText: 'Go to Courses',
                confirmButtonColor: '#3a7d44',      /* decent muted green */
                showCancelButton: true,
                cancelButtonText: 'Add Another',
                cancelButtonColor: '#c0392b',       /* decent muted red   */
                reverseButtons: true,
                customClass: {
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'coursedetail.php';
                }
            });
        <?php elseif (!empty($insertError)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Something went wrong',
                text: '<?= addslashes($insertError); ?>',
                confirmButtonText: 'Try Again',
                confirmButtonColor: '#c0392b',      /* decent muted red */
                customClass: { confirmButton: 'swal2-confirm' }
            });
        <?php endif; ?>

        // ─── Client-side form validation ──────────────────────────────────
        document.getElementById("addCourseForm").addEventListener("submit", function (e) {

            let categoryId = document.getElementById("categoryId").value;
            let tutorId = document.getElementById("tutorId").value;
            let title = document.getElementById("courseTitle").value.trim();
            let slug = document.getElementById("courseSlug").value.trim();
            let desc = document.getElementById("courseDescription").value.trim();
            let price = document.getElementById("coursePrice").value;
            let lessons = document.getElementById("totalLessons").value;

            if (categoryId === "") {
                e.preventDefault();
                showToastWarning("Please select a Category.");
                document.getElementById("categoryId").focus();
                return;
            }
            if (tutorId === "") {
                e.preventDefault();
                showToastWarning("Please select a Tutor.");
                document.getElementById("tutorId").focus();
                return;
            }
            if (title === "") {
                e.preventDefault();
                showToastWarning("Course title is required.");
                document.getElementById("courseTitle").focus();
                return;
            }
            if (slug === "") {
                e.preventDefault();
                showToastWarning("Course slug is required.");
                document.getElementById("courseSlug").focus();
                return;
            }
            if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
                e.preventDefault();
                showToastWarning("Slug must contain only lowercase letters, numbers, and hyphens.");
                document.getElementById("courseSlug").focus();
                return;
            }
            if (desc === "") {
                e.preventDefault();
                showToastWarning("Course description is required.");
                document.getElementById("courseDescription").focus();
                return;
            }
            if (price !== "" && parseFloat(price) < 0) {
                e.preventDefault();
                showToastWarning("Price cannot be negative.");
                document.getElementById("coursePrice").focus();
                return;
            }
            if (lessons !== "" && parseInt(lessons) < 0) {
                e.preventDefault();
                showToastWarning("Total lessons cannot be negative.");
                document.getElementById("totalLessons").focus();
                return;
            }

            // All good — loading state
            let btn = document.querySelector(".btn-save");
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
        });

        // ─── Auto-generate slug from title ────────────────────────────────
        document.getElementById("courseTitle").addEventListener("input", function () {
            let slugField = document.getElementById("courseSlug");
            if (slugField.dataset.manual !== "true") {
                slugField.value = this.value
                    .toLowerCase().trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            }
        });
        document.getElementById("courseSlug").addEventListener("input", function () {
            this.dataset.manual = "true";
        });
    </script>

</body>

</html>