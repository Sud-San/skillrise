<?php
include "connection.php";

// --- AJAX endpoint: live duplicate check for category name/code ---
if (isset($_GET['check_category'])) {
    header('Content-Type: application/json; charset=utf-8');

    $type = isset($_GET['type']) ? $_GET['type'] : 'name'; // 'name' or 'code'
    $value = isset($_GET['value']) ? trim($_GET['value']) : '';
    $value_esc = mysqli_real_escape_string($conn, $value);
    $exclude_id = 0;
    if (isset($_GET['category_id']) && intval($_GET['category_id']) > 0) {
        $exclude_id = intval($_GET['category_id']);
    }

    if ($value_esc === '') {
        echo json_encode(['exists' => false]);
        exit;
    }

    if ($type === 'name') {
        $sql = "SELECT category_id FROM category_tbl WHERE LOWER(category_name) = LOWER('$value_esc') ";
    } else {
        // code
        $sql = "SELECT category_id FROM category_tbl WHERE category_code = '$value_esc' ";
    }

    if ($exclude_id > 0) {
        $sql .= " AND category_id != $exclude_id ";
    }
    $sql .= " LIMIT 1";

    $res = mysqli_query($conn, $sql);
    $exists = ($res && mysqli_num_rows($res) > 0) ? true : false;
    echo json_encode(['exists' => $exists]);
    exit;
}
// --- end AJAX endpoint ---

// Initialize variables
$category_name = $category_code = $category_description = "";
$status = "";
$category_id = 0;
$status_value = 1; // Default status

// Check if editing existing category
if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    $str = "SELECT * FROM category_tbl WHERE category_id = '$category_id'";
    $result = mysqli_query($conn, $str);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $category_name = $row['category_name'];
        $category_code = $row['category_code'];
        $category_description = $row['category_description'];
        $status_value = $row['category_status'];
    }
}

