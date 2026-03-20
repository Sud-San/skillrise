<?php
require_once('includes/init.php');
include 'connection.php';

$tutor_id = $_SESSION['tutor_id'];

// Fetch lessons belonging to courses owned by this tutor
$lessons = mysqli_query($conn, "
    SELECT l.*, c.course_title 
    FROM lessons_tbl l
    JOIN course_tbl c ON l.course_id = c.course_id
    WHERE c.tutor_id = $tutor_id
    ORDER BY l.created_at DESC
");

// Handle lesson update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lesson_id']) && !isset($_POST['action'])) {
    $lessonId = (int) $_POST['lesson_id'];
    $title = mysqli_real_escape_string($conn, $_POST['lesson_title']);
    $duration = (int) $_POST['duration'];
    $content = mysqli_real_escape_string($conn, $_POST['content']);

    $updateQuery = "UPDATE lessons_tbl SET 
                    lesson_title = '$title', 
                    duration = $duration, 
                    content = '$content' 
                    WHERE lesson_id = $lessonId";

    if (mysqli_query($conn, $updateQuery)) {
        header("Location: lessondetail.php?msg=success");
    } else {
        header("Location: lessondetail.php?msg=error");
    }
    exit;
}

// Handle lesson delete via GET
if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    // Security check: ensure lesson belongs to this tutor
    $check = mysqli_query($conn, "SELECT l.lesson_id FROM lessons_tbl l JOIN course_tbl c ON l.course_id = c.course_id WHERE l.lesson_id = $deleteId AND c.tutor_id = $tutor_id");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM lessons_tbl WHERE lesson_id = $deleteId");
        header("Location: lessondetail.php?msg=success");
    } else {
        header("Location: lessondetail.php?msg=error");
    }
    exit;
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
        .section-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #718096;
            font-weight: 700;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 0.5rem;
        }

        .filter-btn {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            color: #4a5568;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .filter-btn:hover {
            background-color: #f7fafc;
            border-color: #cbd5e0;
        }

        .add-btn {
            background-color: #28a745;
            color: #ffffff;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .add-btn:hover {
            background-color: #218838;
            color: #ffffff;
        }
    </style>
</head>

<body class="app">
    <?php include 'includes/header.php' ?>

    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">
                <div class="row g-3 mb-4 align-items-center justify-content-between">
                    <div class="col-auto">
                        <h1 class="app-page-title mb-0">Lesson Details</h1>
                    </div>
                    <div class="col-auto">
                        <div class="page-utilities">
                            <div class="row g-2 justify-content-start justify-content-md-end align-items-center">
                                <div class="col-auto">
                                    <a class="add-btn" href="add_lesson.php">
                                        <i class="fa-solid fa-plus me-2"></i>Add New Lesson
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="lessonTable" class="table table-hover mb-0 text-left">
                                <thead class="table-light">
                                    <tr>
                                        <th class="cell">Sr No</th>
                                        <th class="cell">Lesson Title</th>
                                        <th class="cell">Course</th>
                                        <th class="cell">Duration (Min)</th>
                                        <th class="cell">Status</th>
                                        <th class="cell">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sr = 1;
                                    while ($row = mysqli_fetch_assoc($lessons)):
                                        ?>
                                        <tr>
                                            <td class="cell"><?= $sr++; ?></td>
                                            <td class="cell"><span class="truncate"><?= htmlspecialchars($row['lesson_title']); ?></span></td>
                                            <td class="cell"><?= htmlspecialchars($row['course_title']); ?></td>
                                            <td class="cell"><?= $row['duration']; ?></td>
                                            <td class="cell">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-switch" type="checkbox" 
                                                        data-lesson-id="<?= $row['lesson_id']; ?>"
                                                        <?= $row['status'] == 1 ? 'checked' : ''; ?>>
                                                </div>
                                            </td>
                                            <td class="cell">
                                                <button class="btn btn-sm btn-light edit-btn" 
                                                    data-id="<?= $row['lesson_id']; ?>"
                                                    data-title="<?= htmlspecialchars($row['lesson_title']); ?>"
                                                    data-duration="<?= $row['duration']; ?>"
                                                    data-content="<?= htmlspecialchars($row['content']); ?>">
                                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light delete-btn" data-id="<?= $row['lesson_id']; ?>">
                                                    <i class="fa-solid fa-trash text-danger"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="lessondetail.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Lesson</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="lesson_id" id="edit_lesson_id">
                        <div class="mb-3">
                            <label class="form-label">Lesson Title</label>
                            <input type="text" name="lesson_title" id="edit_lesson_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration (Minutes)</label>
                            <input type="number" name="duration" id="edit_duration" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" id="edit_content" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/script.php' ?>

    <script>
        // ============== SWEETALERT2 HELPER FUNCTIONS ==============
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        }

        $(document).ready(function () {
            const table = $('#lessonTable').DataTable({
                responsive: true
            });

            // Handle Edit Button
            $('.edit-btn').on('click', function () {
                const data = $(this).data();
                $('#edit_lesson_id').val(data.id);
                $('#edit_lesson_title').val(data.title);
                $('#edit_duration').val(data.duration);
                $('#edit_content').val(data.content);
                $('#editModal').modal('show');
            });

            // Handle Delete Button
            $('.delete-btn').on('click', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'lessondetail.php?delete_id=' + id;
                    }
                });
            });

            // Handle Status Toggle
            $(document).on('change', '.status-switch', function () {
                const $switch = $(this);
                const lessonId = $switch.data('lesson-id');
                const newStatus = $switch.is(':checked') ? 1 : 0;

                $.ajax({
                    url: 'lessondetail_status.php',
                    type: 'POST',
                    data: {
                        action: 'toggle_status',
                        lesson_id: lessonId,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            showSuccess(res.message);
                        } else {
                            showError(res.message);
                            $switch.prop('checked', !newStatus);
                        }
                    },
                    error: function () {
                        showError('Network error');
                        $switch.prop('checked', !newStatus);
                    }
                });
            });

            // PHP Side Messages
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'success'): ?>
                    showSuccess('Operation completed successfully!');
                <?php else: ?>
                    showError('Something went wrong!');
                <?php endif; ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>
