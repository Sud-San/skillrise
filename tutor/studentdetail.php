<?php
require_once('includes/init.php');
include 'connection.php';

// ============== HANDLE STATUS TOGGLE (AJAX REQUEST) ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {

    header('Content-Type: application/json');

    $userId = (int) $_POST['user_id'];
    $newStatus = (int) $_POST['status']; // 1 or 0

    $query = "UPDATE user_tbl SET user_status = $newStatus WHERE user_id = $userId";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
    exit; // Stop execution after handling status toggle
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <?php include 'includes/headtag.php' ?>

    <link rel="stylesheet" href="assets/css/studentdetail.css">

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
                                            <h4 class="h3 mb-0">Student Details</h4>
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
                                                <!--		<a href="add_course.php" class="btn add-order-btn">
                                                            <i class="fa-solid fa-plus"></i>&nbsp;&nbsp;Add New Course
                                                        </a>	-->

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
                                                    <th>S.No</th>
                                                    <th>User Id</th>
                                                    <th>Profile</th>
                                                    <th>User Name</th>
                                                    <th>Email</th>
                                                    <th>Course Name</th>
                                                    <th>Join Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $showStart = 1;
                                                $logged_tutor_id = $_SESSION['tutor_id'];

                                                $display_query = "
																SELECT 
																	u.user_id,
																	u.user_name,
																	u.user_email,
																	u.user_status,
																	u.created_at AS user_created_at,
																	u.profile_pic AS user_profile_pic,
																	u.mobile AS detail_mobile,
																	u.gender AS detail_gender,
																	u.dob,

																	c.course_title,

																	e.enrollment_status,
																	e.progress,
																	e.tutor_id
																FROM user_tbl u

																INNER JOIN enrollments_tbl e
																	ON u.user_id = e.user_id

																INNER JOIN course_tbl c 
																	ON e.course_id = c.course_id
																
																WHERE e.tutor_id = $logged_tutor_id

																ORDER BY u.user_id DESC
																";

                                                $result = mysqli_query($conn, $display_query);
                                                if (!$result) {
                                                    die("Query Failed: " . mysqli_error($conn));
                                                }
                                                while ($user = mysqli_fetch_array($result)) {
                                                    $userJson = htmlspecialchars(
                                                        json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    );
                                                    ?>

                                                    <tr>
                                                        <td>
                                                            <?= $showStart++; ?>
                                                        </td>
                                                        <td><?= $user['user_id']; ?></td>
                                                        <td data-user="<?= $userJson ?>" class="view-user-info"
                                                            title="View Profile" style="cursor: pointer;">
                                                            <img src="<?php echo "../" . $user_profile_path . $user['user_profile_pic'] ?: 'default-profile.png'; ?>"
                                                                class="logo rounded-circle" alt="User Profile"
                                                                onerror="this.onerror=null;this.src='<?php echo '../' . $user_profile_path . 'default-profile.png'; ?>';"
                                                                width="80">
                                                        </td>

                                                        <td class="view-user-info" data-user='<?= $userJson ?>'
                                                            title="View Profile" style="cursor: pointer;">
                                                            <div
                                                                class="d-flex align-items-center justify-content-center gap-2">
                                                                <span><?= htmlspecialchars($user['user_name']); ?></span>
                                                            </div>
                                                        </td>
                                                        <td><?= htmlspecialchars($user['user_email']); ?></td>
                                                        <td><?= htmlspecialchars($user['course_title'] ?? 'Not Enrolled'); ?>
                                                        </td>
                                                        <td><?php echo date_format(new DateTime($user['user_created_at']), 'd-m-Y'); ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex justify-content-center">
                                                                <div class="form-check form-switch custom-switch">
                                                                    <input class="form-check-input status-switch"
                                                                        type="checkbox"
                                                                        data-user-id="<?= $user['user_id']; ?>"
                                                                        <?= ($user['user_status'] == 1) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label"></label>
                                                                </div>
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



    <!-- Professional User Profile Modal -->
    <div class="modal fade" id="userProfileModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <!-- Modal Header -->
                <div class="modal-header bg-white border-0 pb-0">
                    <div class="w-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img id="modalProfilePic" src="assets/images/users/default.png"
                                class="rounded-circle border border-3 border-success"
                                style="width: 80px; height: 80px; object-fit: cover;" alt="User Profile">
                            <div>
                                <h4 id="modalUserName" class="mb-1 fw-bold text-dark"></h4>
                                <p id="modalUserEmail" class="mb-0 text-muted">
                                    <i class="fa-solid fa-envelope me-2"></i><span></span>
                                </p>
                            </div>
                            <div class="ms-auto">
                                <span id="modalStatusBadge" class="badge bg-success px-3 py-2 rounded-pill">
                                    <i class="fa-solid fa-circle-check me-1"></i> Active
                                </span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <div class="row g-4">

                        <!-- Personal Information Section -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fa-solid fa-user me-2 text-success"></i>
                                        Personal Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted fw-semibold" style="width: 40%;">
                                                    <i class="fa-solid fa-phone me-2"></i>Contact
                                                </td>
                                                <td id="infoContact" class="fw-bold">-</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-semibold">
                                                    <i class="fa-solid fa-cake-candles me-2"></i>Date of Birth
                                                </td>
                                                <td id="infoDOB" class="fw-bold">-</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-semibold">
                                                    <i class="fa-solid fa-venus-mars me-2"></i>Gender
                                                </td>
                                                <td id="infoGender" class="fw-bold">-</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-semibold">
                                                    <i class="fa-solid fa-location-dot me-2"></i>Location
                                                </td>
                                                <td id="infoLocation" class="fw-bold">-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information Section -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fa-solid fa-graduation-cap me-2 text-success"></i>
                                        Additional Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <label class="text-muted small fw-semibold mb-2">SKILLS</label>
                                        <div id="infoSkills" class="fw-bold text-dark">-</div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="text-muted small fw-semibold mb-2">LANGUAGES KNOWN</label>
                                        <div id="infoLanguages" class="fw-bold text-dark">-</div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="text-muted small fw-semibold mb-2">COURSE PROGRESS</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="flex-grow-1">
                                                <div class="progress" style="height: 12px;">
                                                    <div id="progressBar" class="progress-bar bg-success"
                                                        role="progressbar" style="width: 0%">
                                                    </div>
                                                </div>
                                            </div>
                                            <span id="infoProgress" class="fw-bold text-success"
                                                style="min-width: 45px;">0%</span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-muted small fw-semibold mb-2">ABOUT ME</label>
                                        <div id="infoAbout" class="fw-bold text-dark">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fa-solid fa-times me-2"></i>Close
                    </button>
                </div>

            </div>
        </div>
    </div>


    <!-- Javascript -->
    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php include 'includes/script.php' ?>

    <script src="assets/js/studentdetail.js"></script>
</body>

</html>