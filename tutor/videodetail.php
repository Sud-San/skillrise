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

    <style>
        /* Card hover effect */
        .card:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        /* Table header bold and uppercase */
        #datatable thead th {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Center the actions column */
        #datatable td:last-child {
            width: 120px;
        }

        /* Modal styling */

        /* Modal styling */
        .modal-header {
            background: #28a745;
            color: white;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        /* Dynamic validation styles */
        .error-border {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .success-border {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        /* Filter Button Styles */
        .filter-btn {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
        }

        .filter-btn i {
            margin-right: 8px;
        }

        /* Add Order Button */
        .add-order-btn {
            background-color: #28a745;
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
        }

        .add-order-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        .add-order-btn i {
            margin-right: 8px;
        }

        /* Dropdown menu styling */
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
        }

        .dropdown-item {
            padding: 10px 20px;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-item.active {
            background-color: #28a745;
            color: white;
        }



        /* MODAL */
        /* PROFILE MODAL */
        /* MODERN PROFILE MODAL OVERHAUL */
        .profile-modal .modal-content {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        /* Creative Header with Gradient and Glassmorphism */
        .profile-header {
            background: linear-gradient(135deg, #1e7e34 0%, #28a745 100%);
            padding: 40px 30px;
            position: relative;
            color: white;
        }

        .profile-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            clip-path: ellipse(60% 100% at 50% 100%);
        }

        .profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 20px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .profile-avatar:hover {
            transform: scale(1.05) rotate(3deg);
        }

        /* Professional Typography */
        #profileName {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }

        .header-meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            opacity: 0.9;
        }

        /* Info Cards */
        .info-card {
            background: #ffffff;
            border: 1px solid #f1f4f8;
            border-radius: 20px;
            padding: 24px;
            height: 100%;
        }

        .info-title {
            font-size: 15px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9f5ff;
            display: flex;
            align-items: center;
        }

        .info-title i {
            background: #e9f5ff;
            color: #28a745;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-right: 12px;
        }

        /* Badge Style Lists */
        .clean-list li {
            display: inline-block;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 6px 14px;
            border-radius: 50px;
            margin: 3px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            transition: all 0.2s;
        }

        .clean-list li:hover {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        /* Soft Boxes for Grid Items */
        .info-item.soft {
            border: 1px solid #f1f5f9;
            background: #fdfdfd;
            padding: 15px;
            border-radius: 12px;
        }

        .info-item label {
            color: #94a3b8;
            font-size: 10px;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
    </style>

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