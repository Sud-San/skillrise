<?php
require_once('includes/init.php');
require_once('includes/package_check.php');
include 'connection.php';

$logged_tutor_id = $_SESSION['tutor_id'];

// ============== HANDLE FILE UPLOAD ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_note') {
    header('Content-Type: application/json');

    $courseId = (int) $_POST['course_id'];
    $lessonId = (int) $_POST['lesson_id'];
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    if (!$courseId || !$lessonId || !$description) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (!isset($_FILES['note_file']) || $_FILES['note_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid file.']);
        exit;
    }

    $file = $_FILES['note_file'];
    $allowedExts = ['pdf', 'doc', 'docx'];
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileMime = mime_content_type($file['tmp_name']);
    $maxSize = 10 * 1024 * 1024;

    if (!in_array($fileExt, $allowedExts) || !in_array($fileMime, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Only PDF, DOC, and DOCX files are allowed.']);
        exit;
    }
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File size must not exceed 10MB.']);
        exit;
    }

    $uploadDir = '/uploads/notes/';
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0755, true);

    $uniqueName = 'note_' . $logged_tutor_id . '_' . time() . '_' . uniqid() . '.' . $fileExt;
    $destPath = $uploadDir . $uniqueName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file. Check folder permissions.']);
        exit;
    }

    $fileSize = (int) $file['size'];
    $fileType = mysqli_real_escape_string($conn, $fileExt);
    $fileUrl = 'uploads/notes/' . $uniqueName;

    $insert = "INSERT INTO course_notes (course_id, lesson_id, file_url, description, file_size, file_type)
               VALUES ($courseId, $lessonId, '$fileUrl', '$description', $fileSize, '$fileType')";

    if (mysqli_query($conn, $insert)) {
        echo json_encode(['success' => true]);
    } else {
        unlink($destPath);
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
    exit;
}

// ============== FETCH COURSES ==============
$courses = mysqli_query($conn, "SELECT course_id, course_title FROM course_tbl WHERE tutor_id = $logged_tutor_id ORDER BY course_title");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php'; ?>
    <link rel="stylesheet" href="assets/css/add_notes.css" />
</head>