// Handle form submission
if (isset($_POST['btn_category'])) {
    date_default_timezone_set('Asia/Kolkata');
    $time = date('Y-m-d H:i:s');

    // Get and sanitize form data
    $category_name = isset($_POST['category_name']) ? mysqli_real_escape_string($conn, trim($_POST['category_name'])) : '';
    $category_code = isset($_POST['category_code']) ? mysqli_real_escape_string($conn, trim($_POST['category_code'])) : '';
    $category_description = isset($_POST['category_description']) ? mysqli_real_escape_string($conn, trim($_POST['category_description'])) : '';

    // Get category_id from POST if editing
    if (isset($_POST['category_id']) && intval($_POST['category_id']) > 0) {
        $category_id = intval($_POST['category_id']);
    }

    // BASIC VALIDATIONS
    $errors = array();

    // Validate Category Name
    if (empty($category_name)) {
        $errors[] = "category_name_empty";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $category_name)) {
        $errors[] = "category_name_invalid";
    } elseif (strlen($category_name) < 3) {
        $errors[] = "category_name_short";
    } elseif (strlen($category_name) > 100) {
        $errors[] = "category_name_long";
    }

    // Validate Category Code - NOW NUMERIC ONLY
    if (empty($category_code)) {
        $errors[] = "category_code_empty";
    } elseif (!preg_match("/^[0-9]+$/", $category_code)) {
        $errors[] = "category_code_invalid";
    } elseif (strlen($category_code) < 1) {
        $errors[] = "category_code_short";
    } elseif (strlen($category_code) > 10) {
        $errors[] = "category_code_long";
    }

    // Validate Category Description (optional but if provided, check length)
    if (!empty($category_description) && strlen($category_description) > 500) {
        $errors[] = "category_description_long";
    }

    // If no validation errors, proceed with duplicate checks
    if (empty($errors)) {
        // Duplicate checks
        if ($category_id > 0) {
            $check_name_sql = "SELECT * FROM category_tbl WHERE LOWER(category_name) = LOWER('$category_name') AND category_id != $category_id";
            $check_code_sql = "SELECT * FROM category_tbl WHERE category_code = '$category_code' AND category_id != $category_id";
        } else {
            $check_name_sql = "SELECT * FROM category_tbl WHERE LOWER(category_name) = LOWER('$category_name')";
            $check_code_sql = "SELECT * FROM category_tbl WHERE category_code = '$category_code'";
        }

        $dup_name_q = mysqli_query($conn, $check_name_sql);
        $dup_code_q = mysqli_query($conn, $check_code_sql);

        if ($dup_name_q && mysqli_num_rows($dup_name_q) > 0) {
            $status = "duplicate_category_name";
        } elseif ($dup_code_q && mysqli_num_rows($dup_code_q) > 0) {
            $status = "duplicate_category_code";
        } else {
            // Check if editing or adding new
            if ($category_id > 0) {
                // Update existing category
                $update_category = "UPDATE category_tbl SET 
                                   category_name = '$category_name', 
                                   category_code = '$category_code', 
                                   category_description = '$category_description', 
                                   category_status = '$status_value',
                                   updated_at = '$time' 
                                   WHERE category_id = '$category_id'";
                $query = mysqli_query($conn, $update_category);
                $action = "updated";
            } else {
                // Insert new category
                $insert_category = "INSERT INTO category_tbl 
                                   (category_name, category_code, category_description, category_status, created_at, updated_at) 
                                   VALUES 
                                   ('$category_name', '$category_code', '$category_description', '$status_value', '$time', '$time')";
                $query = mysqli_query($conn, $insert_category);
                $action = "added";
            }

            if ($query) {
                $status = "success";
                // Redirect after successful operation
                header("Location: manage-category.php?status=success&action=" . $action);
                exit;
            } else {
                $status = "error";
            }
        }
    } else {
        // Set status to first error for display
        $status = $errors[0];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $category_id > 0 ? 'Edit' : 'Add'; ?> Category | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%2328a745' viewBox='0 0 20 20'%3E%3Cpath d='M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0zm5.3 7.7l-6 6c-.2.2-.5.3-.7.3s-.5-.1-.7-.3l-3-3c-.4-.4-.4-1 0-1.4s1-.4 1.4 0l2.3 2.3 5.3-5.3c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4z'/%3E%3C/svg%3E") !important;
            background-size: 20px;
            background-position: right 10px center;
            background-repeat: no-repeat;
        }

        .is-valid {
            border-color: #28a745 !important;
        }
    </style>

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="wrapper">
        <!-- Menu -->
        <!-- Sidenav Menu Start -->
        <?php include_once("sidebar.php"); ?>
        <!-- Sidenav Menu End -->

        <!-- Topbar Start -->
        <?php include_once("header.php"); ?>
        <!-- Topbar End -->

        <!-- Search Modal -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-transparent">
                    <form>
                        <div class="card mb-1">
                            <div class="px-3 py-2 d-flex flex-row align-items-center" id="top-search">
                                <i class="ri-search-line fs-22"></i>
                                <input type="search" class="form-control border-0" id="search-modal-input"
                                    placeholder="Search for actions, people,">
                                <button type="submit" class="btn p-0" data-bs-dismiss="modal"
                                    aria-label="Close">[esc]</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        <div class="page-content">
            <div class="page-container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed d-flex align-items-center">
                                <h2 class="header-title"><?php echo $category_id > 0 ? 'Edit' : 'Add'; ?> Category</h2>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">

                                    <div class="mb-3">
                                        <label class="form-label" for="categoryName">Category Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="categoryName"
                                            placeholder="Enter Category Name (e.g., HTML, Python)" name="category_name"
                                            value="<?php echo htmlspecialchars($category_name); ?>" maxlength="100">
                                        <div class="invalid-feedback" id="categoryName_error"></div>
                                        <small class="text-muted">Only letters and spaces allowed (min 3, max 100
                                            characters)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="categoryCode">Category Code <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="categoryCode"
                                            placeholder="Enter Category Code (e.g., 101, 605, 800)" name="category_code"
                                            value="<?php echo htmlspecialchars($category_code); ?>" maxlength="10"
                                            inputmode="numeric" pattern="[0-9]*">
                                        <div class="invalid-feedback" id="categoryCode_error"></div>
                                        <small class="text-muted">Only numbers allowed (max 10 digits)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="categoryDescription">Category Description</label>
                                        <textarea name="category_description" id="categoryDescription"
                                            class="form-control" placeholder="Enter Category description"
                                            maxlength="500"><?php echo htmlspecialchars($category_description); ?></textarea>
                                        <div class="invalid-feedback" id="categoryDescription_error"></div>
                                        <small class="text-muted">Maximum 500 characters</small>
                                    </div>

                                    <button class="btn btn-primary" name="btn_category" type="submit">
                                        <?php echo $category_id > 0 ? "Update Category" : "Add Category"; ?>
                                    </button>
                                    <a href="manage-category.php" class="btn btn-danger">Cancel</a>
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

    <!-- Apex Chart js -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Dashboard js -->
    <script src="assets/js/pages/dashboard.js"></script>

    <script>
        (function () {
            const form = document.querySelector('.needs-validation');
            const nameInput = document.getElementById('categoryName');
            const codeInput = document.getElementById('categoryCode');
            const descInput = document.getElementById('categoryDescription');

            const nameFeedback = document.getElementById('categoryName_error');
            const codeFeedback = document.getElementById('categoryCode_error');
            const descFeedback = document.getElementById('categoryDescription_error');

            const categoryId = '<?php echo $category_id; ?>';

            // Patterns
            const namePattern = /^[A-Za-z\s]+$/;
            const codePattern = /^[0-9]+$/;  // Only numbers allowed

            // Helper functions
            function setInvalid(element, feedbackElement, message) {
                element.classList.add('is-invalid');
                element.classList.remove('is-valid');
                if (feedbackElement) {
                    feedbackElement.textContent = message;
                }
            }

            function setValid(element, feedbackElement) {
                element.classList.add('is-valid');
                element.classList.remove('is-invalid');
                if (feedbackElement) {
                    feedbackElement.textContent = '';
                }
            }

            function clearValidation(element, feedbackElement) {
                element.classList.remove('is-invalid', 'is-valid');
                if (feedbackElement) {
                    feedbackElement.textContent = '';
                }
            }

            // Local validators
            function validateName() {
                const value = nameInput.value.trim();

                if (value === '') {
                    setInvalid(nameInput, nameFeedback, 'Category name is required.');
                    return false;
                }

                if (value.length < 3) {
                    setInvalid(nameInput, nameFeedback, 'Category name must be at least 3 characters.');
                    return false;
                }

                if (value.length > 100) {
                    setInvalid(nameInput, nameFeedback, 'Category name cannot exceed 100 characters.');
                    return false;
                }

                if (!namePattern.test(value)) {
                    setInvalid(nameInput, nameFeedback, 'Only letters and spaces are allowed.');
                    return false;
                }

                if (/^[0-9]/.test(value)) {
                    setInvalid(nameInput, nameFeedback, 'Category name should not start with a number.');
                    return false;
                }

                if (/^[0-9]+$/.test(value)) {
                    setInvalid(nameInput, nameFeedback, 'Category name cannot be only numbers.');
                    return false;
                }

                setValid(nameInput, nameFeedback);
                return true;
            }

            function validateCode() {
                const value = codeInput.value.trim();

                if (value === '') {
                    setInvalid(codeInput, codeFeedback, 'Category code is required.');
                    return false;
                }

                if (value.length < 1) {
                    setInvalid(codeInput, codeFeedback, 'Category code must be at least 1 character.');
                    return false;
                }

                if (value.length > 10) {
                    setInvalid(codeInput, codeFeedback, 'Category code cannot exceed 10 characters.');
                    return false;
                }

                if (!codePattern.test(value)) {
                    setInvalid(codeInput, codeFeedback, 'Only numbers are allowed (0-9).');
                    return false;
                }

                setValid(codeInput, codeFeedback);
                return true;
            }

            function validateDescription() {
                const value = descInput.value.trim();

                if (value.length > 500) {
                    setInvalid(descInput, descFeedback, 'Description cannot exceed 500 characters.');
                    return false;
                }

                if (value === '') {
                    clearValidation(descInput, descFeedback);
                } else {
                    setValid(descInput, descFeedback);
                }
                return true;
            }

            // AJAX duplicate check
            let debounceTimer;

            function checkDuplicate(type, value) {
                const params = new URLSearchParams({
                    check_category: '1',
                    type: type,
                    value: value
                });

                if (categoryId && parseInt(categoryId) > 0) {
                    params.append('category_id', categoryId);
                }

                fetch(window.location.pathname + '?' + params.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            if (type === 'name') {
                                setInvalid(nameInput, nameFeedback, 'Category name already exists.');
                            } else {
                                setInvalid(codeInput, codeFeedback, 'Category code already exists.');
                            }
                        } else {
                            if (type === 'name' && validateName()) {
                                setValid(nameInput, nameFeedback);
                            }
                            if (type === 'code' && validateCode()) {
                                setValid(codeInput, codeFeedback);
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            function scheduleDuplicateCheck(type, value) {
                if (debounceTimer) clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => checkDuplicate(type, value), 500);
            }

            // Event listeners
            nameInput.addEventListener('input', function () {
                const value = this.value.trim();
                if (value === '') {
                    clearValidation(nameInput, nameFeedback);
                    return;
                }
                if (validateName()) {
                    scheduleDuplicateCheck('name', value);
                }
            });

            nameInput.addEventListener('blur', function () {
                const value = this.value.trim();
                if (value !== '' && validateName()) {
                    checkDuplicate('name', value);
                }
            });

            codeInput.addEventListener('input', function () {
                const value = this.value.trim();
                if (value === '') {
                    clearValidation(codeInput, codeFeedback);
                    return;
                }
                if (validateCode()) {
                    scheduleDuplicateCheck('code', value);
                }
            });

            codeInput.addEventListener('blur', function () {
                const value = this.value.trim();
                if (value !== '' && validateCode()) {
                    checkDuplicate('code', value);
                }
            });

            descInput.addEventListener('input', validateDescription);
            descInput.addEventListener('blur', validateDescription);

            // Prevent non-numeric input for code field
            codeInput.addEventListener('keypress', function (e) {
                const char = String.fromCharCode(e.keyCode);
                if (!/[0-9]/.test(char)) {
                    e.preventDefault();
                }
            });

            // Form submit handler
            form.addEventListener('submit', function (e) {
                if (debounceTimer) {
                    clearTimeout(debounceTimer);
                }

                const isNameValid = validateName();
                const isCodeValid = validateCode();
                const isDescValid = validateDescription();

                if (!isNameValid || !isCodeValid || !isDescValid) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Scroll to first error
                    const firstInvalid = document.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }

                    return false;
                }
            });
        })();
    </script>

    <!-- Status messages with SweetAlert -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Category <?php echo $_GET['action']; ?> successfully.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        </script>
    <?php endif; ?>

    <?php if ($status == "success"): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Category saved successfully.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        </script>
    <?php elseif ($status == "error"): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Something went wrong. Please try again.',
                confirmButtonText: 'OK'
            });
        </script>
    <?php elseif ($status == "duplicate_category_name"): ?>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Category Name!',
                text: 'This category name already exists. Please use a different name.',
                confirmButtonText: 'OK'
            });
        </script>
    <?php elseif ($status == "duplicate_category_code"): ?>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Category Code!',
                text: 'This category code already exists. Please use a different code.',
                confirmButtonText: 'OK'
            });
        </script>
    <?php elseif ($status == "category_name_empty"): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Category name is required.',
                confirmButtonText: 'OK'
            });
        </script>
    <?php elseif ($status == "category_name_invalid"): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Category name can only contain letters and spaces.',
                confirmButtonText: 'OK'
            });
        </script>
    <?php elseif ($status == "category_name_short"): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Category name must be at least 3 characters long.',
                confirmButtonText: 'OK'
            });
        </script>
    <?php elseif ($status == "category_code_empty"): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Category code is required.',
                confirmButtonText: 'OK'
            });
        </script>
    <?php elseif ($status == "category_code_invalid"): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Category code can only contain numbers (0-9).',
                confirmButtonText: 'OK'
            });
        </script>
    <?php elseif ($status == "category_code_short"): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Category code must be at least 1 digit.',
                confirmButtonText: 'OK'
            });
        </script>
    <?php elseif ($status == "category_code_long"): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Category code cannot exceed 10 digits.',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>
</body>

</html>