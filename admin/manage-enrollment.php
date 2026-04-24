<?php
include_once("auth_check.php");
include_once("connection.php");
$i=0;
// DELETE ENROLLMENT
if (isset($_GET['enrollment_del_id'])) {
    $enrollment_delete_id = $_GET['enrollment_del_id'];

    $delete_query = "DELETE FROM enrollments_tbl WHERE enrollment_id='$enrollment_delete_id'";
    $delete_result = mysqli_query($conn, $delete_query);

    if ($delete_result) {
        header("Location: manage-enrollment.php?deleted=1");
        exit;
    } else {
        header("Location: manage-enrollment.php?error=1");
        exit;
    }
}

// DISPLAY DATA
$display_query = "SELECT enrollments_tbl.*, 
                    user_tbl.user_id, user_tbl.user_name, user_tbl.user_email, user_tbl.mobile, user_tbl.user_status, user_tbl.created_at as user_created_at, user_tbl.profile_pic as user_profile_pic,
                    tutor_tbl.tutor_id, tutor_tbl.tutor_name, tutor_tbl.tutor_email, tutor_tbl.tutor_phone, tutor_tbl.tutor_status, tutor_tbl.created_at, tutor_profile_tbl.profile_pic, tutor_profile_tbl.country,
                    course_tbl.course_id, course_tbl.course_title, course_tbl.course_description , course_tbl.course_level, course_tbl.price, course_tbl.course_status, course_tbl.total_lesson,
                    user_payment_tbl.payment_status,
                    category_tbl.category_name
                FROM enrollments_tbl
                    LEFT JOIN user_tbl ON enrollments_tbl.user_id = user_tbl.user_id
                    LEFT JOIN tutor_tbl ON enrollments_tbl.tutor_id = tutor_tbl.tutor_id
                    LEFT JOIN tutor_profile_tbl ON tutor_tbl.tutor_id = tutor_profile_tbl.tutor_id
                    LEFT JOIN course_tbl ON enrollments_tbl.course_id = course_tbl.course_id
                    LEFT JOIN category_tbl ON course_tbl.category_id = category_tbl.category_id
                    LEFT JOIN user_payment_tbl ON enrollments_tbl.user_payment_id = user_payment_tbl.user_payment_id
                    ORDER BY enrollment_id DESC";
