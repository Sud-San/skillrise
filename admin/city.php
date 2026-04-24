<?php
include("connection.php");

###############################################################
# AJAX Duplicate City Name Check
###############################################################
if (isset($_GET['check_city'])) {
    header('Content-Type: application/json; charset=utf-8');

    $name = trim($_GET['name'] ?? '');
    $name = mysqli_real_escape_string($conn, $name);
    $exclude_id = intval($_GET['city_id'] ?? 0);

    if ($name === '') {
        echo json_encode(['exists' => false]);
        exit;
    }

    $sql = "SELECT city_id FROM city_tbl WHERE LOWER(city_name)=LOWER('$name')";
    if ($exclude_id > 0)
        $sql .= " AND city_id != $exclude_id ";
    $sql .= " LIMIT 1";

    $res = mysqli_query($conn, $sql);

    echo json_encode(['exists' => mysqli_num_rows($res) > 0]);
    exit;
}

###############################################################
# FETCH STATES
###############################################################
$stateQuery = "SELECT state_id, state_name FROM state_tbl WHERE state_status = 1 ORDER BY state_name ASC";
$stateResult = mysqli_query($conn, $stateQuery);

$selected_state = '';
$city = '';

###############################################################
# EDIT MODE CHECK
###############################################################
$city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : 0;

if ($city_id > 0) {
    $cityQuery = "SELECT * FROM city_tbl WHERE city_id = '$city_id'";
    $cityResult = mysqli_query($conn, $cityQuery);

    if ($cityResult && mysqli_num_rows($cityResult) > 0) {
        $row = mysqli_fetch_array($cityResult);
        $selected_state = $row['state_id'];
        $city = $row['city_name'];
    }
}

