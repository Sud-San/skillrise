<?php
include 'connection.php';

if (!$conn) {
    echo "<script>Swal.fire('Error', 'Database connection failed.', 'error');</script>";
}

// Initialize variables
$college_course_id = "";
$stream_id = "";
$eligibility = "";
$ccs_status = 1;
$status = "";

// ---------------- FETCH FOR UPDATE ----------------
if (isset($_GET['id']) && intval($_GET['id']) > 0) {
    $id = intval($_GET['id']);
    $fetch_sql = "SELECT * FROM college_course_stream_tbl WHERE id = " . intval($id);
    $fetch_res = mysqli_query($conn, $fetch_sql);

    if ($fetch_res && mysqli_num_rows($fetch_res) > 0) {
        $row = mysqli_fetch_assoc($fetch_res);

        $college_course_id = $row['college_course_id'];
        $stream_id = $row['stream_id'];
        $eligibility = $row['eligibility'];
        $ccs_status = $row['ccs_status'];
    }
}

// ---------------- INSERT / UPDATE QUERY ----------------
if (isset($_POST['btn_clg_course_stream'])) {

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    $college_course_id = mysqli_real_escape_string($conn, trim($_POST['college_course_id']));
    $stream_id = mysqli_real_escape_string($conn, trim($_POST['stream_id']));
    $eligibility = mysqli_real_escape_string($conn, trim($_POST['eligibility']));
    $ccs_status =  1;
    
    if (!preg_match("/^[A-Za-z0-9\s]+$/", $eligibility)) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Invalid Eligibility',
            text: 'Special characters are not allowed. Only letters, numbers, and spaces are allowed.'
        });
    </script>";
    exit;
}


    if ($id > 0) {
        // ---------- UPDATE ----------
        $update_sql = "
            UPDATE college_course_stream_tbl 
            SET 
                college_course_id = '$college_course_id',
                stream_id = '$stream_id',
                eligibility = '$eligibility'
            WHERE id = $id
        ";

        $query = mysqli_query($conn, $update_sql);
        $status = $query ? "success" : "error";

    } else {
        // ---------- INSERT ----------
        $insert_sql = "
            INSERT INTO college_course_stream_tbl 
            (college_course_id, stream_id, eligibility, ccs_status)
            VALUES 
            ('$college_course_id', '$stream_id', '$eligibility', '$ccs_status')
        ";

        $query = mysqli_query($conn, $insert_sql);
        $status = $query ? "success" : "error";
    }

    if ($status == "success") {

    $message = ($id > 0) 
        ? "College course stream updated successfully!" 
        : "College course stream added successfully!";

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: " . json_encode($message) . ",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                window.location.href = 'manage-clg-course-stream.php';
            });
        });
    </script>";
}
else {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Database error occurred!',
                showConfirmButton: true
            });
        });
    </script>";
}

}
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="utf-8" />
        <title>Adminto | Add college course stream</title>
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- SweetAlert2 used only to show success toast like course file -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            /* show invalid feedback (we use Bootstrap markup) */
            .invalid-feedback { display: block; color: red; font-size: 14px; }
        </style>
        <style>
            /* ✅ Keep red border + red invalid icon */
            .form-control.is-invalid,
            .was-validated .form-control:invalid {
                border-color: #dc3545 !important;      /* red */
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23dc3545' viewBox='0 0 20 20'%3E%3Cpath d='M10 0a10 10 0 1 0 0 20A10 10 0 0 0 10 0zm0 15a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6zm1-4.5a1 1 0 1 1-2 0V6a1 1 0 1 1 2 0v4.5z'/%3E%3C/svg%3E") !important;
                background-size: 20px;
                background-position: right 10px center;
                background-repeat: no-repeat;
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
        <!-- Datatables css -->
        <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/fixedColumns.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css" />


        <!-- Icons css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
</head>
<body>
        <div class="wrapper">
            <!-- Menu -->
            <!-- Sidenav Menu Start -->
            <?php  include_once("sidebar.php");?>
            <!-- Sidenav Menu End -->
            <!-- Topbar Start -->
            <?php include_once("header.php");?>
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
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header border-bottom border-dashed d-flex align-items-center">
                                            <h2 class="header-title"><?php echo (isset($_GET['id']) && intval($_GET['id']) > 0) ? 'Edit' : 'Add'; ?> College Course Stream</h2>
                                        </div>
                                        <div class="card-body">
                                                <!-- inside card-body -->
            <form method="POST" class="needs-validation" novalidate>
                <?php if (isset($_GET['id']) && intval($_GET['id']) > 0): ?>
                    <input type="hidden" name="id" value="<?php echo intval($_GET['id']); ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="college_course_id">College Course</label>
                    <select name="college_course_id" id="college_course_id" class="form-control select2" data-toggle="select2" required>
                        <!-- make the placeholder option disabled so it's treated as empty/invalid -->
                        <option value="" disabled <?php echo empty($college_course_id) ? 'selected' : ''; ?>>Select Course</option>
                        <?php
                        $course_sql = "SELECT course_id, course_name FROM course_tbl ORDER BY course_name";
                        $course_result = mysqli_query($conn, $course_sql);

                        if ($course_result) {
                            while($course_row = mysqli_fetch_assoc($course_result)){
                                $selected = ($college_course_id == $course_row['course_id']) ? 'selected' : '';
                                echo "<option value='".htmlspecialchars($course_row['course_id'])."' $selected>".htmlspecialchars($course_row['course_name'])."</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">Please select a course.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="stream_id">Stream</label>
                    <select name="stream_id" id="stream_id" class="form-control select2" data-toggle="select2" required>
                        <option value="" disabled <?php echo empty($stream_id) ? 'selected' : ''; ?>>Select Stream</option>
                        <?php
                        $stream_sql = "SELECT stream_id, stream_name FROM stream_tbl ORDER BY stream_name";
                        $stream_result = mysqli_query($conn, $stream_sql);

                        if ($stream_result) {
                            while($stream_row = mysqli_fetch_assoc($stream_result)){
                                $selected = ($stream_id == $stream_row['stream_id']) ? 'selected' : '';
                                echo "<option value='".htmlspecialchars($stream_row['stream_id'])."' $selected>".htmlspecialchars($stream_row['stream_name'])."</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">Please select a stream.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="eligibility">Eligibility</label>
                    <!-- ADD required here -->
                    <input type="text" class="form-control" id="eligibility"
                        placeholder="Enter Eligibility" name="eligibility"
                        value="<?php echo isset($eligibility) ? htmlspecialchars($eligibility) : ''; ?>"
                        required maxlength="255"
                         pattern="^[A-Za-z0-9\s]+$">
                    <div class="invalid-feedback">Please enter eligibility (only letters, numbers, and spaces are allowed, max 255 characters).</div>
                </div>

                <button class="btn btn-primary" name="btn_clg_course_stream" type="submit">
                    <?php echo (isset($_GET['id']) && intval($_GET['id']) > 0) ? 'Update' : 'Add'; ?> College Course Stream
                </button>
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
        <!-- Wraper class div -->
        </div>

        
         <!-- Vendor js -->
        <!-- <script src="assets/js/vendor.min.js"></script> -->

        <!-- App js -->
        <!-- <script src="assets/js/app.js"></script> -->

        <!-- Apex Chart js -->
        <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

        <!-- Projects Analytics Dashboard App js -->
        <script src="assets/js/pages/dashboard.js"></script>

        <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <script>
        (() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');

  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {

      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();

        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
      }

      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
<script>
(() => {
  'use strict'
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
<script>
document.getElementById('eligibility').addEventListener('input', function(e) {
    // Remove any characters that are not letters, numbers, or spaces
    this.value = this.value.replace(/[^A-Za-z0-9\s]/g, '');
});
</script>
<?php if (!empty($alert_js)) echo "<script>$alert_js</script>"; ?>
                <!-- Footer Start -->
                <?php include_once("footer.php");?>
</body>
</html>