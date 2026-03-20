<?php
include "connection.php";
// Show Php code 
$display_category = "select * from category_tbl ORDER BY category_id DESC";
$result = mysqli_query($conn, $display_category);

// Delete code
if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $id = $_GET['category_id'];

    $delete_category = "DELETE FROM category_tbl WHERE category_id='$id'";
    mysqli_query($conn, $delete_category);

    // Very important: redirect with success flag
    header("Location: manage-category.php?deleted=1");
    exit;
}

$i = 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Font Awesome 6.6.0 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta charset="utf-8" />
    <title>Manage Category | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
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
    <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/fixedColumns.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uD1O7M2V3Xw2o4rU6+z3HQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 32px;
            height: 16px;
        }

        .switch input {
            display: none;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #0a0a0aff;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 14px;
            width: 14px;
            left: -3px;
            bottom: 1px;
            background-color: white;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #283ba7ff;
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }

        .slider.round {
            border-radius: 30px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .dataTables_wrapper .dt-buttons {
            margin-top: 0 !important;
        }

        .dataTables_wrapper {
            margin-top: -10px !important;
            /* reduce gap */
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


                <!-- <h1>Manage State</h1> -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title mb-2">Manage Category</h4>
                                <p class="text-muted mb-0">
                                    Manage List of Category
                                </p>
                            </div>

                            <div class="card-body">
                                <div class="d-flex justify-content-start mb-0 ms-0">
                                    <a href="category.php" class="btn btn-primary mb-0">
                                        Add Category
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table id="datatable-buttons"
                                        class="table table-bordered table-striped display nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Sr No</th>
                                                <th
                                                    style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                    Category Id</th>
                                                <th
                                                    style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                    Category Name</th>
                                                <th
                                                    style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                    Category Code</th>
                                                <th>Description</th>
                                                <th>Short Description</th>
                                                <th>Image</th>
                                                <th>Status</th>
                                                <th>Actions</th>

                                                <!-- <th>Start date</th>
                                            <th>Salary</th> -->
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr>
                                                    <td><?php echo $i++ ?></td>
                                                    <td><?php echo $row['category_id'] ?></td>
                                                    <td><?php echo $row['category_name'] ?></td>
                                                    <td><?php echo $row['category_code'] ?></td>
                                                    <td
                                                        style="text-align: justify; vertical-align:middle; white-space: wrap;">
                                                        <?php echo htmlspecialchars($row['category_description']) ?>
                                                    </td>
                                                    <td
                                                        style="text-align: justify; vertical-align:middle; white-space: wrap;">
                                                        <?php echo htmlspecialchars($row['short_description']) ?>
                                                    </td>
                                                    <td>
                                                        <img src="<?php echo "../assets/images/" . $row['img'] ?>"
                                                            alt="Category Image" style="width: 100px; height: 100px;">
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" class="form-check-input toggle-switch"
                                                                data-id="<?= $row['category_id']; ?>"
                                                                <?= ($row['category_status'] == 1) ? "checked" : ""; ?>>
                                                        </div>
                                                    </td>


                                                    <td>
                                                        <a href="#" class="delete-category"
                                                            data-id="<?= $row['category_id']; ?>">
                                                            <i class="fa-solid fa-trash text-danger"></i>
                                                        </a>
                                                        &nbsp;|&nbsp;
                                                        <a href="category.php?category_id=<?= $row['category_id'] ?>">
                                                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                                                        </a>
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
    <!-- Status Toggle -->

    <script>
        document.querySelectorAll('.toggle-switch').forEach(function (toggle) {
            toggle.addEventListener('change', function () {

                let categoryId = this.getAttribute('data-id');
                let status = this.checked ? 1 : 0;

                // Status SweetAlert
                Swal.fire({
                    title: "Status Updated",
                    text: "category status updated successfully!",
                    icon: "success",
                    timer: 1200,
                    showConfirmButton: false
                });

                // Send AJAX
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "update_status.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("category_id=" + categoryId + "&category_status=" + status);
            });
        });
    </script>

    <!-- Delete category -->
    <!-- DELETE category -->
    <script>
        document.querySelectorAll(".delete-category").forEach(btn => {

            btn.addEventListener("click", function (e) {
                e.preventDefault();

                let id = this.getAttribute("data-id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you really want to delete this category?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Delete",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Direct redirect
                        window.location.href = "manage-category.php?category_id=" + id;
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
                text: "category deleted successfully.",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php } ?>


</body>

</html>