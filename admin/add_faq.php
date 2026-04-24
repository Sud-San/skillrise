<?php
include("connection.php");
$status = "";
$faq_ques = "";
$faq_ans = "";
if (isset($_GET['faq_update'])) {
    $str = "select * from faq where id='" . $_GET['faq_update'] . "'";
    $result = mysqli_query($conn, $str);
    $row = mysqli_fetch_array($result);
    $faq_ques = $row['question'];
    $faq_ans = $row['answer'];
}
//Insert FAQ questin And Answer


if (isset($_POST['add_faq'])) {
    // Server-side validation
    if (!preg_match("/^[A-Za-z\s.,?!-]+$/", $_POST['question']) || !preg_match("/^[A-Za-z\s.,?!-]+$/", $_POST['answer'])) {
        $status = "error";
    } else {
        if (!empty($_GET['faq_update'])) {
            $update_query = "UPDATE faq set question='" . $_POST['question'] . "' ,answer='" . $_POST['answer'] . "' where id='" . $_GET['faq_update'] . "' ";
            $result = mysqli_query($conn, $update_query);
            // header("location:manage_faq.php");
            if ($result) {
                $status = "update";
            } else {
                $status = "error";
            }
        } else {
            // Escaping input to remove backslashes and special characters
            $question = mysqli_real_escape_string($conn, $_POST['question']);
            $answer = mysqli_real_escape_string($conn, $_POST['answer']);
            $str = "INSERT INTO faq VALUES (NULL, '$question', '$answer', '1', '$time', '$time')";
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
    <title>Add FAQ | <?php echo $company_name; ?></title>
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
                                <h2 class="header-title">Add FAQ</h2>
                            </div>
                            <div class="card-body">
                                <form class="needs-validation" method="POST">
                                    <div class="mb-3">
                                        <div class="position-relative mb-3">
                                            <label class="form-label" for="validationTooltip04">FAQ Question</label>
                                            <input type="text" class="form-control" id="validationTooltip04"
                                                placeholder="Enter Faq Question" required
                                                value="<?php echo $faq_ques; ?>" name="question"
                                                pattern="[A-Za-z\s.,?!-]+"
                                                title="Only alphabets and spaces are allowed">
                                            <div class="invalid-feedback">
                                                Please provide a valid FAQ Question.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="position-relative mb-3">
                                            <label class="form-label" for="validationTooltip05">FAQ Answer</label>
                                            <input type="text" class="form-control" id="validationTooltip05"
                                                placeholder="Enter Faq Answer" required value="<?php echo $faq_ans; ?>"
                                                name="answer" pattern="[A-Za-z\s.,?!-]+"
                                                title="Only alphabets and spaces are allowed">
                                            <div class="invalid-feedback">
                                                Please provide a valid FAQ Answer.
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" type="submit" name="add_faq">
                                        <?php echo isset($_GET['faq_update']) ? "Update FAQ" : "Add FAQ"; ?>
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

</body>

</html>
<!-- SWEEET ALERT  -->
<?php if ($status == "success") { ?>
    <script>
        Swal.fire({
            title: "FAQ Added!",
            text: "The FAQ has been added successfully.",
            icon: "success",
            confirmButtonText: "OK"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "manage_faq.php";
            }
        });
    </script>

<?php } elseif ($status == "error") { ?>
    <script>
        Swal.fire({
            title: "Error!",
            text: "Something went wrong while adding the FAQ.",
            icon: "error",
            confirmButtonText: "OK"
        });
    </script>

<?php } elseif ($status == "update") { ?>
    <script>
        Swal.fire({
            title: "FAQ UPDATED!",
            text: "The FAQ has been updated successfully.",
            icon: "success",
            confirmButtonText: "OK"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "manage_faq.php";
            }
        });
    </script>
<?php } ?>