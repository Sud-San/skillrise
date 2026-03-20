<?php
include("connection.php");

// DELETE STATE
if (isset($_GET['state_del_id'])) {
    $state_delete_id = $_GET['state_del_id'];

    $delete_query = "DELETE FROM user_payment_tbl WHERE state_id='" . $state_delete_id . "'";
    $delete_result = mysqli_query($conn, $delete_query);

    if ($delete_result) {
        header("Location: manage-state.php?deleted=1");
        exit;
    } else {
        header("Location: manage-state.php?error=1");
        exit;
    }
}

// DISPLAY CODE
$display_query = "SELECT user_tbl.user_id, user_tbl.user_name, user_tbl.user_email, user_tbl.mobile as user_phone, user_tbl.user_status, user_tbl.created_at as user_created_at, user_tbl.profile_pic as user_profile_pic, 
                tutor_tbl.*, course_tbl.*, user_payment_tbl.*, tutor_profile_tbl.*, category_tbl.* FROM user_payment_tbl
                LEFT JOIN user_tbl ON user_tbl.user_id = user_payment_tbl.user_id
                LEFT JOIN tutor_tbl ON tutor_tbl.tutor_id = user_payment_tbl.tutor_id
                LEFT JOIN tutor_profile_tbl ON tutor_profile_tbl.tutor_id = tutor_tbl.tutor_id
                LEFT JOIN course_tbl ON course_tbl.course_id = user_payment_tbl.course_id
                LEFT JOIN category_tbl ON category_tbl.category_id = course_tbl.category_id
                ORDER BY user_payment_tbl.user_payment_id DESC";
$result = mysqli_query($conn, $display_query);
$i = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Manage User Payment | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <script src="assets/js/config.js"></script>

    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
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
                                <h4 class="header-title mb-2">Manage User Payments</h4>
                                <p class="text-muted mb-0">Manage list of User Payments.</p>
                            </div>
                            <div class="card-body">
                                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Sr No</th>
                                            <th>Payment ID</th>
                                            <th>Razorpay ID</th>
                                            <th>User Name</th>
                                            <th>Tutor Name</th>
                                            <th>Course Name</th>
                                            <th>Payment Amount</th>
                                            <th>Payment Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php while ($payment = mysqli_fetch_array($result)) { 
                                             $tutorJson = htmlspecialchars(
                                                json_encode($payment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            $userJson = htmlspecialchars(
                                                json_encode($payment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            $courseJson = htmlspecialchars(
                                                json_encode($payment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            $categoryJson = htmlspecialchars(
                                                json_encode($payment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                            <tr>
                                                <td><?php echo ++$i; ?></td>
                                                <td><?php echo $payment['user_payment_id']; ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $payment['razorpay_id']; ?></td>
                                                <td style="width: 100px; cursor: pointer; text-align: center; vertical-align:middle; white-space: wrap;" class="view-user text-primary" data-user="<?= $userJson ?>"><?php echo $payment['user_name']; ?></td>
                                                <td style="width: 100px; cursor: pointer; text-align: center; vertical-align:middle; white-space: wrap;" class="view-tutor text-primary" data-tutor="<?= $tutorJson ?>"><?php echo $payment['tutor_name']; ?></td>
                                                <td style="width: 100px; cursor: pointer; text-align: center; vertical-align:middle; white-space: wrap;" class="view-course text-primary" data-course="<?= $courseJson ?>" data-category="<?= $categoryJson ?>"><?php echo $payment['course_title']; ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo "₹" . $payment['amount']; ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo date("d-m-Y", strtotime($payment['payment_date'])); ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                    <?php
                                                    if ($payment['payment_status'] == 1) {
                                                        echo '<span style="font-size:14px;" class="badge bg-success">Completed</span>';
                                                    } else {
                                                        echo '<span style="font-size:14px;" class="badge bg-danger">Failed</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <!-- <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input toggle-switch"
                                                            data-id="<?php //echo $payment['user_payment_id']; ?>"
                                                            <?php //echo ($payment['payment_status'] == 1) ? "checked" : ""; ?>>
                                                    </div>
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



</body>
<script src="assets/js/view-modals.js"></script>

<script>
    // TOGGLE STATUS
    $(document).on("change", ".toggle-switch", function() {
        var payment_id = $(this).data("id");
        var payment_status = $(this).is(":checked") ? 1 : 0;

        $.ajax({
            url: "update_status.php",
            type: "POST",
            data: {
                payment_id: payment_id,
                payment_status: payment_status
            },
            success: function(response) {
                Swal.fire({
                    title: "Status Updated",
                    text: "Payment status changed successfully.",
                    icon: "success",
                    timer: 1000,
                    showConfirmButton: false
                });
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: "Error",
                    text: "Failed to update status!",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                console.log("AJAX Error:", error, xhr.responseText);
            }
        });
    });

    // DELETE STATE ALERT
    document.querySelectorAll(".deletestate").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();

            let id = this.getAttribute("data-id");

            Swal.fire({
                title: "Are you sure?",
                text: "Do you really want to delete this    payment?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "manage-user-payment.php?payment_del_id=" + id;
                }
            });
        });
    });
</script>

<!-- SUCCESS MESSAGE AFTER DELETE -->
<?php if (isset($_GET['deleted'])) { ?>
    <script>
        Swal.fire({
            icon: "success",
            title: "Deleted!",
            text: "Payment deleted successfully.",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
<?php } ?>

</html>