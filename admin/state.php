<?php
include("connection.php");
$status = "";
$time = date('Y-m-d H:i:s'); // Make sure time is defined
$state = "";

if (isset($_GET['state_update_id'])) {
    $str = "select * from state_tbl where state_id='" . $_GET['state_update_id'] . "'";
    $result = mysqli_query($conn, $str);
    $row = mysqli_fetch_array($result);
    $state = $row['state_name'];
}
if (isset($_POST['add_State'])) {
    $state_name = trim($_POST['state_name']);

    // Check if state already exists
    $check_query = mysqli_query($conn, "SELECT * FROM state_tbl WHERE state_name = '$state_name'");
    if (mysqli_num_rows($check_query) > 0) {
        $status = "duplicate";
    } else {
        if (!empty($_GET['state_update_id'])) {
            $update_query = "UPDATE state_tbl set state_name='" . $_POST['state_name'] . "' where state_id='" . $_GET['state_update_id'] . "' ";
            mysqli_query($conn, $update_query);
            header("location:manage-state.php");
        } else {
            // Insert new state
            $str = "INSERT INTO state_tbl (state_id, state_name, state_status, created_at, updated_at) 
                VALUES (NULL, '$state_name', '1', '$time', '$time')";
            $query = mysqli_query($conn, $str);
            if ($query) {
                $status = "success";
            } else {
                $status = "error";
            }
        }
    }
}
// test
if (isset($_POST['ajax_check'])) {
    $state_name = trim($_POST['state_name']);

    $check = mysqli_query($conn, "SELECT * FROM state_tbl WHERE state_name='$state_name'");

    if (mysqli_num_rows($check) > 0) {
        echo "duplicate";
    } else {
        echo "ok";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Add State | <?php echo $company_name; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                                <h2 class="header-title">Add State</h2>
                            </div>
                            <div class="card-body">
                                <form class="needs-validation" novalidate method="POST">
                                    <div class="mb-3">
                                        <label class="form-label" for="state_name">State</label>
                                        <input type="text" class="form-control" id="state_name" name="state_name"
                                            placeholder="Enter State" required value="<?php echo $state; ?>"
                                            pattern="^[A-Za-z]+$">
                                        <div class="invalid-feedback">
                                            Please provide a valid State.
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" type="submit" name="add_State">
                                        <?php echo isset($_GET['state_update_id']) ? "Update State" : "Add State"; ?>
                                    </button>
                                </form>
                                <?php if ($status == "success") { ?>
                                    <script>
                                        Swal.fire({
                                            title: "State Added!",
                                            text: "The state has been added successfully.",
                                            icon: "success",
                                            timer: 5000,
                                            confirmButtonText: "OK",
                                            timerProgressBar: true
                                        });
                                    </script>
                                <?php } elseif ($status == "error") { ?>
                                    <script>
                                        Swal.fire({
                                            title: "Error!",
                                            text: "Something went wrong while adding the state.",
                                            icon: "error",
                                            confirmButtonText: "OK"
                                        });
                                    </script>
                                <?php } elseif ($status == "duplicate") { ?>
                                    <script>
                                        Swal.fire({
                                            title: "Duplicate State!",
                                            text: "This state name already exists. Please enter a unique state.",
                                            icon: "warning",
                                            confirmButtonText: "OK"
                                        });
                                    </script>
                                <?php } ?>
                            </div>
                        </div> <!-- end card-->
                    </div> <!-- end col-->

                </div>
                <!-- end row -->

            </div> <!-- container -->
            <!-- Footer Start -->
            <?php include_once("footer.php"); ?>
            <!-- end Footer -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
        <!-- Wraper class div -->
    </div>
    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <!-- Apex Chart js -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <!-- Projects Analytics Dashboard App js -->
    <script src="assets/js/pages/dashboard.js"></script>
    <script>
        $(document).ready(function () {

            $("#state_name").on("keyup", function () {

                let state_name = $(this).val().trim();

                if (state_name.length < 2) {
                    $("#state_name").removeClass("is-invalid").removeClass("is-valid");
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "", // SAME PAGE 
                    data: {
                        ajax_check: 1,
                        state_name: state_name
                    },
                    success: function (response) {

                        if (response === "duplicate") {
                            $("#state_name").addClass("is-invalid").removeClass("is-valid");
                            $("#state_error").text("This state already exists!");
                        } else {
                            $("#state_name").addClass("is-valid").removeClass("is-invalid");
                            $("#state_error").text("");
                        }

                    }
                });
            });

        });
    </script>
</body>

</html>