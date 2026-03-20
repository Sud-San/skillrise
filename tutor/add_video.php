<?php
ob_start();
require_once('includes/init.php');
include 'connection.php';
include 'includes/headtag.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {

    $tutor_id = $_SESSION['tutor_id'];
    $course_id = $_POST['course_id'] ?? 0;
    $lesson_id = $_POST['lesson_id'] ?? 0;

    $uploadDir = __DIR__ . '/../videos/';

    // Ensure upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $final_video_url = '';
    $upload_error = '';

    // Handle Video File Upload
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == UPLOAD_ERR_OK && $_FILES['video_file']['size'] > 0) {
        $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/mov', 'video/quicktime'];
        $fileType = $_FILES['video_file']['type'];
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['video_file']['name']));
        $tmpName = $_FILES['video_file']['tmp_name'];
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $final_video_url = $fileName;
        } else {
            $upload_error = 'File upload failed. Check folder permissions for ' . $uploadDir;
        }
    } elseif (isset($_FILES['video_file']) && $_FILES['video_file']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_error = 'Upload error code: ' . $_FILES['video_file']['error'];
    }

    if ($upload_error) {
        $error_msg = $upload_error;
    } elseif (empty($final_video_url)) {
        $error_msg = 'Please select a video file to upload.';
    } else {
        $stmt = $conn->prepare("INSERT INTO videos_tbl 
            (tutor_id, course_id, lesson_id, video_url, video_status, uploaded_at)
            VALUES (?, ?, ?, ?, 1, NOW())");

        $lesson_id_val = ($lesson_id > 0) ? intval($lesson_id) : null;
        $stmt->bind_param("iiis", $tutor_id, $course_id, $lesson_id_val, $final_video_url);

        if ($stmt->execute()) {
            header("Location: videodetail.php?success=1");
            exit;
        } else {
            $error_msg = 'Database error: ' . $stmt->error;
        }
    }
}
?>

<style>
    .page-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 8px rgba(0, 0, 0, .07);
        overflow: hidden;
    }

    .page-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 22px 28px 18px;
        border-bottom: 1px solid #f0f0f0;
    }

    .page-card-title .title-icon {
        width: 34px;
        height: 34px;
        background: #166534;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .page-card-title .title-icon i {
        color: #fff;
        font-size: .95rem;
    }

    .page-card-title h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .page-card-body {
        padding: 28px;
    }

    .section-heading {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 18px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e5e7eb;
    }

    .form-label {
        font-size: .85rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-label .req {
        color: #dc2626;
    }

    .form-control,
    .form-select {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 9px 13px;
        font-size: .9rem;
        color: #1f2937;
        background: #fff;
        transition: border-color .18s, box-shadow .18s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, .13);
        outline: none;
    }

    .form-control::placeholder {
        color: #9ca3af;
    }

    .field-hint {
        font-size: .75rem;
        color: #9ca3af;
        margin-top: 5px;
    }

    /* Buttons */
    .btn-area {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding-top: 22px;
        margin-top: 24px;
        border-top: 1px solid #f3f4f6;
    }

    .btn-save {
        background: #16a34a;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 9px 22px;
        font-size: .88rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: background .18s;
    }

    .btn-save:hover {
        background: #15803d;
    }

    .btn-cancel {
        background: #dc2626;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 9px 22px;
        font-size: .88rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: background .18s;
    }

    .btn-cancel:hover {
        background: #b91c1c;
        color: #fff;
    }
</style>

<body class="app">
    <?php include 'includes/header.php'; ?>

    <div class="app-wrapper">
        <div class="app-content pt-4 p-md-3 p-lg-4">
            <div class="container-xl">

                <div class="page-card">

                    <div class="page-card-title">
                        <div class="title-icon"><i class="bi bi-camera-video"></i></div>
                        <h4>Add New Video</h4>
                    </div>

                    <div class="page-card-body">
                        <?php if (!empty($error_msg)): ?>
                            <div class="alert alert-danger"
                                style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:.9rem;">
                                <strong>Error:</strong> <?php echo htmlspecialchars($error_msg); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">

                            <div class="section-heading">Video Information</div>
                            <div class="row g-3 mb-4">

                                <div class="col-md-6">
                                    <label class="form-label">Course Name <span class="req">*</span></label>
                                    <select name="course_id" id="courseSelect" class="form-select" required>
                                        <option value="">Select Course</option>
                                        <?php
                                        $courseQuery = mysqli_query($conn, "SELECT course_id, course_title FROM course_tbl WHERE tutor_id = {$_SESSION['tutor_id']} ");
                                        while ($course = mysqli_fetch_assoc($courseQuery)) {
                                            echo "<option value='{$course['course_id']}'>{$course['course_title']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Lesson <span class="req">*</span></label>
                                    <select name="lesson_id" id="lessonSelect" class="form-select" required>
                                        <option value="">Select a Course First</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Video File <span class="req">*</span></label>
                                    <input type="file" name="video_file" class="form-control"
                                        accept="video/mp4,video/webm,video/ogg" required>
                                    <div class="field-hint">MP4 recommended. Max size depends on your server's
                                        <code>upload_max_filesize</code> setting.</div>
                                </div>

                            </div>

                            <div class="btn-area">
                                <button type="submit" class="btn-save">
                                    <i class="fa-solid fa-save"></i> Add Video
                                </button>
                                <a href="videodetail.php" class="btn-cancel">
                                    <i class="fa-solid fa-times"></i> Cancel
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Javascript -->
    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <?php include 'includes/script.php'; ?>

    <script>
        $(document).ready(function () {
            $('#courseSelect').change(function () {
                var course_id = $(this).val();
                if (course_id) {
                    $.ajax({
                        url: 'ajax/get_lessons.php',
                        type: 'GET',
                        data: { course_id: course_id },
                        dataType: 'json',
                        success: function (data) {
                            $('#lessonSelect').empty();
                            $('#lessonSelect').append('<option value="">Select Lesson</option>');
                            $.each(data, function (key, value) {
                                $('#lessonSelect').append('<option value="' + value.lesson_id + '">' + value.lesson_title + '</option>');
                            });
                        }
                    });
                } else {
                    $('#lessonSelect').empty();
                    $('#lessonSelect').append('<option value="">Select a Course First</option>');
                }
            });
        });
    </script>
</body>

</html>