###############################################################
# FORM SUBMIT
###############################################################
$status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $state = intval($_POST['state']);
    $city_name = trim($_POST['city_name']);

    if ($state == 0)
        $status = "state_error";
    if ($city_name == "")
        $status = "city_empty";

    if ($status == "") {

        date_default_timezone_set('Asia/Kolkata');
        $time = date("Y-m-d H:i:s");

        if ($city_id > 0) {
            $check_sql = "SELECT * FROM city_tbl 
                          WHERE city_name='$city_name' AND city_id != $city_id";
        } else {
            $check_sql = "SELECT * FROM city_tbl WHERE city_name='$city_name'";
        }

        $check_query = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_query) > 0) {
            $status = "duplicate";
        } else {

            if ($city_id > 0) {
                $update = "UPDATE city_tbl SET
                           state_id='$state',
                           city_name='$city_name',
                           updated_at='$time'
                           WHERE city_id='$city_id'";

                mysqli_query($conn, $update);
                header("location: manage-city.php?updated=1");
                exit;
            } else {
                $insertQuery = "INSERT INTO city_tbl
                                (city_name, state_id, city_status, created_at, updated_at)
                                VALUES ('$city_name', '$state', '1', '$time', '$time')";

                if (mysqli_query($conn, $insertQuery)) {
                    $status = "success";
                } else {
                    $status = "error";
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Add City | <?php echo $company_name; ?></title>

    <link rel="shortcut icon" href="../SkillRise_logo1.png">
    <script src="assets/js/config.js"></script>
    <link href="assets/css/vendor.min.css" rel="stylesheet" />
    <link href="assets/css/app.min.css" rel="stylesheet" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .is-invalid {
            border: 1px solid red !important;
        }

        .valid-feedback {
            display: none !important;
        }
    </style>
</head>

<body>

    <div class="wrapper">

        <?php include("sidebar.php"); ?>
        <?php include("header.php"); ?>

        <div class="page-content">
            <div class="page-container">

                <div class="row">
                    <div class="col-lg-12">

                        <div class="card">

                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title">
                                    <?= ($city_id > 0) ? "EDIT CITY" : "ADD CITY"; ?>
                                </h4>
                            </div>

                            <div class="card-body">

                                <?php if ($status == "duplicate") { ?>
                                    <div class="alert alert-warning">City name already exists.</div>
                                <?php } elseif ($status == "error") { ?>
                                    <div class="alert alert-danger">Something went wrong.</div>
                                <?php } ?>

                                <!-- FORM -->
                                <form method="POST" id="cityForm">

                                    <input type="hidden" id="hidden_city_id" value="<?= $city_id; ?>">

                                    <!-- STATE -->
                                    <div class="mb-3">
                                        <label class="form-label">State</label>
                                        <select class="form-control select2" data-toggle="select2" name="state">
                                            <option value="" hidden disabled selected>Select State</option>

                                            <?php while ($row = mysqli_fetch_array($stateResult)) { ?>
                                                <option value="<?= $row['state_id']; ?>"
                                                    <?= ($row['state_id'] == $selected_state) ? "selected" : ""; ?>>
                                                    <?= $row['state_name']; ?>
                                                </option>
                                            <?php } ?>

                                        </select>
                                        <small id="errState" class="text-danger" style="display:none;margin-top:5px;">
                                            Please select a state.
                                        </small>
                                    </div>

                                    <!-- CITY -->
                                    <div class="mb-3">
                                        <label class="form-label">City Name</label>

                                        <input type="text" class="form-control" id="cityName" name="city_name"
                                            placeholder="City" value="<?= $city; ?>">

                                        <small id="errRequired" class="text-danger"
                                            style="display:none;margin-top:5px;">
                                            City name is required.
                                        </small>

                                        <small id="errNumber" class="text-danger" style="display:none;margin-top:5px;">
                                            Numbers are not allowed.
                                        </small>

                                        <small id="errSpecial" class="text-danger" style="display:none;margin-top:5px;">
                                            Special characters are not allowed.
                                        </small>

                                        <small id="errDuplicate" class="text-danger"
                                            style="display:none;margin-top:5px;">
                                            City name already exists.
                                        </small>
                                    </div>

                                    <button class="btn btn-primary" type="submit">
                                        <?= ($city_id > 0) ? "Update City" : "Add City"; ?>
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <?php include("footer.php"); ?>

        </div>

    </div>

    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
        /* =============================================
   CHARACTER VALIDATION
============================================= */

        const cityInput = document.getElementById("cityName");
        const cityId = document.getElementById("hidden_city_id").value || 0;

        const errRequired = document.getElementById("errRequired");
        const errNumber = document.getElementById("errNumber");
        const errSpecial = document.getElementById("errSpecial");
        const errDuplicate = document.getElementById("errDuplicate");
        const stateSelect = document.querySelector("select[name='state']");
        const errState = document.getElementById("errState");

        let debounceTimer = null;

        cityInput.addEventListener("input", () => {

            const value = cityInput.value.trim();

            hideAllErrors();

            if (value === "") {
                errRequired.style.display = "block";
                cityInput.classList.add("is-invalid");
                return;
            }

            if (/[0-9]/.test(value)) {
                errNumber.style.display = "block";
                cityInput.classList.add("is-invalid");
                return;
            }

            if (/[^A-Za-z\s]/.test(value)) {
                errSpecial.style.display = "block";
                cityInput.classList.add("is-invalid");
                return;
            }

            cityInput.classList.remove("is-invalid");

            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                checkDuplicate(value);
            }, 400);
        });

        function hideAllErrors() {
            errRequired.style.display = "none";
            errNumber.style.display = "none";
            errSpecial.style.display = "none";
            errDuplicate.style.display = "none";
            errState.style.display = "none";
        }

        /* Duplicate check */
        function checkDuplicate(val) {
            const params = new URLSearchParams();
            params.append("check_city", 1);
            params.append("name", val);
            params.append("city_id", cityId);

            fetch("city.php?" + params.toString())
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        cityInput.classList.add("is-invalid");
                        errDuplicate.style.display = "block";
                    }
                });
        }

        /* =============================================
           LIVE STATE VALIDATION
        ============================================= */
        stateSelect.addEventListener("change", () => {
            if (stateSelect.value !== "") {
                errState.style.display = "none";
                stateSelect.classList.remove("is-invalid");
            }
        });

        /* =============================================
           SUBMIT VALIDATION
        ============================================= */
        document.getElementById("cityForm").addEventListener("submit", function (event) {

            hideAllErrors();

            let state = stateSelect.value;
            let city = cityInput.value.trim();

            if (state === "") {
                errState.style.display = "block";
                stateSelect.classList.add("is-invalid");
                event.preventDefault();
                return;
            }

            if (city === "") {
                errRequired.style.display = "block";
                event.preventDefault();
                return;
            }

            if (cityInput.classList.contains("is-invalid")) {
                event.preventDefault();
                return;
            }

            event.preventDefault();

            Swal.fire({
                icon: 'success',
                title: 'City Saved!',
                text: 'City saved successfully.',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });

            setTimeout(() => {
                event.target.submit();
            }, 2500);
        });
    </script>

</body>

</html>