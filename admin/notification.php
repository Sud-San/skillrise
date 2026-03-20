<?php
include "connection.php";
// DB connection check
if (!$conn) {
    die("Database connection failed.");
}
$i=0;
// Handle Delete Request (Secure with prepared statement)
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {

    $id = intval($_GET['delete_id']);

    $delete = "DELETE FROM notification_tbl WHERE notification_id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: all-notification.php?deleted=1");
        exit;
    } else {
        header("Location: all-notification.php?error=1");
        exit;
    }
}

// DISPLAY CODE
$display_query = "
SELECT 
    *
FROM notification_tbl
LEFT JOIN user_tbl ON notification_tbl.user_id = user_tbl.user_id
ORDER BY notification_id DESC;
";

$result = mysqli_query($conn, $display_query);


?>
<!DOCTYPE html>
<html lang="en">

<head>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta charset="utf-8" />
    <title>Manage Notification | <?php echo $company_name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font Awesome 6.6.0 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <!-- Datatables css -->
    <<link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/fixedColumns.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uD1O7M2V3Xw2o4rU6+z3HQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        img.logo {
            width: 90px;
            height: 60px;
            object-fit: contain;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: #fff;
            padding: 4px;
        }


        /* Clean horizontal scrollbar */
        .table-responsive {
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #b5b5b5;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #8f8f8f;
        }

        .dataTables_paginate {
            display: flex !important;
            justify-content: center !important;
            margin-top: 15px;
        }

        /* Search box alignment */
        .dataTables_filter {
            float: right !important;
            text-align: right;
            margin-bottom: 10px;
        }

        .dataTables_wrapper {
            width: 100% !important;
        }
    </style>


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


                <!-- <h1>Manage State</h1> -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h1 class="header-title mb-2">Manage Notification</h1>
                                <p class="text-muted mb-0">
                                    Manage List of Notification.
                                </p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive w-100">
                                    <table id="datatable-buttons" class="table table-bordered table-striped display nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Sr No</th>
                                                <th>Notification ID</th>
                                                <th>User Name</th>
                                                <th>Message</th>
                                                <th>Notification Date</th>
                                                <th>Notification Time</th>
                                                <th>Notification Type</th>
                                                <th>Notification Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($state = mysqli_fetch_array($result)) { ?>
                                                <tr>
                                                    <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo ++$i; ?></td>
                                                    <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $state['notification_id']; ?></td>
                                                    <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $state['user_name']; ?></td>
                                                    <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $state['message']; ?></td>
                                                    <?php
                                                        $createdAt = new DateTime($state['created_at']);
                                                    ?>
                                                    <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $createdAt->format("d-m-Y"); ?></td>
                                                    <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $createdAt->format("h:i A"); ?></td>
                                                    <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $state['notification_type']; ?></td>
                                                    <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox"
                                                                class="form-check-input toggle-switch"
                                                                id="switch_<?php echo $state['notification_id']; ?>"
                                                                data-id="<?php echo $state['notification_id']; ?>"
                                                                <?php echo ($state['is_read'] == 1) ? 'checked' : ''; ?>>
                                                        </div>
                                                    </td>
                                                </tr>

                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div> <!-- end row-->

            </div> <!-- container -->

            <!-- Footer Start -->
            <?php include_once("footer.php"); ?>
            <!-- end Footer -->

        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>
    <!-- Theme Settings -->
    <?php include("theme-setting.php"); ?>
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
        function showFullAddress(fullAddress) {
            Swal.fire({
                title: "Full Address",
                html: fullAddress.replace(/\n/g, "<br>"),
                width: 600,
                confirmButtonText: "Close"
            });
        }
    </script>

</body>

<script>
    $('.button-menu-mobile').on('click', function() {
        setTimeout(function() {
            $('#datatable-buttons').DataTable().columns.adjust();
        }, 300);
    });
</script>

<script>
    $(document).ready(function() {

        $(document).on('change', '.toggle-switch', function() {

            var notification_id = $(this).data('id'); // FIXED
            var notification_status = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: 'update_status.php',
                type: 'POST',
                data: {
                    notification_id: notification_id,
                    notification_status: notification_status
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Status Updated!',
                        text: 'College status changed successfully.',
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong while updating status.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });

        });

    });
</script>

<script>
    document.querySelectorAll(".deleteNotification").forEach(btn => {

        btn.addEventListener("click", function(e) {
            e.preventDefault();

            let notification_id = this.getAttribute("data-id");

            Swal.fire({
                title: "Are you sure?",
                text: "Do you really want to delete this notification?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel"
            }).then((result) => {

                if (result.isConfirmed) {
                    window.location.href = "all-notification.php?delete_id=" + notification_id;
                }

            });

        });

    });
</script>
<?php if (isset($_GET['deleted'])) { ?>
    <script>
        Swal.fire({
            icon: "success",
            title: "Deleted!",
            text: "Notification deleted successfully.",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
<?php } ?>
<script>
    function showDescription(desc) {
        Swal.fire({
            title: 'Notification Description',
            html: desc.replace(/\n/g, '<br>'), // Convert line breaks to <br>
            showConfirmButton: true,
            width: 600
        });
    }
</script>

</html>