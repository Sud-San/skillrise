<?php
include "connection.php";

// --- AJAX endpoint: live duplicate check for course name ---
if (isset($_GET['check_course'])) {
    header('Content-Type: application/json; charset=utf-8');
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    $name_esc = mysqli_real_escape_string($conn, $name);
    $exclude_id = 0;
    if (isset($_GET['course_id']) && intval($_GET['course_id']) > 0) {
        $exclude_id = intval($_GET['course_id']);
    }

    if ($name_esc === '') {
        echo json_encode(['exists' => false]);
        exit;
    }

    // case-insensitive check
    $sql = "SELECT course_id FROM course_tbl WHERE LOWER(course_name) = LOWER('$name_esc') ";
    if ($exclude_id > 0)
        $sql .= " AND course_id != $exclude_id ";
    $sql .= " LIMIT 1";
    $res = mysqli_query($conn, $sql);
    $exists = ($res && mysqli_num_rows($res) > 0) ? true : false;
    echo json_encode(['exists' => $exists]);
    exit;
}
// --- end AJAX endpoint ---

$status = "";
$name = "";
$duration = "";
$description = "";
$eligibility = "";

// Determine course id from GET (view/edit) or POST (form submit)
$course_id = 0;
if (isset($_GET['course-id']) && intval($_GET['course-id']) > 0) {
    $course_id = intval($_GET['course-id']);
} elseif (isset($_POST['course_id']) && intval($_POST['course_id']) > 0) {
    $course_id = intval($_POST['course_id']);
}

// If viewing for edit, fetch existing row
if ($course_id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $str = "SELECT * FROM course_tbl WHERE course_tbl.course_id = $course_id";
    $results = mysqli_query($conn, $str);
    if ($results && mysqli_num_rows($results) > 0) {
        $rows = mysqli_fetch_array($results);
        $name = $rows['course_name'];
        $duration = $rows['duration'];
        $description = $rows['description'];
        $eligibility = $rows['elig_id'];
    }
}


