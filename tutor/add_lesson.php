<?php
require_once('includes/init.php');
require_once('includes/package_check.php');
include 'connection.php';

$tutor_id = $_SESSION['tutor_id'];
$insertSuccess = false;
$insertError = '';

// Fetch tutor's courses for the dropdown
$courses = mysqli_query($conn, "SELECT course_id, course_title, category_id FROM course_tbl WHERE tutor_id = $tutor_id");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int) $_POST['course_id'];
    $title = mysqli_real_escape_string($conn, $_POST['lesson_title']);
    $duration = null;
    $content = mysqli_real_escape_string($conn, $_POST['content']);

    // Get category_id from the selected course
    $catQuery = mysqli_query($conn, "SELECT category_id FROM course_tbl WHERE course_id = $course_id");
    $catRow = mysqli_fetch_assoc($catQuery);
    $category_id = $catRow['category_id'] ?? 0;

    $query = "INSERT INTO lessons_tbl (course_id, category_id, lesson_title, content, status, created_at) 
              VALUES ($course_id, $category_id, '$title', '$content', 1, NOW())";

    if (mysqli_query($conn, $query)) {
        $insertSuccess = true;
    } else {
        $insertError = mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php' ?>
    <!-- SweetAlert2 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <style>
        .card-header {
            background-color: #ffffff;
            border-bottom: 2px solid #f0f2f5;
        }

        .btn-save {
            background-color: #28a745;
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }

        .btn-save:hover {
            background-color: #218838;
        }

        .btn-cancel {
            background-color: #f8f9fa;
            color: #4a5568;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>

<body class="app">
    <?php include 'includes/header.php' ?>

    <div class="app-wrapper">
        <div class="app-content pt-4 p-md-3 p-lg-4">
            <div class="container-xl">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header p-4">
                        <h4 class="h3 mb-0">Add New Lesson</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="add_lesson.php">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Select Course</label>
                                    <select name="course_id" class="form-select" required>
                                        <option value="">-- Select Course --</option>
                                        <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                                            <option value="<?= $c['course_id']; ?>">
                                                <?= htmlspecialchars($c['course_title']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Lesson Title</label>
                                    <input type="text" name="lesson_title" class="form-control"
                                        placeholder="e.g. Introduction to Variables" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Lesson Content</label>
                                    <textarea name="content" class="form-control" rows="10"
                                        placeholder="Enter lesson notes or content here..."></textarea>
                                </div>
                            </div>

                            <div class="mt-5 d-flex gap-3">
                                <button type="submit" class="btn-save">
                                    <i class="fa-solid fa-save me-2"></i>Create Lesson
                                </button>
                                <a href="lessondetail.php" class="btn-cancel">
                                    <i class="fa-solid fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/script.php' ?>

    <script>
        $(document).ready(function () {
            <?php if ($insertSuccess): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Lesson Added!',
                    text: 'The lesson has been successfully created.',
                    showCancelButton: true,
                    confirmButtonText: 'View Lessons',
                    cancelButtonText: 'Add Another'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'lessondetail.php';
                    } else {
                        window.location.href = 'add_lesson.php';
                    }
                });
            <?php elseif ($insertError): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?= addslashes($insertError); ?>'
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>