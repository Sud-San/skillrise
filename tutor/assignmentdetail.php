<?php
require_once('includes/init.php');
include 'connection.php';

$tutor_id = $_SESSION['tutor_id'];
$courses = mysqli_query($conn, "
    SELECT 
        course_tbl.*, 
        assignment_tbl.assignment_id, 
        assignment_tbl.title, 
        assignment_tbl.description,
        assignment_tbl.file_url, 
        assignment_tbl.status as assignment_status 
    FROM course_tbl 
    JOIN assignment_tbl ON course_tbl.course_id = assignment_tbl.course_id 
    WHERE course_tbl.tutor_id = $tutor_id 
    ORDER BY course_id DESC
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
    <link rel="stylesheet" href="assets/css/assignmentdetail.css">

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
                                            <h4 class="h3 mb-0">Assignment Details</h4>
                                        </div>
                                        <div class="col-auto">
                                            <div class="d-flex gap-2">
                                                <!-- Filter Dropdown Button -->
                                                <div class="dropdown">
                                                    <button class="btn filter-btn dropdown-toggle" type="button"
                                                        id="filterDropdown" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fa-solid fa-filter"></i>&nbsp;&nbsp;Filter by
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                                        <li><a class="dropdown-item filter-option active" href="#"
                                                                data-filter="">Show All</a></li>
                                                        <li><a class="dropdown-item filter-option" href="#"
                                                                data-filter="active">Active Only</a></li>
                                                        <li><a class="dropdown-item filter-option" href="#"
                                                                data-filter="inactive">Inactive Only</a></li>
                                                    </ul>
                                                </div>

                                                <!-- Add Order Button -->
                                                <a href="add_assignment.php" class="btn add-order-btn">
                                                    <i class="fa-solid fa-plus"></i>&nbsp;&nbsp;Add New Assignment
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
                                                    <th>Course Name</th>
                                                    <th>Assignment Name</th>
                                                    <th>Description</th>
                                                    <th>File</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = mysqli_fetch_assoc($courses)) { ?>
                                                    <tr>

                                                        <!-- COURSE NAME -->
                                                        <td class="text-start">
                                                            <div class="user-cell">
                                                                <span class="user-name">
                                                                    <?= htmlspecialchars($row['course_title']); ?>
                                                                </span>
                                                            </div>
                                                        </td>

                                                        <!-- ASSIGNMENT NAME -->
                                                        <td>
                                                            <span class="text-muted fst-italic">
                                                                <?php if (empty($row['title'])) { ?>
                                                                    No title available
                                                                <?php } else { ?>
                                                                    <?php echo htmlspecialchars($row['title']); ?>
                                                                <?php } ?>
                                                            </span>
                                                        </td>

                                                        <!-- DESCRIPTION -->
                                                        <td>
                                                            <span class="text-muted fst-italic">
                                                                <?php if (empty($row['description'])) { ?>
                                                                    No description available
                                                                <?php } else { ?>
                                                                    <?php echo htmlspecialchars($row['description']); ?>
                                                                <?php } ?>
                                                            </span>
                                                        </td>

                                                        <!-- FILE -->
                                                        <td>
                                                            <span class="text-muted fst-italic">
                                                                <?php if (empty($row['file_url'])) { ?>
                                                                    No file available
                                                                <?php } else { ?>
                                                                    <a href="assets/assignments/<?php echo htmlspecialchars($row['file_url']); ?>"
                                                                        target="_blank">
                                                                        <?php echo htmlspecialchars($row['file_url']); ?>
                                                                    </a>
                                                                <?php } ?>
                                                            </span>
                                                        </td>

                                                        <!-- STATUS -->
                                                        <td>
                                                            <div class="d-flex justify-content-center">
                                                                <?php if (!empty($row['assignment_id'])) { ?>
                                                                    <div class="form-check form-switch custom-switch">
                                                                        <input class="form-check-input status-switch" type="checkbox"
                                                                            data-id="<?= $row['assignment_id']; ?>"
                                                                            <?= ($row['assignment_status'] == 1) ? 'checked' : ''; ?>>
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <span class="badge bg-secondary">No Assignment</span>
                                                                <?php } ?>
                                                            </div>
                                                        </td>

                                                        <!-- ACTION -->
                                                        <td class="text-center">
                                                            <?php if (!empty($row['assignment_id'])) { ?>
                                                                    <a href="#" class="edit-btn text-primary"
                                                                        data-id="<?= $row['assignment_id']; ?>"
                                                                        data-course="<?= $row['course_id']; ?>"
                                                                        data-title="<?= htmlspecialchars($row['title'] ?? ''); ?>"
                                                                        data-desc="<?= htmlspecialchars($row['description'] ?? ''); ?>"
                                                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                                                        title="Edit Assignment">
                                                                        <i class="fa-solid fa-pen"></i>
                                                                    </a>
                                                                    <a href="#" class="delete-btn text-danger ms-2"
                                                                        data-id="<?= $row['assignment_id']; ?>"
                                                                        title="Delete Assignment">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </a>
                                                            <?php } ?>
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
                    <h5 class="modal-title" id="editModalLabel"><i class="fa-solid fa-edit me-2"></i>Edit Assignment Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAssignmentForm" enctype="multipart/form-data">
                        <input type="hidden" id="editAssignmentId" name="assignment_id">

                        <div class="row g-3">
                            <!-- Course Name -->
                            <div class="col-md-6">
                                <label class="form-label">Course Name <span class="text-danger">*</span></label>
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

                            <!-- Assignment Title -->
                            <div class="col-md-6">
                                <label class="form-label">Assignment Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editAssignmentTitle" name="title" required>
                            </div>

                            <!-- Assignment Description -->
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="editAssignmentDescription" name="description" rows="3"></textarea>
                            </div>

                            <!-- Assignment File -->
                            <div class="col-12">
                                <label class="form-label">Assignment File</label>
                                <input type="file" name="assignment_file" class="form-control" id="editAssignmentFile"
                                    accept=".pdf,.doc,.docx,.zip,.rar">
                                <small class="text-muted">Leave blank to keep current file</small>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i
                            class="fa-solid fa-times me-2"></i>Cancel</button>
                    <button type="button" class="btn btn-success" id="saveAssignmentChanges"
                        style="background-color: #28a745; border-color: #28a745;">
                        <i class="fa-solid fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="userProfileModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content profile-modal">
                <div class="profile-header">
                    <div class="d-flex align-items-center gap-4">
                        <img src="https://i.pravatar.cc/300" class="profile-avatar" alt="User" />
                        <div class="flex-grow-1">
                            <div class="badge bg-light text-success mb-2 px-3 py-2 rounded-pill">Verified Student</div>
                            <h5 id="profileName" class="text-white"></h5>
                            <div class="header-meta">
                                <span><i class="fa-solid fa-briefcase me-1"></i> <span id="profileRole"></span></span>
                                <span><i class="fa-solid fa-location-dot me-1"></i> <span
                                        id="profileLocation"></span></span>
                            </div>
                        </div>
                    </div>
                    <button class="btn-close btn-close-white position-absolute top-0 end-0 m-4"
                        data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-title">
                                    <i class="fa-solid fa-id-card"></i>&nbsp; Contact Information
                                </div>
                                <div class="info-grid">
                                    <div class="info-item soft mb-3">
                                        <label>EMAIL ADDRESS</label>
                                        <div id="infoEmail" class="fw-bold text-dark"></div>
                                    </div>
                                    <div class="info-item soft mb-3">
                                        <label>PHONE NUMBER</label>
                                        <div id="infoPhone" class="fw-bold text-dark"></div>
                                    </div>
                                    <div class="info-item soft mb-3">
                                        <label>PREFERED LANGUAGE</label>
                                        <div id="infoLanguage" class="fw-bold text-dark"></div>
                                    </div>
                                    <div class="info-item soft">
                                        <label>RESIDENTIAL ADDRESS</label>
                                        <div id="infoAddress" class="fw-bold text-dark small"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-title">
                                    <i class="fa-solid fa-graduation-cap"></i>&nbsp; Academic Profile
                                </div>
                                <div class="mb-4">
                                    <label class="text-muted small fw-bold mb-2 d-block">HIGHEST DEGREE</label>
                                    <div id="infoDegree" class="h6 fw-bold"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="text-muted small fw-bold mb-2 d-block">ENROLLED COURSES</label>
                                    <ul id="infoCourses" class="clean-list p-0"></ul>
                                </div>
                                <div>
                                    <label class="text-muted small fw-bold mb-2 d-block">CERTIFICATIONS</label>
                                    <ul id="infoCertificates" class="clean-list p-0"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Javascript -->
    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <?php include 'includes/script.php' ?>
    <script src="assets/js/assignmentdetail.js"></script>

</body>

</html>