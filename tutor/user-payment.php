<?php
require_once('includes/init.php');
include 'connection.php';

// ============== HANDLE ENROLLMENT STATUS TOGGLE (AJAX) ==============
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'toggle_enrollment_status'
) {

    header('Content-Type: application/json');

    $enrollmentId = (int) $_POST['enrollment_id'];
    $newStatus = $_POST['status']; // active / inactive OR paid / pending

    $query = "
        UPDATE enrollments_tbl
        SET status = '$newStatus'
        WHERE enrollment_id = $enrollmentId
    ";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
    exit;
}


?>


<!DOCTYPE html>
<html lang="en">

<head>

    <?php include 'includes/headtag.php' ?>

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

        /* Eye icon styling */
        .view-user-info {
            transition: all 0.3s ease;
            font-size: 16px;
            color: #28a745;
        }

        .view-user-info:hover {
            color: #1e7e34 !important;
            transform: scale(1.2);
        }

        /* Professional Modal Styling */
        #userProfileModal .modal-content {
            border-radius: 16px;
            overflow: hidden;
        }

        #userProfileModal .modal-header {
            padding: 1.5rem 2rem;
        }

        #userProfileModal .modal-body {
            background-color: #f8f9fa;
        }

        #userProfileModal .card {
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        #userProfileModal .card:hover {
            transform: translateY(-2px);
        }

        #userProfileModal .card-header {
            padding: 1rem 1.25rem;
            border-radius: 12px 12px 0 0 !important;
        }

        #userProfileModal .card-body {
            padding: 1.5rem 1.25rem;
        }

        #userProfileModal table td {
            padding: 0.75rem 0.5rem;
        }

        #userProfileModal .progress {
            border-radius: 10px;
            background-color: #e9ecef;
        }

        #userProfileModal .progress-bar {
            border-radius: 10px;
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
                                            <h4 class="h3 mb-0">Student Payment Details </h4>
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
                                                    <th>enrollment Id</th>
                                                    <th>Profile</th>
                                                    <th>User Name</th>
                                                    <th>Tutor Name</th>
                                                    <th>Course Name</th>
                                                    <th>Paid Amount</th>
                                                    <th>Invoice</th>

                                                    <th>Enrolled_at</th>
                                                    <th>certificate_issued</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $logged_tutor_id = $_SESSION['tutor_id'];

                                                $display_query = "
																SELECT
																	e.enrollment_id,
                                                                    u.user_id,
																	u.user_name        AS student_name,
																	u.profile_pic AS user_profile_pic,
																	t.tutor_name       AS tutor_name,
                                                                    t.tutor_id,
																	c.course_title,
                                                                    c.course_id,
																	e.amount,
																	e.enrollment_status,
																	e.enrolled_at,
																	e.completed_at,
																	e.certificate_issued

																FROM enrollments_tbl e

																LEFT JOIN user_tbl u
																	ON e.user_id = u.user_id

																LEFT JOIN tutor_tbl t
																	ON e.tutor_id = t.tutor_id

																LEFT JOIN course_tbl c
																	ON e.course_id = c.course_id
																WHERE e.tutor_id = $logged_tutor_id

																ORDER BY e.enrollment_id DESC
																";


                                                $result = mysqli_query($conn, $display_query);

                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $userJson = htmlspecialchars(
                                                        json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    );
                                                    ?>

                                                    <tr>
                                                        <td><?= $row['enrollment_id']; ?></td>

                                                        <td>
                                                            <img src="<?= "../" . $user_profile_path . $row['user_profile_pic'] ?: 'default-profile.png'; ?>"
                                                                class="rounded-circle" width="60" height="60"
                                                                style="object-fit:cover"
                                                                onerror="this.src='assets/images/Student_Profile_Images/default-profile.png';">
                                                        </td>

                                                        <td><?= htmlspecialchars($row['student_name']); ?></td>
                                                        <td><?= htmlspecialchars($row['tutor_name']); ?></td>
                                                        <td><?= htmlspecialchars($row['course_title']); ?></td>

                                                        <td>₹<?= number_format($row['amount'], 2); ?></td>
                                                        <td>
                                                            <?php
                                                            $q = "SELECT user_payment_id 
                                                                FROM user_payment_tbl 
                                                                WHERE user_id=" . $row['user_id'] . " 
                                                                AND tutor_id=" . $row['tutor_id'] . " 
                                                                AND course_id=" . $row['course_id'];
                                                            $result = mysqli_query($conn, $q);
                                                            $row1 = mysqli_fetch_assoc($result);
                                                            ?>
                                                            <a
                                                                href="user-invoice.php?uid=<?= $row['user_id'] ?>&cid=<?= $row['course_id'] ?>&tid=<?= $row['tutor_id'] ?>">
                                                                <i class="fa-solid fa-file-invoice text-primary fs-4"></i>
                                                            </a>
                                                        </td>
                                                        <td><?= date('d-m-Y', strtotime($row['enrolled_at'])); ?></td>

                                                        <td>
                                                            <?= $row['certificate_issued']
                                                                ? '<span class="badge bg-success">Issued</span>'
                                                                : '<span class="badge bg-secondary">Pending</span>'; ?>
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
                                        <i class="fa-solid fa-user me-2 text-primary"></i>
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
    <?php include 'includes/script.php' ?>


    <script>
        $(document).ready(function () {

            // ================= USER INFO POPUP =================
            $(document).on('click', '.view-user-info', function (e) {
                e.preventDefault();

                // Parse user data from data attribute
                let userData = $(this).data('user');

                console.log('User Data:', userData); // For debugging

                // Update profile image
                let profilePic = userData.user_profile_pic || 'default.png';
                $('#modalProfilePic').attr('src', 'assets/images/users/' + profilePic);

                // Populate header section
                $('#modalUserName').text(userData.user_name || 'N/A');
                $('#modalUserEmail span').text(userData.user_email || 'N/A');

                // Update status badge
                if (userData.user_status == 1) {
                    $('#modalStatusBadge').removeClass('bg-danger').addClass('bg-success')
                        .html('<i class="fa-solid fa-circle-check me-1"></i> Active');
                } else {
                    $('#modalStatusBadge').removeClass('bg-success').addClass('bg-danger')
                        .html('<i class="fa-solid fa-circle-xmark me-1"></i> Inactive');
                }

                // Populate personal information
                $('#infoContact').text(userData.detail_mobile || 'Not Provided');

                // Date of Birth
                if (userData.dob) {
                    let dob = new Date(userData.dob);
                    let formattedDOB = dob.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                    $('#infoDOB').text(formattedDOB);
                } else {
                    $('#infoDOB').text('Not Provided');
                }

                $('#infoGender').text(userData.detail_gender || 'Not Specified');
                $('#infoLocation').text(userData.address || 'Not Provided');

                // Additional information
                $('#infoSkills').text(userData.skills || 'Not Provided');
                $('#infoLanguages').text(userData.lang_known || 'Not Provided');
                $('#infoAbout').text(userData.about_me || 'No information provided');

                // Handle progress with animation
                let progress = userData.progress || 0;
                $('#infoProgress').text(progress + '%');

                // Reset and animate progress bar
                $('#progressBar').css('width', '0%');
                setTimeout(function () {
                    $('#progressBar').css('width', progress + '%');
                }, 300);

                // Show modal
                $('#userProfileModal').modal('show');
            });

            // ============== DATATABLE WITH FILTER ==============

            let currentFilter = '';

            // Custom FILTER for Active / Inactive
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

                // No filter → show all
                if (!currentFilter) return true;

                let api = new $.fn.dataTable.Api(settings);
                let node = api.row(dataIndex).node();
                if (!node) return true;

                // ✅ FIXED CLASS NAME
                let $switch = $(node).find('.enrollment-status-switch');
                if ($switch.length === 0) return true;

                let isChecked = $switch.prop('checked');

                if (currentFilter === 'active') {
                    return isChecked === true;
                }

                if (currentFilter === 'inactive') {
                    return isChecked === false;
                }

                return true;
            });

            // DataTable init
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }

            let table = $('#datatable').DataTable({
                scrollX: true,
                scrollCollapse: true,
                responsive: false,
                lengthChange: false,
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

                let filterValue = $(this).data('filter');
                console.log('Filter clicked:', filterValue); // DEBUG

                // Remove active class from all options
                $('.filter-option').removeClass('active');

                // Add active class to clicked option
                $(this).addClass('active');

                // Set filter value
                currentFilter = filterValue;

                console.log('Current filter set to:', currentFilter); // DEBUG

                // Redraw table with filter
                table.draw();

                console.log('Table redrawn'); // DEBUG
            });

            // ============== STATUS TOGGLE FUNCTIONALITY ==============

            $(document).on('change', '.enrollment-status-switch', function () {

                let checkbox = $(this);
                let enrollmentId = checkbox.data('enrollment-id');
                let newStatus = checkbox.is(':checked') ? 1 : 0;

                $.ajax({
                    url: 'user-payment.php', // same file or handler file
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'toggle_enrollment_status',
                        enrollment_id: enrollmentId,
                        status: newStatus
                    },
                    success: function (response) {
                        if (!response.success) {
                            alert('Failed to update status');
                            checkbox.prop('checked', !newStatus); // revert
                        }
                    },
                    error: function () {
                        alert('Server error');
                        checkbox.prop('checked', !newStatus); // revert
                    }
                });
            });


        });
    </script>

</body>

</html>