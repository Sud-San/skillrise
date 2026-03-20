<?php
include("connection.php");

// DELETE STATE
if (isset($_GET['state_del_id'])) {
    $state_delete_id = $_GET['state_del_id'];

    $delete_query = "DELETE FROM state_tbl WHERE state_id='" . $state_delete_id . "'";
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
$display_query = "SELECT * FROM state_tbl";
$result = mysqli_query($conn, $display_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Manage State | <?php echo $company_name; ?></title>
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
                                <h4 class="header-title mb-2">Manage State</h4>
                                <p class="text-muted mb-0">Manage list of states.</p>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-start mb-0 ms-0">
                                    <a href="state.php" class="btn btn-primary">
                                        Add State
                                    </a>
                                </div>
                                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Sr No.</th>
                                            <th>State Name</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php while ($state = mysqli_fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $state['state_id']; ?></td>
                                                <td><?php echo $state['state_name']; ?></td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input toggle-switch"
                                                            data-id="<?php echo $state['state_id']; ?>"
                                                            <?php echo ($state['state_status'] == 1) ? "checked" : ""; ?>>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="#" class="deletestate"
                                                        data-id="<?php echo $state['state_id']; ?>">
                                                        <i class="fa-solid fa-trash text-danger"></i>
                                                    </a>

                                                    &nbsp; | &nbsp;

                                                    <a href="state.php?state_update_id=<?php echo $state['state_id']; ?>">
                                                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                                                    </a>
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

<script>
    // TOGGLE STATUS
    $(document).on("change", ".toggle-switch", function() {
        var state_id = $(this).data("id");
        var state_status = $(this).is(":checked") ? 1 : 0;

        $.ajax({
            url: "update_status.php",
            type: "POST",
            data: {
                state_id: state_id,
                state_status: state_status
            },
            success: function(response) {
                Swal.fire({
                    title: "Status Updated",
                    text: "State status changed successfully.",
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
                text: "Do you really want to delete this state?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "manage-state.php?state_del_id=" + id;
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
            text: "State deleted successfully.",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
<?php } ?>

</html>