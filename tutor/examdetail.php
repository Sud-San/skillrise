<?php
include 'connection.php';

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
                                            <h4 class="h3 mb-0">Exam Details</h4>
                                        </div>
                                        <div class="col-auto">
                                            <div class="d-flex gap-2">
                                                <!-- Filter Dropdown Button -->
                                                <!--		<div class="dropdown">
                                                            <button class="btn filter-btn dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="fa-solid fa-filter"></i>&nbsp;&nbsp;Filter by
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                                                <li><a class="dropdown-item filter-option active" href="#" data-filter="">Show All</a></li>
                                                                <li><a class="dropdown-item filter-option" href="#" data-filter="active">Active Only</a></li>
                                                                <li><a class="dropdown-item filter-option" href="#" data-filter="inactive">Inactive Only</a></li>
                                                            </ul>
                                                        </div>	-->

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
                                                    <th>Student Name</th>
                                                    <th>Course Name</th>
                                                    <th>Score</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>

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
                    <h5 class="modal-title" id="editModalLabel"><i class="fa-solid fa-edit me-2"></i>Edit Order Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCourseForm">
                        <input type="hidden" id="editCourseId">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Course Title</label>
                                <input type="text" class="form-control" id="editCourseTitle">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Course Level</label>
                                <select class="form-select" id="editCourseLevel">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="editCourseDescription" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Lessons</label>
                                <input type="number" class="form-control" id="editTotalLesson">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" class="form-control" id="editPrice">
                            </div>
                        </div>
                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i
                            class="fa-solid fa-times me-2"></i>Cancel</button>
                    <button type="button" class="btn btn-success" id="saveChanges"
                        style="background-color: #28a745; border-color: #28a745;"><i
                            class="fa-solid fa-save me-2"></i>Save Changes</button>
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



    <?php include 'includes/script.php' ?>


    <script>
        $(document).ready(function () {

            // ================= USER INFO POPUP =================
            $(document).on('click', '.user-profile', function () {

                $('#profileName').text($(this).data('name'));
                $('#profileRole').text($(this).data('role'));
                $('#profileLocation').text($(this).data('location'));

                $('#infoFullName').text($(this).data('name'));
                $('#infoEmail').text($(this).data('email'));
                $('#infoPhone').text($(this).data('phone'));
                $('#infoLanguage').text($(this).data('language'));
                $('#infoAddress').text($(this).data('address'));
                $('#infoDegree').text($(this).data('degree'));

                $('#infoCourses').html('');
                $(this).data('courses').forEach(c =>
                    $('#infoCourses').append(`<li>${c}</li>`)
                );

                $('#infoCertificates').html('');
                $(this).data('certificates').forEach(c =>
                    $('#infoCertificates').append(`<li>${c}</li>`)
                );


                $('#userProfileModal').modal('show');
            });




            // ============== DYNAMIC VALIDATION SYSTEM ==============

            const ValidationRules = {
                Name: {
                    rules: [
                        {
                            test: (val) => val.trim().length >= 2,
                            message: 'Name must be at least 2 characters long'
                        },
                        {
                            test: (val) => /^[a-zA-Z\s]+$/.test(val),
                            message: 'Name should contain only letters'
                        }
                    ]
                },
                Email: {
                    rules: [
                        {
                            test: (val) => val.trim().length > 0,
                            message: 'Email is required'
                        },
                        {
                            test: (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
                            message: 'Please enter a valid email address'
                        }
                    ]
                },
                Phone: {
                    rules: [
                        {
                            test: (val) => val.trim().length > 0,
                            message: 'Phone number is required'
                        },
                        {
                            test: (val) => /^\d{10}$/.test(val.replace(/\s|-/g, '')),
                            message: 'Phone must be exactly 10 digits'
                        }
                    ]
                },
                Course: {
                    rules: [
                        {
                            test: (val) => val.trim().length >= 2,
                            message: 'Course name must be at least 2 characters'
                        }
                    ]
                },
                Enrolled: {
                    rules: [
                        {
                            test: (val) => val.trim().length > 0,
                            message: 'Enrolled courses is required'
                        },
                        {
                            test: (val) => !isNaN(val) && parseInt(val) >= 0,
                            message: 'Must be a number greater than or equal to 0'
                        }
                    ]
                },
                Progress: {
                    rules: [
                        {
                            test: (val) => val.trim().length > 0,
                            message: 'Progress is required'
                        },
                        {
                            test: (val) => !isNaN(val) && parseInt(val) >= 0 && parseInt(val) <= 100,
                            message: 'Progress must be between 0 and 100'
                        }
                    ]
                },
                Tutor: {
                    rules: [
                        {
                            test: (val) => val.trim().length >= 2,
                            message: 'Tutor name must be at least 2 characters'
                        }
                    ]
                }
            };

            // Validate single field
            function validateField(fieldId) {
                const field = $('#' + fieldId);
                const value = field.val();
                const fieldType = fieldId.replace('add', '').replace('edit', '');
                const rules = ValidationRules[fieldType];
                const errorDiv = $('#' + fieldId + 'Error');

                if (!rules) return true;

                for (let rule of rules.rules) {
                    if (!rule.test(value)) {
                        field.removeClass('success-border').addClass('error-border');
                        errorDiv.text(rule.message).addClass('show');
                        return false;
                    }
                }

                field.removeClass('error-border').addClass('success-border');
                errorDiv.removeClass('show');
                return true;
            }

            // Clear validation on field
            function clearValidation(fieldId) {
                $('#' + fieldId).removeClass('error-border success-border');
                $('#' + fieldId + 'Error').removeClass('show');
            }

            // Validate all fields in a form
            function validateForm(prefix) {
                let isValid = true;
                const fields = ['Name', 'Email', 'Phone', 'Course', 'Enrolled', 'Progress', 'Tutor'];

                fields.forEach(field => {
                    if (!validateField(prefix + field)) {
                        isValid = false;
                    }
                });

                return isValid;
            }

            // Attach real-time validation for both forms
            const prefixes = ['add', 'edit'];
            const fields = ['Name', 'Email', 'Phone', 'Course', 'Enrolled', 'Progress', 'Tutor'];

            prefixes.forEach(prefix => {
                fields.forEach(field => {
                    const fieldId = prefix + field;
                    $('#' + fieldId).on('input blur', function () {
                        validateField(fieldId);
                    });

                    $('#' + fieldId).on('focus', function () {
                        clearValidation(fieldId);
                    });
                });
            });

            // ============== DATATABLE WITH FILTER ==============

            let currentFilter = '';

            // Custom FILTER for Active/Inactive
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (!currentFilter) return true;

                let row = $("#datatable tbody tr").eq(dataIndex);
                let switchElem = row.find(".status-switch");
                let isChecked = switchElem.prop("checked");

                if (currentFilter === "active" && isChecked) return true;
                if (currentFilter === "inactive" && !isChecked) return true;

                return false;
            });

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

            // Redraw table when status switch is toggled
            $(document).on('change', '.status-switch', function () {
                table.draw();
            });

            // ============== ADD ORDER FUNCTIONALITY ==============

            $('#addOrder').on('click', function () {
                if (!validateForm('add')) {
                    alert('⚠️ Please fix all validation errors before adding.');
                    return;
                }

                // Get values
                let name = $('#addName').val();
                let email = $('#addEmail').val();
                let phone = $('#addPhone').val();
                let course = $('#addCourse').val();
                let enrolled = $('#addEnrolled').val();
                let progress = $('#addProgress').val();
                let tutor = $('#addTutor').val();
                let isActive = $('#addStatus').prop('checked');

                // Generate random ID
                let newId = '#' + Math.floor(Math.random() * 90000 + 10000);

                // Add new row to table
                let newRow = table.row.add([
                    newId,
                    'IMAGE',
                    name,
                    email,
                    phone,
                    course,
                    enrolled,
                    progress + '%',
                    tutor,
                    `<div class="d-flex justify-content-center">
                <div class="form-check form-switch custom-switch">
                    <input class="form-check-input status-switch" type="checkbox" ${isActive ? 'checked' : ''}>
                    <label class="form-check-label"></label>
                </div>
            </div>`,
                    `<a class="text-primary edit-btn" href="#" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa-solid fa-pen me-2"></i></a>
             <a class="text-danger delete-btn" href="#"><i class="fa-solid fa-trash me-2"></i></a>`
                ]).draw().node();

                // Close modal
                $('#addModal').modal('hide');

                // Clear form
                $('#addForm')[0].reset();
                fields.forEach(field => clearValidation('add' + field));

                alert('✅ Order added successfully!');

                // AJAX call would go here
                // $.ajax({ ... });
            });

            // Clear add form when modal is closed
            $('#addModal').on('hidden.bs.modal', function () {
                $('#addForm')[0].reset();
                fields.forEach(field => clearValidation('add' + field));
            });

            // ============== EDIT FUNCTIONALITY ==============

            let currentRow;
            $('#datatable tbody').on('click', '.edit-btn', function (e) {
                e.preventDefault();
                currentRow = $(this).closest('tr');

                let cells = currentRow.find('td');

                // Populate modal fields
                $('#editId').val(cells.eq(0).text());
                $('#editName').val(cells.eq(2).text());
                $('#editEmail').val(cells.eq(3).text());
                $('#editPhone').val(cells.eq(4).text());
                $('#editCourse').val(cells.eq(5).text());
                $('#editEnrolled').val(cells.eq(6).text());
                $('#editProgress').val(cells.eq(7).text().replace('%', ''));
                $('#editTutor').val(cells.eq(8).text());

                let isActive = cells.eq(9).find('.status-switch').prop('checked');
                $('#editStatus').prop('checked', isActive);

                // Clear all validations
                fields.forEach(field => clearValidation('edit' + field));
            });

            // Save changes with validation
            $('#saveChanges').on('click', function () {
                if (!validateForm('edit')) {
                    alert('⚠️ Please fix all validation errors before saving.');
                    return;
                }

                // Get values
                let name = $('#editName').val();
                let email = $('#editEmail').val();
                let phone = $('#editPhone').val();
                let course = $('#editCourse').val();
                let enrolled = $('#editEnrolled').val();
                let progress = $('#editProgress').val();
                let tutor = $('#editTutor').val();

                // Update the table row
                let cells = currentRow.find('td');
                cells.eq(2).text(name);
                cells.eq(3).text(email);
                cells.eq(4).text(phone);
                cells.eq(5).text(course);
                cells.eq(6).text(enrolled);
                cells.eq(7).text(progress + '%');
                cells.eq(8).text(tutor);

                let isActive = $('#editStatus').prop('checked');
                cells.eq(9).find('.status-switch').prop('checked', isActive);

                $('#editModal').modal('hide');

                alert('✅ Order updated successfully!');

                // AJAX call would go here
            });

            // Delete button click
            $('#datatable tbody').on('click', '.delete-btn', function (e) {
                e.preventDefault();

                if (confirm('⚠️ Are you sure you want to delete this order?')) {
                    let row = $(this).closest('tr');
                    table.row(row).remove().draw();

                    alert('🗑️ Order deleted successfully!');
                }
            });
        });

        $(document).on('click', '.edit-btn', function () {
            $('#editCourseId').val($(this).data('id'));
            $('#editCourseTitle').val($(this).data('title'));
            $('#editCourseDescription').val($(this).data('desc'));
            $('#editCourseLevel').val($(this).data('level'));
            $('#editTotalLesson').val($(this).data('lesson'));
            $('#editPrice').val($(this).data('price'));
            $('#editStatus').prop('checked', $(this).data('status') == 1);
        });


        $('#saveChanges').click(function () {
            $.ajax({
                url: 'ajax/coursedetail.php',
                type: 'POST',
                data: {
                    course_id: $('#editCourseId').val(),   // 🔥 REQUIRED
                    title: $('#editCourseTitle').val(),
                    description: $('#editCourseDescription').val(),
                    level: $('#editCourseLevel').val(),
                    lesson: $('#editTotalLesson').val(),
                    price: $('#editPrice').val()
                },
                success: function (res) {
                    if (res.trim() === 'success') {
                        location.reload();
                    } else {
                        alert(res);
                    }
                },
                error: function (xhr) {
                    alert('Update failed: ' + xhr.responseText);
                }
            });
        });


    </script>

</body>

</html>