<body class="app">
    <?php include 'includes/header.php'; ?>

    <div class="app-wrapper">
        <div class="app-content pt-2 p-md-3 p-lg-4">
            <div class="container-xl">

                <!-- Breadcrumb -->
                <div class="bc">
                    <a href="notesdetail.php"><i class="fa-solid fa-file-lines me-1"></i>Notes</a>
                    <span class="sep">›</span>Upload Note
                </div>

                <!-- Main card — matches dashboard width -->
                <div class="card shadow-sm border-0 rounded-4">

                    <!-- Header -->
                    <div class="card-header bg-white pt-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="h5 mb-0 fw-bold">
                                <i class="fa-solid fa-file-arrow-up me-2 text-success"></i>Upload New Note
                            </h4>
                            <a href="notesdetail.php" class="btn-back">
                                <i class="fa-solid fa-arrow-left"></i> Back to Notes
                            </a>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body p-4">
                        <form id="addNoteForm" enctype="multipart/form-data" novalidate>
                            <div class="row g-4">

                                <!-- ── LEFT: Details + Access ── -->
                                <div class="col-lg-7">

                                    <div class="section-lbl"><i class="fa-solid fa-circle-dot"></i> Note Details</div>

                                    <div class="row g-3 mb-4">

                                        <div class="col-md-6">
                                            <label class="form-label">Course <span class="text-danger">*</span></label>
                                            <select class="form-select" id="noteCourse" name="course_id">
                                                <option value="">— Select Course —</option>
                                                <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                                                    <option value="<?= $c['course_id'] ?>">
                                                        <?= htmlspecialchars($c['course_title']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <div class="error-msg" id="courseErr">Please select a course.</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Lesson <span class="text-danger">*</span></label>
                                            <select class="form-select" id="noteLesson" name="lesson_id" disabled>
                                                <option value="">— Select a Course First —</option>
                                            </select>
                                            <div class="error-msg" id="lessonErr">Please select a lesson.</div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Description <span
                                                    class="text-danger">*</span></label>
                                            <textarea class="form-control" id="noteDesc" name="description" rows="3"
                                                maxlength="500"
                                                placeholder="Briefly describe what this note covers..."></textarea>
                                            <div class="d-flex justify-content-between mt-1">
                                                <div class="error-msg" id="descErr">Please enter a description.</div>
                                                <span class="char-count ms-auto" id="descCount">0 / 500</span>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="access-row">
                                        <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3"
                                            style="background:#f0fff4; border:1px solid #b7ebc8; font-size:12px; color:#166534; width:100%;">
                                            <i class="fa-solid fa-circle-info flex-shrink-0"></i>
                                            <span>Notes are linked to a specific lesson and accessible to enrolled
                                                students.</span>
                                        </div>
                                    </div>

                                </div>

                                <!-- ── RIGHT: File Upload ── -->
                                <div class="col-lg-5">

                                    <div class="section-lbl"><i class="fa-solid fa-circle-dot"></i> File Upload</div>

                                    <!-- Info strip -->
                                    <div class="d-flex align-items-center gap-2 mb-3 px-3 py-2 rounded-3"
                                        style="background:#f0f4ff; border:1px solid #d0deff; font-size:12px; color:#3a5bd9;">
                                        <i class="fa-solid fa-circle-info flex-shrink-0"></i>
                                        <span>PDF, DOC, DOCX &nbsp;·&nbsp; Max <strong>10 MB</strong></span>
                                    </div>

                                    <!-- Drop zone -->
                                    <div class="drop-zone" id="dropZone">
                                        <input type="file" id="noteFile" name="note_file" accept=".pdf,.doc,.docx">

                                        <div id="dzDefault">
                                            <div class="dz-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                            <p class="fw-semibold mb-1 text-dark" style="font-size:14px;">Drop file here
                                            </p>
                                            <p class="text-muted mb-0" style="font-size:12px;">
                                                or <span class="text-success fw-bold">click to browse</span>
                                            </p>
                                        </div>

                                        <div class="file-row" id="dzPreview">
                                            <div class="file-thumb pdf" id="fileThumb">
                                                <i class="fa-solid fa-file-pdf" id="fileThumbIcon"></i>
                                            </div>
                                            <div class="f-info">
                                                <div class="f-name" id="previewName"></div>
                                                <div class="f-meta" id="previewMeta"></div>
                                            </div>
                                            <button type="button" id="clearFile"
                                                class="btn btn-sm btn-outline-danger flex-shrink-0"
                                                style="border-radius:6px; font-size:12px; position:relative; z-index:10;">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="error-msg mt-2" id="fileErr">Please select a valid file.</div>

                                    <!-- Upload progress -->
                                    <div id="progWrap">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted" style="font-size:11px;">Uploading...</small>
                                            <small class="text-success fw-bold" style="font-size:11px;"
                                                id="progPct">0%</small>
                                        </div>
                                        <div class="prog-track">
                                            <div class="prog-fill" id="progFill"></div>
                                        </div>
                                    </div>

                                </div>
                                <!-- /right col -->

                            </div>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-white border-top d-flex justify-content-end gap-2 py-3 px-4">
                        <a href="notesdetail.php" class="btn-back">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </a>
                        <button type="button" class="btn-upload" id="submitBtn">
                            <i class="fa-solid fa-upload"></i> Upload Note
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'includes/script.php'; ?>

    <script>
        $(document).ready(function () {

            // Char counter
            $('#noteDesc').on('input', function () {
                let n = $(this).val().length;
                let $c = $('#descCount').text(n + ' / 500').removeClass('warn over');
                if (n >= 500) $c.addClass('over');
                else if (n >= 400) $c.addClass('warn');
            });

            // Access cards
            $('input[name="is_free"]').on('change', function () {
                $('#optFree, #optPaid').removeClass('sel-free sel-paid');
                $(this).val() === '1' ? $('#optFree').addClass('sel-free') : $('#optPaid').addClass('sel-paid');
            });

            // Drop zone
            let $dz = $('#dropZone');

            $dz.on('dragover dragenter', function (e) {
                e.preventDefault(); $(this).addClass('dragover');
            }).on('dragleave', function (e) {
                e.preventDefault(); $(this).removeClass('dragover');
            }).on('drop', function (e) {
                e.preventDefault(); $(this).removeClass('dragover');
                let f = e.originalEvent.dataTransfer.files[0];
                if (f) {
                    let dt = new DataTransfer(); dt.items.add(f);
                    document.getElementById('noteFile').files = dt.files;
                    handleFile(f);
                }
            });

            $('#noteFile').on('change', function () { if (this.files[0]) handleFile(this.files[0]); });

            $('#clearFile').on('click', function (e) {
                e.stopPropagation();
                $('#noteFile').val('');
                resetDz();
                $('#fileErr').removeClass('show');
            });

            function handleFile(f) {
                let ext = f.name.split('.').pop().toLowerCase();
                if (!['pdf', 'doc', 'docx'].includes(ext)) { $('#fileErr').text('Only PDF, DOC, DOCX allowed.').addClass('show'); resetDz(); return; }
                if (f.size > 10485760) { $('#fileErr').text('File must not exceed 10 MB.').addClass('show'); resetDz(); return; }
                $('#fileErr').removeClass('show');

                let $t = $('#fileThumb'), $i = $('#fileThumbIcon');
                $t.removeClass('pdf word'); $i.removeClass('fa-file-pdf fa-file-word');
                ext === 'pdf' ? ($t.addClass('pdf'), $i.addClass('fa-file-pdf')) : ($t.addClass('word'), $i.addClass('fa-file-word'));

                let size = f.size > 1048576 ? (f.size / 1048576).toFixed(2) + ' MB' : (f.size / 1024).toFixed(1) + ' KB';
                $('#previewName').text(f.name);
                $('#previewMeta').text(size + ' · ' + ext.toUpperCase());
                $('#dzDefault').hide();
                $('#dzPreview').addClass('show');
                $dz.addClass('has-file');
            }

            function resetDz() {
                $('#dzDefault').show();
                $('#dzPreview').removeClass('show');
                $dz.removeClass('has-file dragover');
            }

            // Course → load lessons
            $('#noteCourse').on('change', function () {
                $(this).removeClass('error-border');
                $('#courseErr').removeClass('show');
                var course_id = $(this).val();
                var $ls = $('#noteLesson');
                $ls.empty().append('<option value="">— Loading... —</option>').prop('disabled', true);
                if (course_id) {
                    $.getJSON('ajax/get_lessons.php', { course_id: course_id }, function (data) {
                        $ls.empty().append('<option value="">— Select Lesson —</option>');
                        if (data && data.length) {
                            $.each(data, function (i, v) {
                                $ls.append('<option value="' + v.lesson_id + '">' + v.lesson_title + '</option>');
                            });
                            $ls.prop('disabled', false);
                        } else {
                            $ls.append('<option value="">No lessons found</option>');
                        }
                    }).fail(function () {
                        $ls.empty().append('<option value="">Failed to load lessons</option>');
                    });
                } else {
                    $ls.empty().append('<option value="">— Select a Course First —</option>');
                }
            });

            $('#noteLesson').on('change', function () {
                $(this).removeClass('error-border'); $('#lessonErr').removeClass('show');
            });

            // Submit
            $('#submitBtn').on('click', function () {
                let ok = true;
                $('.error-msg').removeClass('show');
                $('.form-control, .form-select').removeClass('error-border');

                if (!$('#noteCourse').val()) { $('#courseErr').addClass('show'); $('#noteCourse').addClass('error-border'); ok = false; }
                if (!$('#noteLesson').val()) { $('#lessonErr').addClass('show'); $('#noteLesson').addClass('error-border'); ok = false; }
                if (!$('#noteDesc').val().trim()) { $('#descErr').addClass('show'); $('#noteDesc').addClass('error-border'); ok = false; }
                if (!document.getElementById('noteFile').files.length) { $('#fileErr').text('Please select a file.').addClass('show'); ok = false; }

                if (!ok) { $('html,body').animate({ scrollTop: $('.error-msg.show').first().offset().top - 100 }, 250); return; }

                let fd = new FormData($('#addNoteForm')[0]);
                fd.append('action', 'upload_note');

                let $btn = $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Uploading...');
                $('#progWrap').show();

                $.ajax({
                    url: 'add_notes.php', type: 'POST', data: fd,
                    processData: false, contentType: false,
                    xhr: function () {
                        let x = new XMLHttpRequest();
                        x.upload.addEventListener('progress', function (e) {
                            if (e.lengthComputable) {
                                let p = Math.round(e.loaded / e.total * 100);
                                $('#progFill').css('width', p + '%');
                                $('#progPct').text(p + '%');
                            }
                        });
                        return x;
                    },
                    success: function (res) {
                        $('#progWrap').hide(); $('#progFill').css('width', '0%');
                        $btn.prop('disabled', false).html('<i class="fa-solid fa-upload me-1"></i> Upload Note');
                        if (res.success) {
                            Swal.fire({
                                icon: 'success', title: 'Note Uploaded!',
                                text: 'Your note has been saved successfully.',
                                confirmButtonColor: '#28a745', confirmButtonText: 'Go to Notes'
                            }).then(() => { window.location.href = 'notesdetail.php'; });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Upload Failed!', text: res.message || 'Something went wrong.', confirmButtonColor: '#dc3545' });
                        }
                    },
                    error: function () {
                        $('#progWrap').hide();
                        $btn.prop('disabled', false).html('<i class="fa-solid fa-upload me-1"></i> Upload Note');
                        Swal.fire({ icon: 'error', title: 'Server Error!', text: 'Unable to upload. Please try again.', confirmButtonColor: '#dc3545' });
                    }
                });
            });

        });
    </script>

</body>

</html>