// Handle form submit (insert or update)
if (isset($_POST['btn'])) {
    date_default_timezone_set('Asia/Kolkata');
    $time = date('Y-m-d H:i:s');

    // sanitize incoming values
    $course_name = isset($_POST['course_name']) ? mysqli_real_escape_string($conn, trim($_POST['course_name'])) : '';
    $course_duration = isset($_POST['course_duration']) ? mysqli_real_escape_string($conn, trim($_POST['course_duration'])) : '';
    $course_description = isset($_POST['course_description']) ? mysqli_real_escape_string($conn, trim($_POST['course_description'])) : '';
    $course_eligibility = isset($_POST['elig_id']) ? mysqli_real_escape_string($conn, trim($_POST['elig_id'])) : '';

    // duplicate check: when editing, exclude the current record
    if ($course_id > 0) {
        $check_sql = "SELECT * FROM course_tbl WHERE course_name = '$course_name' AND course_id != $course_id";
    } else {
        $check_sql = "SELECT * FROM course_tbl WHERE course_name = '$course_name'";
    }

    $check_query = mysqli_query($conn, $check_sql);
    if ($check_query && mysqli_num_rows($check_query) > 0) {
        $status = "duplicate";
    } else {
        if ($course_id > 0) {
            // UPDATE existing
            $update_sql = "UPDATE course_tbl SET 
                                course_name = '$course_name',
                                duration = '$course_duration',
                                description = '$course_description',
                                elig_id = '$course_eligibility',
                                updated_at = '$time'
                           WHERE course_id = $course_id";
            if (mysqli_query($conn, $update_sql)) {
                // redirect after successful update
                header('Location: manage-course.php');
                exit;
            } else {
                $status = "error";
            }
        } else {
            // INSERT new
            $insert_sql = "INSERT INTO course_tbl (course_id, course_name, duration, description, elig_id, course_status, created_at, updated_at)
                           VALUES (NULL, '$course_name', '$course_duration', '$course_description', '$course_eligibility', '1', '$time', '$time')";
            if (mysqli_query($conn, $insert_sql)) {
                $status = "success";
            } else {
                $status = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Adminto | Add Course</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Only show error feedback. Hide green valid messages and don't display green outlines. */
        .valid-feedback {
            display: none !important;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Menu -->
        <?php include_once("sidebar.php"); ?>
        <!-- Topbar -->
        <?php include_once("header.php"); ?>

        <!-- Start Page Content here -->
        <div class="page-content">
            <div class="page-container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed d-flex align-items-center">
                                <h2 class="header-title">Add Course</h2>
                            </div>
                            <div class="card-body">

                                <!-- form uses Bootstrap validation markup -->
                                <form method="post" class="needs-validation" novalidate>
                                    <input type="hidden" id="hidden_course_id" name="course_id"
                                        value="<?php echo isset($course_id) ? intval($course_id) : 0; ?>">

                                    <div class="mb-3">
                                        <label class="form-label" for="courseName">Course Name</label>
                                        <input type="text" class="form-control" id="courseName"
                                            placeholder="Enter Course Name"
                                            value="<?php echo htmlspecialchars($name); ?>" name="course_name"
                                            pattern="^[A-Za-z\s]+$" required>
                                        <div class="invalid-feedback">
                                            Please enter a valid course name (letters and spaces only).
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="courseDuration">Course Duration</label>
                                        <input type="text" class="form-control" id="courseDuration"
                                            placeholder="e.g. 3 Years or 36 Months"
                                            value="<?php echo htmlspecialchars($duration); ?>" name="course_duration"
                                            required>
                                        <div class="invalid-feedback">
                                            Enter duration like 3 Years, 2.5 Years, 36 Months, 12 Weeks or 30 Days.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="courseDescription">Course Description</label>
                                        <textarea class="form-control" id="courseDescription"
                                            placeholder="Enter Course Description" name="course_description"
                                            required><?php echo htmlspecialchars($description); ?></textarea>
                                        <div class="invalid-feedback">
                                            Please provide a description.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="courseEligibility">Course Eligibility</label>
                                        <select name="elig_id" id="courseEligibility" class="form-control select2"
                                            data-toggle="select2" required>
                                            <option <?php if ($eligibility == "")
                                                echo "selected"; ?> disabled value="">
                                                Select Eligibility</option>
                                            <?php
                                            $str = "SELECT * from eligibility_tbl";
                                            $elig_options = mysqli_query($conn, $str);
                                            while ($row = mysqli_fetch_array($elig_options)) {
                                                ?>
                                                <option value="<?php echo $row['elig_id']; ?>" <?php if ($row['elig_id'] == $eligibility)
                                                       echo "selected"; ?>>
                                                    <?php echo $row['minimum']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Eligibility cannot start with a number, cannot be only numbers, must be
                                            under 200 characters and not contain HTML tags.
                                        </div>
                                    </div>

                                    <button class="btn btn-primary" name="btn" type="submit">Add Course</button>
                                </form>

                                <!-- Server-side inline status messages (no pop-up for success) -->
                                <?php if ($status == "duplicate") { ?>
                                    <div style="margin-top:10px;color:#b36b00;">Duplicate course name. Please choose a
                                        different name.</div>
                                <?php } elseif ($status == "error") { ?>
                                    <div style="margin-top:10px;color:red;">Something went wrong. Please try again.</div>
                                <?php } ?>

                                <!-- NOTE: SUCCESS inline message removed as requested -->

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- wrapper -->

    <!-- Vendor & App JS -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>

    <!-- Validation + live duplicate-check script -->
    <script>
        (function () {
            const form = document.querySelector('.needs-validation');

            // grab inputs
            const courseInput = document.getElementById('courseName');
            const durationInput = document.getElementById('courseDuration');
            const descriptionInput = document.getElementById('courseDescription');
            const eligibilityInput = document.getElementById('courseEligibility');
            const hiddenCourseId = document.getElementById('hidden_course_id').value || 0;

            const durationPattern = /^\s*([0-9]{1,2}(?:\.[0-9])?)\s*(years?|months?|weeks?|days?)\s*$/i;

            // Helpers: only mark invalid; remove invalid to go neutral (no green state)
            function setInvalid(el, message) {
                el.classList.add('is-invalid');
                el.classList.remove('is-valid');
                const fb = el.parentElement.querySelector('.invalid-feedback');
                if (fb && message) fb.textContent = message;
            }

            function clearInvalid(el) {
                el.classList.remove('is-invalid');
                // remain neutral; don't add is-valid
            }

            // validators
            function validateCourseLocal() {
                // basic client rules (not duplicate)
                const val = courseInput.value.trim();
                if (val === '') {
                    setInvalid(courseInput, 'Course name is required.');
                    return false;
                }
                if (/^[0-9]/.test(val)) {
                    setInvalid(courseInput, 'Course name should not start with a number.');
                    return false;
                }
                if (/^[0-9]+$/.test(val)) {
                    setInvalid(courseInput, 'Course name cannot be only numbers.');
                    return false;
                }
                if (!/^[A-Za-z\s]+$/.test(val)) {
                    setInvalid(courseInput, 'Only letters and spaces allowed.');
                    return false;
                }
                clearInvalid(courseInput);
                return true;
            }

            function validateDuration() {
                const val = durationInput.value.trim();
                if (val === '') {
                    setInvalid(durationInput, 'Duration is required.');
                    return false;
                }
                const m = val.match(durationPattern);
                if (!m) {
                    setInvalid(durationInput, 'Invalid duration format. Eg. 3 Years, 36 Months.');
                    return false;
                }
                const value = parseFloat(m[1]);
                const unit = m[2].toLowerCase();
                let monthsEquivalent;
                if (unit.startsWith('year')) monthsEquivalent = value * 12;
                else if (unit.startsWith('month')) monthsEquivalent = value;
                else if (unit.startsWith('week')) monthsEquivalent = (value * 7) / 30;
                else if (unit.startsWith('day')) monthsEquivalent = value / 30;
                else monthsEquivalent = 0;

                if (!(value > 0)) {
                    setInvalid(durationInput, 'Duration must be greater than zero.');
                    return false;
                }
                if (monthsEquivalent < 1) {
                    setInvalid(durationInput, 'Minimum allowed duration is 1 Month.');
                    return false;
                }
                if (monthsEquivalent > 120) {
                    setInvalid(durationInput, 'Maximum allowed duration is 10 Years.');
                    return false;
                }
                clearInvalid(durationInput);
                return true;
            }

            function validateDescription() {
                const val = descriptionInput.value.trim();
                if (val === '') {
                    setInvalid(descriptionInput, 'Please provide a description.');
                    return false;
                }
                clearInvalid(descriptionInput);
                return true;
            }

            function validateEligibility() {
                const val = eligibilityInput.value.trim();
                if (val === '') {
                    setInvalid(eligibilityInput, 'Eligibility is required.');
                    return false;
                }
                if (!/^[A-Za-z0-9\s,.\-()]+$/.test(val)) {
                    setInvalid(eligibilityInput, 'Invalid characters in eligibility.');
                    return false;
                }
                if (/<[^>]*>/g.test(val)) {
                    setInvalid(eligibilityInput, 'HTML tags are not allowed.');
                    return false;
                }
                if (val.length > 200) {
                    setInvalid(eligibilityInput, 'Eligibility must be less than 200 characters.');
                    return false;
                }
                clearInvalid(eligibilityInput);
                return true;
            }

            // --- AJAX duplicate check for course name (debounced) ---
            let debounceTimer = null;

            function checkDuplicate(name) {
                // call endpoint: same file with ?check_course=1
                const params = new URLSearchParams();
                params.append('check_course', '1');
                params.append('name', name);
                if (hiddenCourseId && parseInt(hiddenCourseId) > 0) params.append('course_id', hiddenCourseId);

                fetch(window.location.pathname + '?' + params.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.exists) {
                            setInvalid(courseInput, 'Course name already exists.');
                        } else {
                            // only clear duplicate error if local validation passes
                            if (validateCourseLocal()) {
                                clearInvalid(courseInput);
                            }
                        }
                    })
                    .catch(err => {
                        // network errors: do nothing (keep current validation state)
                        console.error('Duplicate check failed:', err);
                    });
            }

            function scheduleDuplicateCheck() {
                const val = courseInput.value.trim();
                if (debounceTimer) clearTimeout(debounceTimer);
                // if empty or fails basic local validation, don't call server
                if (val === '' || !/^[A-Za-z\s]+$/.test(val)) {
                    // but still run local validation to show local error
                    validateCourseLocal();
                    return;
                }
                debounceTimer = setTimeout(() => checkDuplicate(val), 500); // 500ms debounce
            }

            // Live listeners: show errors only; remain neutral when valid
            courseInput.addEventListener('input', function () {
                if (this.value.trim() === '') {
                    clearInvalid(this);
                    return;
                }
                // run local validation and schedule duplicate check
                validateCourseLocal();
                scheduleDuplicateCheck();
            });
            courseInput.addEventListener('blur', function () {
                // on blur run full sequence immediately
                if (validateCourseLocal()) {
                    checkDuplicate(courseInput.value.trim());
                }
            });

            durationInput.addEventListener('input', function () {
                if (this.value.trim() === '') {
                    clearInvalid(this);
                    return;
                }
                validateDuration();
            });
            durationInput.addEventListener('blur', validateDuration);

            descriptionInput.addEventListener('input', function () {
                if (this.value.trim() === '') {
                    clearInvalid(this);
                    return;
                }
                validateDescription();
            });
            descriptionInput.addEventListener('blur', validateDescription);

            eligibilityInput.addEventListener('input', function () {
                if (this.value.trim() === '') {
                    clearInvalid(this);
                    return;
                }
                validateEligibility();
            });
            eligibilityInput.addEventListener('blur', validateEligibility);

            // On submit validate all and prevent submit if any invalid
            form.addEventListener('submit', function (event) {
                // ensure we stop any pending duplicate timer and check synchronously if needed
                if (debounceTimer) {
                    clearTimeout(debounceTimer);
                    debounceTimer = null;
                }

                const v1Local = validateCourseLocal();
                // perform synchronous duplicate check to avoid race on submit
                function checkDuplicateSync(name) {
                    try {
                        const xhr = new XMLHttpRequest();
                        const params = 'check_course=1&name=' + encodeURIComponent(name) + '&course_id=' + encodeURIComponent(hiddenCourseId);
                        xhr.open('GET', window.location.pathname + '?' + params, false); // synchronous
                        xhr.send(null);
                        if (xhr.status === 200) {
                            const resp = JSON.parse(xhr.responseText);
                            return resp.exists === true;
                        }
                    } catch (e) {
                        console.error('Sync duplicate check failed', e);
                    }
                    return false;
                }

                let duplicateExists = false;
                if (v1Local) {
                    duplicateExists = checkDuplicateSync(courseInput.value.trim());
                    if (duplicateExists) setInvalid(courseInput, 'Course name already exists.');
                    else clearInvalid(courseInput);
                }

                const v2 = validateDuration();
                const v3 = validateDescription();
                const v4 = validateEligibility();

                if (!(v1Local && !duplicateExists && v2 && v3 && v4)) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');
                    return false;
                }
                // all valid -> allow submit
            }, false);
        })();
    </script>

    <!-- SweetAlert trigger after server processed insertion -->
    <?php if ($status == "success"): ?>
        <script>
            // show success toast for 4 seconds with progress bar, then hide
            Swal.fire({
                icon: 'success',
                title: 'Course added!',
                text: 'Course added successfully.',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
        </script>
    <?php endif; ?>

</body>

</html>