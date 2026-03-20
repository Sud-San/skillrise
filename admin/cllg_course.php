<?php
include("connection.php");
$status = "";
// Fetech cllgdetilas froorm db
$college_query = mysqli_query($conn, "SELECT clg_id, clg_name FROM cllg_tbl WHERE clg_status='1'");
// Fetech coursedetilas from db
$course_query = mysqli_query($conn, "SELECT course_id, course_name FROM course_tbl WHERE course_status='1'");
// clg_id
// course_id course_fees add_process

// Update fetch Query college course
$clg_id = $course_id = $course_fees = $add_process = "";
if (isset($_GET["update_id"])) {
    $query = "select * from college_course college_course_id='" . $_GET["update_id"] . "'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $clg_id = $row["college_id"];
    $course_id = $row["course_id"];
    $course_fees = $row["college_course_fees"];
    $add_process = $row["college_course_admission_process"];
}

if (isset($_POST['cllg_course'])) {

    $college_id = $_POST['clg_id'];
    $course_id = $_POST['course_id'];

    // Check for duplicate college+course
    // $check = mysqli_query(
    //     $conn,
    //     "SELECT * FROM college_course
    //      WHERE college_id = '$college_id'
    //      AND course_id = '$course_id'"
    // );
    // try to fix the shit
    $update_id = isset($_GET['update_id']) ? $_GET['update_id'] : null;
 // current editing record

$check = mysqli_query(
    $conn,
    "SELECT * FROM college_course
     WHERE college_id = '$college_id'
     AND course_id = '$course_id'
     AND college_course_id != '$update_id'"
);

    if (mysqli_num_rows($check) > 0) {
        $status = "duplicate";
    } else {
        if (!empty($_GET["update_id"])) {
            // Update Query
            $query = "update college_course set college_id='$college_id' , course_id='$course_id' , college_course_fees='" . $_POST['course_fees'] . "' , college_course_admission_process='" . $_POST['add_process'] . "' where college_course_id='" . $_GET['update_id'] . "'";
            mysqli_query($conn, $query);
            header("location:all_cllg_course.php");
        } else {
            // Insert if not duplicate
            $str = "INSERT INTO college_course VALUES
            (NULL,'$college_id','$course_id','" . $_POST['course_fees'] . "',
            '" . $_POST['add_process'] . "','1','$time','$time')";

            $result = mysqli_query($conn, $str);

            if ($result) {
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
    <title>Adminto | Add College Course</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
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
                                <button type="submit" class="btn p-0" data-bs-dismiss="modal" aria-label="Close">[esc]</button>
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
                    <!-- Div For Cllg DropDown -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed d-flex align-items-center">
                                <h2 class="header-title">Add College Course </h2>
                            </div>
                            <div class="card-body">
                                <form class="needs-validation" novalidate method="POST">
                                    <!-- Select Colleg -->
                                    <div class="mb-3">
                                        <label for="clg_id">Select College</label>
                                        <select class="form-control select2" data-toggle="select2" name="clg_id" <?php if(isset($_GET['update_id'])){ echo "disabled"; } ?> required>
                                            <option value="" selected disabled hidden>Choose College</option>
                                            <?php while ($row = mysqli_fetch_assoc($college_query)) { ?>
                                                <option value="<?= $row['clg_id'] ?>"
                                                    <?= ($row['clg_id'] == $clg_id) ? "selected" : ""; ?>>
                                                    <?= $row['clg_name'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please Chosse a valid College.
                                        </div>
                                    </div>
                                    <!-- Select Course -->
                                    <div class="mb-3">
                                        <label for="course_id">Select Course</label>
                                        <select class="form-control select2" data-toggle="select2" name="course_id" <?php if(isset($_GET['update_id'])){ echo "disabled"; } ?> required>
                                            <option value="" selected disabled hidden>Choose Course</option>
                                            <?php while ($row = mysqli_fetch_assoc($course_query)) { ?>
                                                <option  value="<?= $row['course_id']  ?>"
                                                    <?= ($row['course_id'] == $course_id) ? "selected" : ""; ?>>
                                                    <?= $row['course_name'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please Chosse a valid Course.
                                        </div>
                                    </div>
                                    <!-- Sleect Fees For that particular collge  -->
                                    <div class="mb-3">
                                        <label for="example-number" class="form-label">College Course Fees</label>
                                        <input class="form-control" required id="example-number" type="number" name="course_fees" min=0 placeholder="Enter Course Fees As per The Respective College" value="<?php echo $course_fees ?>">
                                        <div class="invalid-feedback">
                                            Please Enter valid Fees.
                                        </div>
                                    </div>
                                    <!-- Enter Admiison Procees -->
                                    <div class="mb-3">
                                        <label for="example-textarea" class="form-label">College Course Admission Process</label>
                                        <textarea class="form-control" id="example-textarea"
                                            rows="3" placeholder="Enter Admission Process" required name="add_process"><?php echo $add_process ?></textarea>
                                        <div class="invalid-feedback">
                                            Please Select Valid Admission Process.
                                        </div>
                                    </div>
                                    <!-- Button To link Cllg Course -->
                                    <button class="btn btn-primary" type="submit" name="cllg_course">
                                        <?php echo isset($_GET['update_id']) ? "Update College Course" : "Add College Course"; ?>
                                    </button>

                                </form>

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

    <!-- Sweet Alert -->
    <?php if ($status == "success") { ?>
        <script>
            Swal.fire({
                title: "College Course Added!",
                text: "The College Course has been added successfully.",
                icon: "success",
                timer: 5000,
                confirmButtonText: "OK",
                timerProgressBar: true
            });
        </script>
    <?php } elseif ($status == "error") { ?>
        <script>
            Swal.fire({
                title: "College Course Error!",
                text: "Something went wrong while adding the state.",
                icon: "error",
                confirmButtonText: "OK"
            });
        </script>
    <?php } elseif ($status == "duplicate") { ?>
        <script>
            Swal.fire({
                title: "Already Linked!",
                text: "This college is already linked with this course.",
                icon: "warning",
                confirmButtonText: "OK"
            });
        </script>
    <?php } ?>
</body>

</html>