$result = mysqli_query($conn, $display_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Manage Enrollment | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="../SkillRise_logo1.png">
    <script src="assets/js/config.js"></script>

    <link href="assets/css/vendor.min.css" rel="stylesheet" />
    <link href="assets/css/app.min.css" rel="stylesheet" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" />

    <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="wrapper">

        <?php include_once("sidebar.php"); ?>
        <?php include_once("header.php"); ?>

        <div class="page-content">
            <div class="page-container">

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title mb-2">Manage Enrollment</h4>
                                <p class="text-muted mb-0">
                                    Manage List Of Enrollment
                                </p>
                            </div>

                            <div class="card-body">
                                <!-- <div class="d-flex justify-content-start mb-0 ms-0">
                                    <a href="add-enrollment.php" class="btn btn-primary">
                                        Add Enrollment
                                    </a>
                                </div> -->

                                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Sr No</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Enrollment ID</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">User Name</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Tutor Name</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Course Name</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Payment Status</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Enrollment Date</th>
                                            <!-- <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Amount</th> -->
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Progress</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Completion Date</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Certificate</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Enrollment Status</th>
                                            <!-- <th>Action</th> -->
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php while ($enroll = mysqli_fetch_array($result)) { 
                                            $tutorJson = htmlspecialchars(
                                                json_encode($enroll, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            $userJson = htmlspecialchars(
                                                json_encode($enroll, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            $courseJson = htmlspecialchars(
                                                json_encode($enroll, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            $categoryJson = htmlspecialchars(
                                                json_encode($enroll, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                        ?>
                                            <tr>
                                                <td><?php echo ++$i; ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $enroll['enrollment_id']; ?></td>
                                                <td style="width: 100px; cursor: pointer; text-align: center; vertical-align:middle; white-space: wrap;" class="view-user text-primary" data-user="<?= $userJson ?>"><?php echo $enroll['user_name']; ?></td>
                                                <td style="width: 100px; cursor: pointer; text-align: center; vertical-align:middle; white-space: wrap;" class="view-tutor text-primary" data-tutor="<?= $tutorJson ?>"><?php echo $enroll['tutor_name']; ?></td>
                                                <td style="width: 100px; cursor: pointer; text-align: center; vertical-align:middle; white-space: wrap;" class="view-course text-primary" data-course="<?= $courseJson ?>" data-category="<?= $categoryJson ?>"><?php echo $enroll['course_title']; ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php if ($enroll['payment_status'] == 1) { echo "<span><i class='fa fa-check'></i></span>"; } else { echo "<span> <i class='fa fa-times'></i></span>"; } ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: nowrap;"><?php echo date_format(new DateTime($enroll['enrolled_at']), 'd-m-Y'); ?></td>
                                                <!-- <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php //echo "₹".$enroll['amount']; ?></td> -->
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $enroll['progress']; ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: nowrap;"><?php if ($enroll['completed_at'] != null) { echo date_format(new DateTime($enroll['completed_at']), 'd-m-Y'); } else { echo "Not Completed"; } ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php if ($enroll['certificate_issued'] == 1) { echo "<span style='font-size: 14px;' class='badge bg-success'>Issued</span>"; } else { echo "<span style='font-size: 14px;' class='badge bg-danger'>Not Issued</span>"; } ?></td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" 
                                                               class="form-check-input toggle-switch"
                                                               data-id="<?php echo $enroll['enrollment_id']; ?>"
                                                               <?php echo ($enroll['enrollment_status'] == "active") ? "checked" : ""; ?>>
                                                    </div>
                                                </td>

                                                <!-- <td>
                                                    <a href="#" class="deleteenrollment"
                                                       data-id="<?php //echo $enroll['enrollment_id']; ?>">
                                                        <i class="fa-solid fa-trash text-primary"></i>
                                                    </a>

                                                    &nbsp; | &nbsp;

                                                    <a href="add-enrollment.php?enrollment_update_id=<?php //echo $enroll['enrollment_id']; ?>">
                                                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                                                    </a>
                                                </td> -->
                                            </tr>
                                        <?php } ?>
                                    </tbody>

                                </table>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <?php include_once("footer.php"); ?>
        </div>

    </div>

    <!-- Scripts -->
           <!-- Theme Settings -->
        <?php include("theme-setting.php");?>
      <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <!-- Apex Chart js -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <!-- Projects Analytics Dashboard App js -->
    <script src="assets/js/pages/dashboard.js"></script>
    <script src="assets/vendor/datatables/dataTables.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.responsive.min.js"></script>
    <script src="assets/vendor/datatables/responsive.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/fixedColumns.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.fixedHeader.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.buttons.min.js"></script>
    <script src="assets/vendor/datatables/buttons.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/buttons.html5.min.js"></script>
    <script src="assets/vendor/datatables/buttons.print.min.js"></script>
    <script src="assets/vendor/datatables/jszip.min.js"></script>
    <script src="assets/vendor/datatables/pdfmake.min.js"></script>
    <script src="assets/vendor/datatables/vfs_fonts.js"></script>
    <script src="assets/vendor/datatables/dataTables.keyTable.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.select.min.js"></script>
    <script src="assets/js/components/table-datatable.js"></script>

<script>
    // UPDATE STATUS
    $(document).on("change", ".toggle-switch", function() {
        var enrollment_id = $(this).data("id");
        var enrollment_status = $(this).is(":checked") ? "active" : "inactive";

        $.ajax({
            url: "update_status.php",
            type: "POST",
            data: {
                enrollmentid: enrollment_id,
                enrollmentstatus: enrollment_status
            },
            success: function() {
                Swal.fire({
                    title: "Status Updated",
                    text: "Enrollment status changed successfully.",
                    icon: "success",
                    timer: 1000,
                    showConfirmButton: false
                });
            },
            error: function() {
                Swal.fire("Error", "Failed to update status!", "error");
            }
        });
    });

    // DELETE ENROLLMENT
    document.querySelectorAll(".deleteenrollment").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();

            let id = this.getAttribute("data-id");

            Swal.fire({
                title: "Are you sure?",
                text: "Do you really want to delete this enrollment?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "manage-enrollment.php?enrollment_del_id=" + id;
                }
            });
        });
    });

</script>

<script src="assets/js/view-modals.js"></script>

<!-- Delete Sweetalert -->
<?php if (isset($_GET['deleted'])) { ?>
    <script>
        Swal.fire({
            icon: "success",
            title: "Deleted!",
            text: "Enrollment deleted successfully.",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
<?php } ?>

</body>
</html>