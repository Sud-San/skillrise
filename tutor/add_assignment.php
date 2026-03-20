<?php
require_once('includes/init.php');
include 'connection.php';
include 'includes/headtag.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $course_id       = $_POST['course_id'];
    $assignment_name = $_POST['assignment_name'];
    $status          = isset($_POST['assignment_status']) ? 1 : 0;

    $uploadDir = __DIR__ . '/assets/assignments/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName   = time() . '_' . basename($_FILES['assignment_file']['name']);
    $tmpName    = $_FILES['assignment_file']['tmp_name'];
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        die('❌ Assignment file upload failed');
    }

    $query = "INSERT INTO assignment_tbl 
              (course_id, assignment_name, assignment_file, assignment_status, created_at)
              VALUES
              ('$course_id', '$assignment_name', '$fileName', '$status', NOW())";

    if (mysqli_query($conn, $query)) {
        header("Location: assignmentdetail.php?success=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<style>
    .page-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 8px rgba(0,0,0,.07);
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
        width: 34px; height: 34px;
        background: #166534;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .page-card-title .title-icon i { color: #fff; font-size: .95rem; }
    .page-card-title h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }
    .page-card-body { padding: 28px; }

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
    .form-label .req { color: #dc2626; }

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
        box-shadow: 0 0 0 3px rgba(22,163,74,.13);
        outline: none;
    }
    .form-control::placeholder { color: #9ca3af; }
    .field-hint { font-size: .75rem; color: #9ca3af; margin-top: 5px; }

    /* Toggle */
    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 16px;
    }
    .toggle-row .t-label { font-size: .88rem; font-weight: 600; color: #374151; }
    .toggle-row .t-sub   { font-size: .75rem; color: #9ca3af; margin-top: 2px; }
    .form-check-input[type="checkbox"] {
        width: 42px; height: 23px;
        appearance: none; -webkit-appearance: none;
        background: #d1d5db;
        border-radius: 99px; border: none;
        position: relative; cursor: pointer;
        transition: background .2s;
        flex-shrink: 0;
    }
    .form-check-input[type="checkbox"]::after {
        content: '';
        position: absolute;
        top: 3px; left: 3px;
        width: 17px; height: 17px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
        transition: transform .2s;
    }
    .form-check-input[type="checkbox"]:checked { background: #16a34a; }
    .form-check-input[type="checkbox"]:checked::after { transform: translateX(19px); }

    /* Buttons */
    .btn-area {
        display: flex; gap: 10px; flex-wrap: wrap;
        padding-top: 22px;
        margin-top: 24px;
        border-top: 1px solid #f3f4f6;
    }
    .btn-save {
        background: #16a34a;
        color: #fff; border: none;
        border-radius: 8px;
        padding: 9px 22px;
        font-size: .88rem; font-weight: 600;
        cursor: pointer;
        display: flex; align-items: center; gap: 7px;
        transition: background .18s;
    }
    .btn-save:hover { background: #15803d; }
    .btn-cancel {
        background: #dc2626;
        color: #fff; border: none;
        border-radius: 8px;
        padding: 9px 22px;
        font-size: .88rem; font-weight: 600;
        text-decoration: none;
        display: flex; align-items: center; gap: 7px;
        transition: background .18s;
    }
    .btn-cancel:hover { background: #b91c1c; color: #fff; }
</style>

<body class="app">
<?php include 'includes/header.php'; ?>

<div class="app-wrapper">
    <div class="app-content pt-4 p-md-3 p-lg-4">
        <div class="container-xl">

            <div class="page-card">

                <div class="page-card-title">
                    <div class="title-icon"><i class="bi bi-file-earmark-plus"></i></div>
                    <h4>Add New Assignment</h4>
                </div>

                <div class="page-card-body">
                    <form method="POST" enctype="multipart/form-data">

                        <div class="section-heading">Assignment Information</div>
                        <div class="row g-3 mb-4">

                            <div class="col-md-6">
                                <label class="form-label">Course Name <span class="req">*</span></label>
                                <select name="course_id" class="form-select" required>
                                    <option value="">Select Course</option>
                                    <?php
                                    $courseQuery = mysqli_query($conn, "SELECT course_id, course_title FROM course_tbl");
                                    while ($course = mysqli_fetch_assoc($courseQuery)) {
                                        echo "<option value='{$course['course_id']}'>{$course['course_title']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Assignment Name <span class="req">*</span></label>
                                <input type="text" name="assignment_name" class="form-control"
                                       placeholder="e.g. Chapter 3 – Arrays" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Assignment File <span class="req">*</span></label>
                                <input type="file" name="assignment_file" class="form-control"
                                       accept=".pdf,.doc,.docx" required>
                                <div class="field-hint">Accepted formats: PDF, DOC, DOCX</div>
                            </div>

                        </div>

                        <div class="section-heading">Settings</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="toggle-row">
                                    <div>
                                        <div class="t-label">Status</div>
                                        <div class="t-sub">Toggle to activate or deactivate</div>
                                    </div>
                                    <input class="form-check-input" type="checkbox"
                                           name="assignment_status" id="assignmentStatus" checked>
                                </div>
                            </div>
                        </div>

                        <div class="btn-area">
                            <button type="submit" class="btn-save">
                                <i class="bi bi-save"></i> Add Assignment
                            </button>
                            <a href="assignmentdetail.php" class="btn-cancel">
                                <i class="bi bi-x"></i> Cancel
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
</body>
</html>