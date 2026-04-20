<?php

require_once('includes/init.php');
include 'connection.php';

$logged_tutor_id = $_SESSION['tutor_id'];

// Create download tracking table if it doesn't exist
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS note_downloads_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    user_id INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_note_user (note_id, user_id)
)");

// Create view tracking table if it doesn't exist
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS note_views_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    user_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_view_note_user (note_id, user_id)
)");

// ============== HANDLE STATUS TOGGLE ==============
// Removed as note_status column does not exist in the schema.

// ============== HANDLE DELETE ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_note') {
    header('Content-Type: application/json');
    $noteId = (int) $_POST['note_id'];

    $res = mysqli_query($conn, "
        SELECT n.file_url 
        FROM course_notes n
        JOIN course_tbl c ON n.course_id = c.course_id
        WHERE n.note_id = $noteId AND c.tutor_id = $logged_tutor_id
    ");
    $note = mysqli_fetch_assoc($res);

    if ($note) {
        $filePath = '../uploads/notes/' . basename($note['file_url']);
        if (file_exists($filePath))
            unlink($filePath);

        // Delete mapping view/download
        mysqli_query($conn, "DELETE FROM note_downloads_tbl WHERE note_id = $noteId");
        mysqli_query($conn, "DELETE FROM note_views_tbl WHERE note_id = $noteId");

        $deleteQuery = "
            DELETE n FROM course_notes n
            JOIN course_tbl c ON n.course_id = c.course_id
            WHERE n.note_id = $noteId AND c.tutor_id = $logged_tutor_id
        ";

        if (mysqli_query($conn, $deleteQuery)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Note not found.']);
    }
    exit;
}

// ============== HANDLE UPDATE ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_note') {
    header('Content-Type: application/json');

    $noteId = (int) $_POST['note_id'];
    $courseId = (int) $_POST['course_id'];
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    if (!$courseId || !$description) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $fileClause = '';
    if (isset($_FILES['note_file']) && $_FILES['note_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['note_file'];
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $allowedExts = ['pdf', 'doc', 'docx'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileMime = mime_content_type($file['tmp_name']);
        $maxSize = 10 * 1024 * 1024;

        if (!in_array($fileExt, $allowedExts) || !in_array($fileMime, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Only PDF, DOC, DOCX files are allowed.']);
            exit;
        }
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File size must not exceed 10MB.']);
            exit;
        }

        $uploadDir = 'assets/uploads/notes/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);

        $oldRes = mysqli_query($conn, "
            SELECT n.file_url 
            FROM course_notes n
            JOIN course_tbl c ON n.course_id = c.course_id
            WHERE n.note_id = $noteId AND c.tutor_id = $logged_tutor_id
        ");
        $oldNote = mysqli_fetch_assoc($oldRes);
        if ($oldNote && file_exists($uploadDir . basename($oldNote['file_url']))) {
            unlink($uploadDir . basename($oldNote['file_url']));
        }

        $uniqueName = 'note_' . $logged_tutor_id . '_' . time() . '_' . uniqid() . '.' . $fileExt;
        $destPath = $uploadDir . $uniqueName;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
            exit;
        }

        $origName = mysqli_real_escape_string($conn, $file['name']);
        $fileSize = (int) $file['size'];
        $fileType = mysqli_real_escape_string($conn, $fileExt);
        $fileUrl = 'uploads/notes/' . $uniqueName;
        $fileClause = ", file_url='$fileUrl', file_size=$fileSize, file_type='$fileType'";
    }

    $update = "UPDATE course_notes n
               JOIN course_tbl c ON n.course_id = c.course_id
               SET
                 n.course_id = $courseId,
                 n.description = '$description'
                 $fileClause
               WHERE n.note_id = $noteId AND c.tutor_id = $logged_tutor_id";

    if (mysqli_query($conn, $update)) {
        echo json_encode(['success' => true, 'message' => 'Note updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
    exit;
}

// ============== HANDLE INCREMENT VIEW ==============
// Removed as view_count column does not exist in the schema.

// ============== HANDLE INCREMENT DOWNLOAD ==============
// Removed as download_count column does not exist in the schema.

// ============== FETCH DROPDOWN DATA ==============
$all_courses = [];
$courses_res = mysqli_query($conn, "SELECT course_id, course_title FROM course_tbl WHERE tutor_id = $logged_tutor_id ORDER BY course_title");
while ($r = mysqli_fetch_assoc($courses_res))
    $all_courses[] = $r;


// ============== FETCH NOTES ==============
$notes_query = "
    SELECT
        n.note_id, n.description, n.file_url, n.file_size,
        n.file_type, n.created_at,
        n.course_id,
        c.course_title,
        ca.category_name
    FROM course_notes n
    LEFT JOIN course_tbl c ON n.course_id = c.course_id
    LEFT JOIN category_tbl ca ON c.category_id = ca.category_id
    WHERE c.tutor_id = $logged_tutor_id
    ORDER BY n.created_at DESC
";
$notes_result = mysqli_query($conn, $notes_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php'; ?>
    <link rel="stylesheet" href="assets/css/notesdetail.css">
</head>

<body class="app">
    <?php include 'includes/header.php'; ?>

    <div class="app-wrapper">
        <div class="app-content pt-2 p-md-3 p-lg-4">
            <div class="container-xl">

                <div class="card shadow-sm border-0 rounded-4">

                    <!-- Card Header -->
                    <div class="card-header bg-white pt-3 pb-3 border-bottom">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="h5 mb-0 fw-bold">
                                    <i class="fa-solid fa-file-lines me-2 text-success"></i>Notes Management
                                </h4>
                            </div>
                            <div class="col-auto d-flex gap-2">
                                <div class="dropdown">
                                    <button class="btn filter-btn dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-filter me-1"></i> Filter
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item filter-option active" href="#" data-filter="">Show
                                                All</a></li>
                                        <li><a class="dropdown-item filter-option" href="#" data-filter="active">Active
                                                Only</a></li>
                                        <li><a class="dropdown-item filter-option" href="#"
                                                data-filter="inactive">Inactive Only</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item filter-option" href="#" data-filter="free">Free
                                                Only</a></li>
                                        <li><a class="dropdown-item filter-option" href="#" data-filter="paid">Paid
                                                Only</a></li>
                                    </ul>
                                </div>
                                <a href="add_notes.php" class="add-note-btn">
                                    <i class="fa-solid fa-upload"></i> Upload Note
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-3">
                        <table id="datatable" class="table table-bordered table-hover align-middle text-center">
                            <thead class="table-light text-uppercase">
                                <tr>
                                    <th>Sr No</th>
                                    <th>ID</th>
                                    <th>Description</th>
                                    <th>Course</th>
                                    <th>Category Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sr_no = 1;
                                while ($note = mysqli_fetch_assoc($notes_result)):
                                    $ext = $note['file_url'];
                                    $isPdf = ($note['file_type'] === 'pdf');
                                    $fileIcon = $isPdf ? 'fa-file-pdf' : 'fa-file-word';
                                    $iconColor = $isPdf ? 'text-danger' : 'text-primary';
                                    $typeBadge = $isPdf ? 'badge bg-danger file-type-badge' : 'badge bg-primary file-type-badge';
                                    $sizeDisp = $note['file_size'] > 1048576
                                        ? number_format($note['file_size'] / (1024 * 1024), 2) . ' MB'
                                        : number_format($note['file_size'] / 1024, 1) . ' KB';
                                    ?>
                                    <tr data-note-id="<?= $note['note_id'] ?>">

                                        <td><?= $sr_no++ ?></td>
                                        <td><?= $note['note_id'] ?></td>

                                        <!-- Description — truncated via CSS -->
                                        <td class="col-desc text-start"
                                            title="<?= htmlspecialchars($note['description']) ?>" style="max-width:160px;">
                                            <?= htmlspecialchars($note['description']) ?>
                                        </td>

                                        <!-- Course -->
                                        <td class="col-course" title="<?= htmlspecialchars($note['course_title'] ?? '') ?>"
                                            style="max-width:120px;">
                                            <?= htmlspecialchars($note['course_title'] ?? '-') ?>
                                        </td>

                                        <!-- Category Name -->
                                        <td class="col-category"
                                            title="<?= htmlspecialchars($note['category_name'] ?? '') ?>"
                                            style="max-width:120px;">
                                            <?= htmlspecialchars($note['category_name'] ?? '-') ?>
                                        </td>

                                        <!-- Type -->
                                        <td><span class="<?= $typeBadge ?>"><?= $note['file_type'] ?></span></td>

                                        <!-- Size -->
                                        <td class="size-text"><?= $sizeDisp ?></td>



                                        <!-- Uploaded -->
                                        <td style="white-space:nowrap;">
                                            <?= date('d-m-Y', strtotime($note['created_at'])) ?>
                                        </td>


                                        <!-- Actions -->
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="<?= '..' . $note['file_url'] ?>" class="btn btn-sm view-note-btn"
                                                    target="_blank" data-note-id="<?= $note['note_id'] ?>" title="View">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-warning edit-note" title="Edit"
                                                    data-note-id="<?= $note['note_id'] ?>"
                                                    data-course-id="<?= $note['course_id'] ?>"
                                                    data-description="<?= $note['description'] ?>"
                                                    data-file-url="<?= $note['file_url'] ?>">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <a href="<?= '..' . $note['file_url'] ?>"
                                                    class="btn btn-sm btn-outline-success download-note"
                                                    data-note-id="<?= $note['note_id'] ?>" title="Download" download>
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger delete-note"
                                                    data-note-id="<?= $note['note_id'] ?>"
                                                    data-file="<?= $note['file_url'] ?>" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
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


    <!-- ══ EDIT MODAL ══ -->
    <div class="modal fade" id="editNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen me-2"></i>Edit Note
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <form id="editNoteForm" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_note">
                        <input type="hidden" name="note_id" id="editNoteId">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Course <span class="text-danger">*</span></label>
                                <select name="course_id" id="editCourseId" class="form-select" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($all_courses as $c): ?>
                                        <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="editDescription" class="form-control" rows="3"
                                    placeholder="Note description…" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">
                                    Replace File <small class="text-muted fw-normal">(optional)</small>
                                </label>
                                <input type="file" name="note_file" id="editNoteFile" class="form-control"
                                    accept=".pdf,.doc,.docx">
                                <div class="text-muted mt-1" style="font-size:11px;">
                                    Current: <span id="editCurrentFile" class="fw-semibold text-dark"></span>
                                </div>
                            </div>
                        </div>

                        <div id="editProgress" class="mt-3">
                            <div class="progress" style="height:5px; border-radius:99px;">
                                <div id="editProgressBar" class="progress-bar bg-success" style="width:0%"></div>
                            </div>
                            <small class="text-muted" style="font-size:11px;">Uploading file…</small>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success btn-sm px-4 fw-semibold" id="submitEdit">
                        <i class="fa-solid fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ══ END EDIT MODAL ══ -->


    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'includes/script.php'; ?>

    <script src="assets/js/notesdetail.js"></script>

</body>

</html>