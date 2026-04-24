<?php
include("connection.php");

// DELETE CITY
if (isset($_GET['city_del_id'])) {
    $city_id = $_GET['city_del_id'];

    $delete_query = "DELETE FROM city_tbl WHERE city_id='$city_id'";
    $delete_result = mysqli_query($conn, $delete_query);

    if ($delete_result) {
        header("Location: manage-city.php?deleted=1");
        exit;
    } else {
        header("Location: manage-city.php?error=1");
        exit;
    }
}

// FETCH DATA
$display_query = "SELECT * FROM tutor_package_tbl 
                INNER JOIN tutor_tbl ON tutor_tbl.tutor_id = tutor_package_tbl.tutor_id
                INNER JOIN package_tbl ON package_tbl.package_id = tutor_package_tbl.package_id
                ORDER BY purchase_id DESC";
$result = mysqli_query($conn, $display_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Manage Tutor Payment | <?php echo $company_name; ?></title>

    <link rel="shortcut icon" href="../SkillRise_logo1.png">
    <script src="assets/js/config.js"></script>

    <link href="assets/css/vendor.min.css" rel="stylesheet" />
    <link href="assets/css/app.min.css" rel="stylesheet" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" />

    <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>
    <div class="wrapper">

        <?php include("sidebar.php"); ?>
        <?php include("header.php"); ?>

        <div class="page-content">
            <div class="page-container">

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title mb-2">Manage Tutor Payment</h4>
                                <p class="text-muted mb-0">Manage list of tutor payments.</p>
                            </div>


                            <div class="card-body">
                                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Tutor Name</th>
                                            <th>Razorpay ID</th>
                                            <th>Package Name</th>
                                            <th>Payment Amount</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Remaining Days</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php while ($payment = mysqli_fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $payment['purchase_id']; ?></td>
                                                <td><?php echo $payment['tutor_name']; ?></td>
                                                <td><?php echo $payment['razorpay_id']; ?></td>
                                                <td><?php echo $payment['package_name']; ?></td>
                                                <td><?php echo "₹" . $payment['amount_paid']; ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($payment['start_date'])); ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($payment['end_date'])); ?></td>
                                                <td>
                                                    <?php
                                                    $endDate = new DateTime($payment['end_date']);
                                                    $today = new DateTime(); // current date
                                                
                                                    $diff = date_diff($today, $endDate);
                                                    if ($diff->invert == 1) {
                                                        echo "<span style='font-size: 14px;' class='badge bg-danger'>Expired</span>";
                                                    } elseif ($diff->days == 0) {
                                                        echo "<span style='font-size: 14px;' class='badge bg-warning'>Expiring Today</span>";
                                                    } else {
                                                        echo "<span style='font-size: 14px;' class='badge bg-success'>" . $diff->days . " Days</span>";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input toggle-switch"
                                                            data-id="<?= $payment['purchase_id']; ?>"
                                                            <?= ($payment['payment_status'] == 1) ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>

                                </table>

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

    <script src="assets/vendor/datatables/dataTables.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.responsive.min.js"></script>
    <script src="assets/vendor/datatables/responsive.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.buttons.min.js"></script>

    <script src="assets/vendor/datatables/buttons.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/jszip.min.js"></script>
    <script src="assets/vendor/datatables/pdfmake.min.js"></script>
    <script src="assets/vendor/datatables/vfs_fonts.js"></script>
    <script src="assets/vendor/datatables/buttons.html5.min.js"></script>
    <script src="assets/vendor/datatables/buttons.print.min.js"></script>

    <script src="assets/js/components/table-datatable.js"></script>

    <!-- STATUS UPDATE (AJAX) -->
    <script>
        document.querySelectorAll(".toggle-switch").forEach(toggle => {
            toggle.addEventListener("change", function () {
                let id = this.getAttribute("data-id");
                let status = this.checked ? 1 : 0;

                let xhr = new XMLHttpRequest();
                xhr.open("POST", "update_status.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                xhr.onload = function () {
                    Swal.fire({
                        icon: "success",
                        title: "Status Updated",
                        text: "Payment status updated successfully!",
                        timer: 1200,
                        showConfirmButton: false
                    });
                };

                xhr.send("purchase_id=" + id + "&payment_status=" + status);
            });
        });
    </script>

    <!-- DELETE SWEETALERT -->
    <script>
        document.querySelectorAll(".deleteCity").forEach(btn => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                let id = this.getAttribute("data-id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you really want to delete this payment?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Delete",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "manage-tutor-payment.php?purchase_del_id=" + id;
                    }
                });
            });
        });
    </script>

    <!-- SHOW SUCCESS MESSAGE ON DELETE -->
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

</body>

</html>