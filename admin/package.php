<?php
include "connection.php";

$insertSuccess = false;
$insertError = false;
$errorMsg = "";
$isUpdate = false;
$data = [];

// Handle update
if (isset($_GET['update_id']) && $_GET['update_id'] != "") {
    $isUpdate = true;
    $update_id = mysqli_real_escape_string($conn, $_GET['update_id']);
    $sql = "SELECT * FROM package_tbl WHERE package_id = '$update_id'";
    $res = mysqli_query($conn, $sql);
    if (mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn'])) {
    // Get and sanitize inputs
    $update_id = isset($_POST['update_id']) ? mysqli_real_escape_string($conn, $_POST['update_id']) : '';
    $package_name = isset($_POST['package_name']) ? mysqli_real_escape_string($conn, trim($_POST['package_name'])) : '';
    $price = isset($_POST['price']) ? mysqli_real_escape_string($conn, trim($_POST['price'])) : '';
    $valid_months = isset($_POST['valid_months']) ? mysqli_real_escape_string($conn, trim($_POST['valid_months'])) : '';
    $max_course = isset($_POST['max_course']) ? mysqli_real_escape_string($conn, trim($_POST['max_course'])) : '';
    $max_video_upload = isset($_POST['max_video_upload']) ? mysqli_real_escape_string($conn, trim($_POST['max_video_upload'])) : '';

    // Handle checkboxes (if checked = 1, else 0)
    $can_add_courses = isset($_POST['can_add_courses']) ? 1 : 0;
    $can_add_videos = isset($_POST['can_add_videos']) ? 1 : 0;
    $can_add_quiz = isset($_POST['can_add_quiz']) ? 1 : 0;
    $can_add_assignments = isset($_POST['can_add_assignments']) ? 1 : 0;
    $can_add_games = isset($_POST['can_add_games']) ? 1 : 0;
    $package_status = isset($_POST['package_status']) ? 1 : 0;

    // VALIDATIONS
    $errors = [];

    // Package Name validation
    if (empty($package_name)) {
        $errors[] = "package_name_required";
    } elseif (strlen($package_name) < 3) {
        $errors[] = "package_name_short";
    } elseif (strlen($package_name) > 100) {
        $errors[] = "package_name_long";
    } elseif (!preg_match("/^[a-zA-Z0-9\s\-]+$/", $package_name)) {
        $errors[] = "package_name_invalid";
    }

    // Price validation
    if (empty($price)) {
        $errors[] = "price_required";
    } elseif (!is_numeric($price)) {
        $errors[] = "price_invalid";
    } elseif ($price < 0) {
        $errors[] = "price_negative";
    } elseif ($price > 9999999) {
        $errors[] = "price_too_high";
    }

    // Valid Months validation
    if (empty($valid_months)) {
        $errors[] = "valid_months_required";
    } elseif (!is_numeric($valid_months)) {
        $errors[] = "valid_months_invalid";
    } elseif ($valid_months < 1) {
        $errors[] = "valid_months_min";
    } elseif ($valid_months > 60) {
        $errors[] = "valid_months_max";
    }

    // Max Course validation
    if (empty($max_course)) {
        $errors[] = "max_course_required";
    } elseif (!is_numeric($max_course)) {
        $errors[] = "max_course_invalid";
    } elseif ($max_course < 0) {
        $errors[] = "max_course_negative";
    } elseif ($max_course > 1000) {
        $errors[] = "max_course_max";
    }

    // Max Video Upload validation
    if (empty($max_video_upload)) {
        $errors[] = "max_video_required";
    } elseif (!is_numeric($max_video_upload)) {
        $errors[] = "max_video_invalid";
    } elseif ($max_video_upload < 0) {
        $errors[] = "max_video_negative";
    } elseif ($max_video_upload > 1000) {
        $errors[] = "max_video_max";
    }

    // Check for duplicate package name (only for new packages)
    if (empty($errors) && !$isUpdate) {
        $check_sql = "SELECT package_id FROM package_tbl WHERE package_name = '$package_name'";
        $check_result = mysqli_query($conn, $check_sql);
        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = "package_name_duplicate";
        }
    }

    // If no errors, proceed with insert/update
    if (empty($errors)) {
        if ($isUpdate && !empty($update_id)) {
            // Update Query
            $update = "UPDATE package_tbl SET 
                      package_name = '$package_name',
                      price = '$price',
                      valid_months = '$valid_months',
                      max_course = '$max_course',
                      max_video_upload = '$max_video_upload',
                      can_add_courses = '$can_add_courses',
                      can_add_videos = '$can_add_videos',
                      can_add_quiz = '$can_add_quiz',
                      can_add_assignments = '$can_add_assignments',
                      can_add_games = '$can_add_games',
                      package_status = '$package_status'
                      WHERE package_id = '$update_id'";

            if (mysqli_query($conn, $update)) {
                $insertSuccess = true;
            } else {
                $insertError = true;
                $errorMsg = "Failed to update package: " . mysqli_error($conn);
            }
        } else {
            // Insert Query
            $insert = "INSERT INTO package_tbl 
                      (package_name, price, valid_months, max_course, max_video_upload, 
                       can_add_courses, can_add_videos, can_add_quiz, can_add_assignments, 
                       can_add_games, package_status, created_at) 
                      VALUES 
                      ('$package_name', '$price', '$valid_months', '$max_course', '$max_video_upload',
                       '$can_add_courses', '$can_add_videos', '$can_add_quiz', '$can_add_assignments',
                       '$can_add_games', '$package_status', NOW())";

            if (mysqli_query($conn, $insert)) {
                $insertSuccess = true;
            } else {
                $insertError = true;
                $errorMsg = "Failed to add package: " . mysqli_error($conn);
            }
        }
    } else {
        $insertError = true;
        $errorMsg = implode(", ", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $isUpdate ? 'Edit' : 'Add'; ?> Package | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- App favicon -->
    <link rel="shortcut icon" href="../SkillRise_logo1.png">

    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <style>
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-control.is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23dc3545' viewBox='0 0 20 20'%3E%3Cpath d='M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0zm0 15a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6zm1-4.5a1 1 0 1 1-2 0V6a1 1 0 1 1 2 0v4.5z'/%3E%3C/svg%3E") !important;
            background-size: 20px;
            background-position: right 10px center;
            background-repeat: no-repeat;
        }

        .form-control.is-valid {
            border-color: #28a745 !important;
        }

        .form-check-input {
            margin-left: 0;
            margin-top: 0.25rem;
        }

        .checkbox-label {
            display: inline-block;
            margin-left: 25px;
            font-weight: normal;
        }

        .card-header {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Sidenav Menu Start -->
        <?php include_once("sidebar.php"); ?>
        <!-- Sidenav Menu End -->

        <!-- Topbar Start -->
        <?php include_once("header.php"); ?>
        <!-- Topbar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        <div class="page-content">
            <div class="page-container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed d-flex align-items-center">
                                <h2 class="header-title"><?php echo $isUpdate ? 'Edit' : 'Add'; ?> Package</h2>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="packageForm">
                                    <input type="hidden" name="update_id"
                                        value="<?php echo isset($data['package_id']) ? $data['package_id'] : ''; ?>">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Package Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="packageName"
                                                    name="package_name"
                                                    value="<?php echo isset($data['package_name']) ? htmlspecialchars($data['package_name']) : ''; ?>"
                                                    placeholder="Enter package name (e.g., Basic, Pro, Premium)"
                                                    maxlength="100">
                                                <div class="invalid-feedback" id="packageName_error"></div>
                                                <small class="text-muted">Letters, numbers, spaces and hyphens only
                                                    (3-100 characters)</small>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Price (₹) <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="price" name="price"
                                                    value="<?php echo isset($data['price']) ? $data['price'] : ''; ?>"
                                                    placeholder="Enter price in rupees" min="0" max="9999999" step="1">
                                                <div class="invalid-feedback" id="price_error"></div>
                                                <small class="text-muted">Maximum: ₹99,99,999</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Valid Months <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="validMonths"
                                                    name="valid_months"
                                                    value="<?php echo isset($data['valid_months']) ? $data['valid_months'] : ''; ?>"
                                                    placeholder="Enter validity in months" min="1" max="60">
                                                <div class="invalid-feedback" id="validMonths_error"></div>
                                                <small class="text-muted">Between 1 to 60 months</small>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Max Courses <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="maxCourse"
                                                    name="max_course"
                                                    value="<?php echo isset($data['max_course']) ? $data['max_course'] : ''; ?>"
                                                    placeholder="Enter maximum courses allowed" min="0" max="1000">
                                                <div class="invalid-feedback" id="maxCourse_error"></div>
                                                <small class="text-muted">Maximum: 1000 courses</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Max Video Upload <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="maxVideoUpload"
                                                    name="max_video_upload"
                                                    value="<?php echo isset($data['max_video_upload']) ? $data['max_video_upload'] : ''; ?>"
                                                    placeholder="Enter maximum videos allowed" min="0" max="1000">
                                                <div class="invalid-feedback" id="maxVideoUpload_error"></div>
                                                <small class="text-muted">Maximum: 1000 videos</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 class="mt-3 mb-3">Features</h5>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="canAddCourses"
                                                        name="can_add_courses" <?php echo (isset($data['can_add_courses']) && $data['can_add_courses'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="canAddCourses">Can Add
                                                        Courses</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="canAddVideos"
                                                        name="can_add_videos" <?php echo (isset($data['can_add_videos']) && $data['can_add_videos'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="canAddVideos">Can Add
                                                        Videos</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="canAddQuiz"
                                                        name="can_add_quiz" <?php echo (isset($data['can_add_quiz']) && $data['can_add_quiz'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="canAddQuiz">Can Add
                                                        Quiz</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                        id="canAddAssignments" name="can_add_assignments" <?php echo (isset($data['can_add_assignments']) && $data['can_add_assignments'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="canAddAssignments">Can Add
                                                        Assignments</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="canAddGames"
                                                        name="can_add_games" <?php echo (isset($data['can_add_games']) && $data['can_add_games'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="canAddGames">Can Add
                                                        Games</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="packageStatus"
                                                        name="package_status" <?php echo (isset($data['package_status']) && $data['package_status'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="packageStatus">Active
                                                        Status</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button class="btn btn-primary" name="btn" type="submit">
                                                <i
                                                    class="fa-solid fa-<?php echo $isUpdate ? 'pen' : 'plus'; ?> me-2"></i>
                                                <?= $isUpdate ? 'Update Package' : 'Add Package' ?>
                                            </button>
                                            <a href="manage-packages.php" class="btn btn-secondary ms-2">
                                                <i class="fa-solid fa-times me-2"></i>Cancel
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                </div>
                <!-- end row -->
            </div> <!-- container -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <script>
        (function () {
            const form = document.getElementById('packageForm');
            const nameInput = document.getElementById('packageName');
            const priceInput = document.getElementById('price');
            const validMonthsInput = document.getElementById('validMonths');
            const maxCourseInput = document.getElementById('maxCourse');
            const maxVideoInput = document.getElementById('maxVideoUpload');

            const nameError = document.getElementById('packageName_error');
            const priceError = document.getElementById('price_error');
            const validMonthsError = document.getElementById('validMonths_error');
            const maxCourseError = document.getElementById('maxCourse_error');
            const maxVideoError = document.getElementById('maxVideoUpload_error');

            // Helper functions
            function setInvalid(element, errorElement, message) {
                element.classList.add('is-invalid');
                element.classList.remove('is-valid');
                if (errorElement) errorElement.textContent = message;
            }

            function setValid(element, errorElement) {
                element.classList.add('is-valid');
                element.classList.remove('is-invalid');
                if (errorElement) errorElement.textContent = '';
            }

            function clearValidation(element, errorElement) {
                element.classList.remove('is-invalid', 'is-valid');
                if (errorElement) errorElement.textContent = '';
            }

            // Validators
            function validateName() {
                const value = nameInput.value.trim();
                const namePattern = /^[a-zA-Z0-9\s\-]+$/;

                if (value === '') {
                    setInvalid(nameInput, nameError, 'Package name is required.');
                    return false;
                }
                if (value.length < 3) {
                    setInvalid(nameInput, nameError, 'Package name must be at least 3 characters.');
                    return false;
                }
                if (value.length > 100) {
                    setInvalid(nameInput, nameError, 'Package name cannot exceed 100 characters.');
                    return false;
                }
                if (!namePattern.test(value)) {
                    setInvalid(nameInput, nameError, 'Only letters, numbers, spaces and hyphens allowed.');
                    return false;
                }
                setValid(nameInput, nameError);
                return true;
            }

            function validatePrice() {
                const value = priceInput.value.trim();

                if (value === '') {
                    setInvalid(priceInput, priceError, 'Price is required.');
                    return false;
                }
                if (isNaN(value) || value === '') {
                    setInvalid(priceInput, priceError, 'Please enter a valid number.');
                    return false;
                }
                if (value < 0) {
                    setInvalid(priceInput, priceError, 'Price cannot be negative.');
                    return false;
                }
                if (value > 9999999) {
                    setInvalid(priceInput, priceError, 'Price cannot exceed ₹99,99,999.');
                    return false;
                }
                setValid(priceInput, priceError);
                return true;
            }

            function validateValidMonths() {
                const value = validMonthsInput.value.trim();

                if (value === '') {
                    setInvalid(validMonthsInput, validMonthsError, 'Valid months is required.');
                    return false;
                }
                if (isNaN(value) || value === '') {
                    setInvalid(validMonthsInput, validMonthsError, 'Please enter a valid number.');
                    return false;
                }
                if (value < 1) {
                    setInvalid(validMonthsInput, validMonthsError, 'Valid months must be at least 1.');
                    return false;
                }
                if (value > 60) {
                    setInvalid(validMonthsInput, validMonthsError, 'Valid months cannot exceed 60.');
                    return false;
                }
                setValid(validMonthsInput, validMonthsError);
                return true;
            }

            function validateMaxCourse() {
                const value = maxCourseInput.value.trim();

                if (value === '') {
                    setInvalid(maxCourseInput, maxCourseError, 'Max courses is required.');
                    return false;
                }
                if (isNaN(value) || value === '') {
                    setInvalid(maxCourseInput, maxCourseError, 'Please enter a valid number.');
                    return false;
                }
                if (value < 0) {
                    setInvalid(maxCourseInput, maxCourseError, 'Max courses cannot be negative.');
                    return false;
                }
                if (value > 1000) {
                    setInvalid(maxCourseInput, maxCourseError, 'Max courses cannot exceed 1000.');
                    return false;
                }
                setValid(maxCourseInput, maxCourseError);
                return true;
            }

            function validateMaxVideo() {
                const value = maxVideoInput.value.trim();

                if (value === '') {
                    setInvalid(maxVideoInput, maxVideoError, 'Max video upload is required.');
                    return false;
                }
                if (isNaN(value) || value === '') {
                    setInvalid(maxVideoInput, maxVideoError, 'Please enter a valid number.');
                    return false;
                }
                if (value < 0) {
                    setInvalid(maxVideoInput, maxVideoError, 'Max video upload cannot be negative.');
                    return false;
                }
                if (value > 1000) {
                    setInvalid(maxVideoInput, maxVideoError, 'Max video upload cannot exceed 1000.');
                    return false;
                }
                setValid(maxVideoInput, maxVideoError);
                return true;
            }

            // Event listeners
            nameInput.addEventListener('input', validateName);
            nameInput.addEventListener('blur', validateName);

            priceInput.addEventListener('input', validatePrice);
            priceInput.addEventListener('blur', validatePrice);

            validMonthsInput.addEventListener('input', validateValidMonths);
            validMonthsInput.addEventListener('blur', validateValidMonths);

            maxCourseInput.addEventListener('input', validateMaxCourse);
            maxCourseInput.addEventListener('blur', validateMaxCourse);

            maxVideoInput.addEventListener('input', validateMaxVideo);
            maxVideoInput.addEventListener('blur', validateMaxVideo);

            // Form submit
            form.addEventListener('submit', function (e) {
                const isNameValid = validateName();
                const isPriceValid = validatePrice();
                const isValidMonthsValid = validateValidMonths();
                const isMaxCourseValid = validateMaxCourse();
                const isMaxVideoValid = validateMaxVideo();

                if (!isNameValid || !isPriceValid || !isValidMonthsValid || !isMaxCourseValid || !isMaxVideoValid) {
                    e.preventDefault();

                    // Scroll to first error
                    const firstInvalid = document.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fix the errors in the form.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        })();
    </script>

    <?php if ($insertSuccess): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= $isUpdate ? "Package updated successfully!" : "Package added successfully!" ?>',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                window.location.href = 'manage-packages.php';
            });
        </script>
    <?php endif; ?>

    <?php if ($insertError && !empty($errorMsg)): ?>
        <script>
            let errorMsg = '<?php echo $errorMsg; ?>';
            let displayMsg = 'Something went wrong.';

            if (errorMsg.includes('package_name_required')) displayMsg = 'Package name is required.';
            else if (errorMsg.includes('package_name_short')) displayMsg = 'Package name must be at least 3 characters.';
            else if (errorMsg.includes('package_name_long')) displayMsg = 'Package name cannot exceed 100 characters.';
            else if (errorMsg.includes('package_name_invalid')) displayMsg = 'Package name can only contain letters, numbers, spaces and hyphens.';
            else if (errorMsg.includes('package_name_duplicate')) displayMsg = 'Package name already exists.';
            else if (errorMsg.includes('price_required')) displayMsg = 'Price is required.';
            else if (errorMsg.includes('price_invalid')) displayMsg = 'Please enter a valid price.';
            else if (errorMsg.includes('price_negative')) displayMsg = 'Price cannot be negative.';
            else if (errorMsg.includes('price_too_high')) displayMsg = 'Price is too high.';
            else if (errorMsg.includes('valid_months_required')) displayMsg = 'Valid months is required.';
            else if (errorMsg.includes('valid_months_invalid')) displayMsg = 'Please enter a valid number for months.';
            else if (errorMsg.includes('valid_months_min')) displayMsg = 'Valid months must be at least 1.';
            else if (errorMsg.includes('valid_months_max')) displayMsg = 'Valid months cannot exceed 60.';
            else if (errorMsg.includes('max_course_required')) displayMsg = 'Max courses is required.';
            else if (errorMsg.includes('max_course_invalid')) displayMsg = 'Please enter a valid number for max courses.';
            else if (errorMsg.includes('max_course_negative')) displayMsg = 'Max courses cannot be negative.';
            else if (errorMsg.includes('max_course_max')) displayMsg = 'Max courses cannot exceed 1000.';
            else if (errorMsg.includes('max_video_required')) displayMsg = 'Max video upload is required.';
            else if (errorMsg.includes('max_video_invalid')) displayMsg = 'Please enter a valid number for max videos.';
            else if (errorMsg.includes('max_video_negative')) displayMsg = 'Max video upload cannot be negative.';
            else if (errorMsg.includes('max_video_max')) displayMsg = 'Max video upload cannot exceed 1000.';

            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: displayMsg,
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php endif; ?>
</body>

</html>