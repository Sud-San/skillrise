<?php
require_once('includes/init.php');
include 'connection.php';

$tutor_id = $_SESSION['tutor_id'];
$videos = mysqli_query($conn, "
    SELECT v.*, c.course_title, l.lesson_title 
    FROM videos_tbl v
    LEFT JOIN course_tbl c ON v.course_id = c.course_id
    LEFT JOIN lessons_tbl l ON v.lesson_id = l.lesson_id
    WHERE v.tutor_id = $tutor_id
    ORDER BY v.uploaded_at DESC
");



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {

    $id = (int) $_POST['course_id'];

    $title = mysqli_real_escape_string(
        $conn,
        isset($_POST['title']) ? $_POST['title'] : ''
    );

    $description = mysqli_real_escape_string(
        $conn,
        isset($_POST['description']) ? $_POST['description'] : ''
    );

    $level = mysqli_real_escape_string(
        $conn,
        isset($_POST['level']) ? $_POST['level'] : ''
    );

    $lesson = isset($_POST['lesson']) ? (int) $_POST['lesson'] : 0;
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;

    $query = "
        UPDATE course_tbl SET
            course_title       = '$title',
            course_description = '$description',
            course_level       = '$level',
            total_lesson       = $lesson,
            price              = $price
        WHERE course_id = $id
    ";

    if (!mysqli_query($conn, $query)) {
        die("Update failed: " . mysqli_error($conn));
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

    <link rel="stylesheet" href="assets/css/videodetail.css">

</head>

<body class="app">

    <?php include 'includes/header.php' ?>

    <div class="app-wrapper">

        <div class="app-content pt-2 p-md-3 p-lg-4">
            <div class="container-xl">

                <div class="row g-3 mb-4 align-items-center justify-content-between">
                    <div class="col-auto">
                    </div>


                    <!-- Main Content -->
                    <div class="main-content">

                        <div class="mt-1">

                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-header bg-white pt-3 pb-3 border-bottom">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h4 class="h3 mb-0">Video Details</h4>
                                        </div>
                                        <div class="col-auto">

                                            <!-- Add Order Button -->
                                            <a href="add_video.php" class="btn add-order-btn">
                                                <i class="fa-solid fa-plus"></i>&nbsp;&nbsp;Add New Video
                                            </a>

                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="card-body">
                                <div class="table-responsive">

                                    <table id="datatable"
                                        class="table table-bordered table-hover align-middle text-center"
                                        style="width:100%">
                                        <thead class="table-light text-uppercase">
                                            <tr>
                                                <th>Course</th>
                                                <th>Lesson (Video Title)</th>
                                                <th>Video</th>
                                                <th>Uploaded At</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($videos)) { ?>
                                                <tr>
                                                    <!-- COURSE -->
                                                    <td class="text-start">
                                                        <div class="user-cell">
                                                            <span class="user-name">
                                                                <?= htmlspecialchars($row['course_title']); ?>
                                                            </span>
                                                        </div>
                                                    </td>

                                                    <!-- LESSON / TITLE -->
                                                    <td class="text-start">
                                                        <span
                                                            class="font-bold"><?= htmlspecialchars($row['lesson_title']); ?></span>
                                                    </td>

                                                    <!-- VIDEO URL -->
                                                    <td class="text-start">
                                                        <div class="text-xs text-muted truncate max-w-[250px]"
                                                            title="<?= htmlspecialchars($row['video_url']); ?>">
                                                            <video width="300px" controls>
                                                                <source src="../videos/<?= $row['video_url']; ?>">
                                                            </video>
                                                        </div>
                                                    </td>

                                                    <!-- UPLOADED AT -->
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            <?= date("d M Y, H:i", strtotime($row['uploaded_at'])); ?>
                                                        </span>
                                                    </td>

                                                    <!-- STATUS -->
                                                    <td>
                                                        <div class="form-check form-switch custom-switch">
                                                            <input class="form-check-input status-switch" type="checkbox"
                                                                data-video-id="<?= $row['video_id']; ?>"
                                                                <?= $row['video_status'] == 1 ? 'checked' : ''; ?>>
                                                            <label class="form-check-label"></label>
                                                        </div>
                                                    </td>

                                                    <!-- ACTION -->
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <a href="#" class="edit-btn text-primary"
                                                                data-id="<?= $row['video_id']; ?>"
                                                                data-course-id="<?= $row['course_id']; ?>"
                                                                data-lesson-id="<?= $row['lesson_id']; ?>"
                                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                                title="Edit Video">
                                                                <i class="fa-solid fa-pen"></i>
                                                            </a>
                                                            <a href="#" class="delete-btn text-danger"
                                                                data-id="<?= $row['video_id']; ?>" title="Delete Video">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--//tab-content-->
        </div><!--//container-fluid-->
    </div><!--//app-content-->
    </div><!--//app-wrapper-->

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel"><i class="fa-solid fa-edit me-2"></i>Edit Video Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editVideoForm" enctype="multipart/form-data">
                        <input type="hidden" id="editVideoId" name="video_id">

                        <div class="row g-3">
                            <!-- Course Name -->
                            <div class="col-md-6">
                                <label class="form-label">Course <span class="text-danger">*</span></label>
                                <select name="course_id" class="form-select" id="editCourseId" required>
                                    <option value="">Select Course</option>
                                    <?php
                                    $courseQuery = mysqli_query($conn, "SELECT course_id, course_title FROM course_tbl WHERE tutor_id = $tutor_id");
                                    while ($course = mysqli_fetch_assoc($courseQuery)) {
                                        echo "<option value='{$course['course_id']}'>{$course['course_title']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Lesson -->
                            <div class="col-md-6">
                                <label class="form-label">Lesson <span class="text-danger">*</span></label>
                                <select name="lesson_id" class="form-select" id="editLessonId" required>
                                    <option value="">First select course...</option>
                                </select>
                            </div>

                            <!-- Video File -->
                            <div class="col-md-12">
                                <label class="form-label">Replace Video File (Optional)</label>
                                <input type="file" name="video_file" class="form-control" id="editVideoFile"
                                    accept="video/*">
                                <small class="text-muted">Leave blank to keep current local video</small>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i
                            class="fa-solid fa-times me-2"></i>Cancel</button>
                    <button type="button" class="btn btn-success" id="saveVideoChanges"
                        style="background-color: #28a745; border-color: #28a745;">
                        <i class="fa-solid fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>







    <!-- Javascript -->
    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

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


            // DataTable init
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }

            let table = $('#datatable').DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                pageLength: 10,
                dom: "<'row mb-3'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend: 'copy', className: 'btn btn-sm btn-outline-default me-1' },
                    { extend: 'csv', className: 'btn btn-sm btn-outline-default me-1' },
                    { extend: 'excel', className: 'btn btn-sm btn-outline-default me-1' },
                    { extend: 'pdf', className: 'btn btn-sm btn-outline-default me-1' },
                    { extend: 'print', className: 'btn btn-sm btn-outline-default me-1' },
                    { extend: 'colvis', className: 'btn btn-sm btn-outline-default' }
                ],
                language: {
                    paginate: {
                        previous: "<i class='fa-solid fa-angle-left'></i>",
                        next: "<i class='fa-solid fa-angle-right'></i>"
                    }
                }
            });

            // Filter dropdown functionality
            $('.filter-option').on('click', function (e) {
                e.preventDefault();

                // Remove active class from all options
                $('.filter-option').removeClass('active');

                // Add active class to clicked option
                $(this).addClass('active');

                // Get filter value
                currentFilter = $(this).data('filter');

                // Redraw table
                table.draw();
            });

            // Update status via AJAX when toggle is clicked
            $(document).on('change', '.status-switch', function () {
                const $switch = $(this);
                const videoId = $switch.data('video-id');
                const newStatus = $switch.is(':checked') ? 1 : 0;

                $.ajax({
                    url: 'videodetail_status.php', // Current page handles toggle
                    type: 'POST',
                    data: {
                        action: 'toggle_status',
                        video_id: videoId,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            showSuccess(res.message);
                            // No need to redraw if result is client-side table, but let's draw filter
                            table.draw();
                        } else {
                            showError(res.message);
                            $switch.prop('checked', !newStatus);
                        }
                    },
                    error: function () {
                        showError('Network error while updating status');
                        $switch.prop('checked', !newStatus);
                    }
                });
            });

            // ============== EDIT MODAL LOGIC ==============
            let currentLessonId = 0;

            $(document).on('click', '.edit-btn', function () {
                const videoId = $(this).data('id');
                const courseId = $(this).data('course-id');
                currentLessonId = $(this).data('lesson-id');

                $('#editVideoId').val(videoId);
                $('#editCourseId').val(courseId).trigger('change');
            });

            // Load lessons when course changes in EDIT modal
            $('#editCourseId').on('change', function () {
                const courseId = $(this).val();
                const lessonSelect = $('#editLessonId');

                if (!courseId) {
                    lessonSelect.html('<option value="">First select course...</option>');
                    return;
                }

                $.ajax({
                    url: 'ajax/get_lessons.php',
                    type: 'GET',
                    data: { course_id: courseId },
                    dataType: 'json',
                    success: function (lessons) {
                        let html = '<option value="">Select Lesson</option>';
                        lessons.forEach(lesson => {
                            const selected = (lesson.lesson_id == currentLessonId) ? 'selected' : '';
                            html += `<option value="${lesson.lesson_id}" ${selected}>${lesson.lesson_title}</option>`;
                        });
                        lessonSelect.html(html);
                    }
                });
            });

            // SAVE VIDEO CHANGES
            $('#saveVideoChanges').on('click', function () {
                const form = $('#editVideoForm')[0];
                const formData = new FormData(form);

                $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');

                $.ajax({
                    url: 'ajax/update_video.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        try {
                            const data = JSON.parse(res);
                            if (data.status === 'success') {
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        } catch (e) {
                            alert('Unexpected response from server');
                        }
                    },
                    complete: function () {
                        $('#saveVideoChanges').prop('disabled', false).html('<i class="fa-solid fa-save me-2"></i>Save Changes');
                    }
                });
            });

            // DELETE VIDEO
            $(document).on('click', '.delete-btn', function (e) {
                e.preventDefault();
                const videoId = $(this).data('id');

                if (confirm('Are you sure you want to delete this video? This action cannot be undone.')) {
                    $.ajax({
                        url: 'ajax/delete_video.php',
                        type: 'POST',
                        data: { video_id: videoId },
                        success: function (res) {
                            try {
                                const data = JSON.parse(res);
                                if (data.status === 'success') {
                                    location.reload();
                                } else {
                                    alert('Error: ' + data.message);
                                }
                            } catch (e) {
                                alert('Unexpected response');
                            }
                        }
                    });
                }
            });
        });


    </script>
    <script>
        $(document).ready(function () {
            // ============== CHECK FOR PHP-SIDE SUCCESS/ERROR MESSAGES ==============
            // Show SweetAlert if redirected back with ?msg= param
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'success'): ?>
                    $(document).ready(function () {
                        showSuccess('Operation completed successfully!');
                    });
                <?php elseif ($_GET['msg'] === 'error'): ?>
                    $(document).ready(function () {
                        showError('Something went wrong. Please try again.');
                    });
                <?php endif; ?>
            <?php endif; ?>

        });
    </script>

</body>

</html>