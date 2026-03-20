<?php
include "connection.php";

//Show Code
// $display_cousre_stream = "select * from course_stream";
$display_cousre_stream = "
    SELECT * FROM games ORDER BY game_id DESC;
";

$result = mysqli_query($conn, $display_cousre_stream);

//Delete Code
if (isset($_GET['cs_id']) && is_numeric($_GET['cs_id'])) {
    $id = $_GET['cs_id'];

    $delete_course_stream = "delete from course_stream where cs_id='$id'";
    mysqli_query($conn, $delete_course_stream);

    // Very important: redirect with success flag
    header("Location: manage-course-stream.php?deleted=1");
    exit;
}
$i = 1;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Manage Games | <?php echo $company_name; ?></title>
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
    <!-- Sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- Datatables css -->
    <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/fixedColumns.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awseome cdn -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
                                <h4 class="header-title mb-2">Manage Games</h4>
                                <p class="text-muted mb-0">
                                    Manage List Of Games
                                </p>
                            </div>
                            <div class="card-body">                                  
                                  <div class="d-flex justify-content-start mb-0 ms-0">
                                        <a href="add-game.php" class="btn btn-primary">
                                            Add Games
                                        </a>
                                    </div>
                                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Sr no.</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Game Id</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Game Name</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Slug</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Icon</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Category</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Description</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Difficulty</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Base Duration Minutes</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Created at</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Status</th>
                                            <th style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php while ($row = mysqli_fetch_array($result)) { ?>
                                            <tr>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $i++ ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $row['game_id'] ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $row['name'] ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $row['slug'] ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $row['icon'] ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $row['category'] ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $row['description'] ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $row['difficulty'] ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo $row['base_duration_minutes'] ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;"><?php echo date('d-m-Y', strtotime($row['created_at'])) ?></td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input toggle-switch" game_id="switch_<?php echo $row['is_active']; ?>"
                                                            data-id="<?php echo $row['game_id']; ?>"
                                                            <?php echo ($row['is_active'] == 1) ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>
                                                <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                    <a href="#" class="deletegame" data-id="<?= $row['game_id']; ?>">
                                                        <i class="fa-solid fa-trash text-danger"></i>
                                                    </a>

                                                    &nbsp;|&nbsp;
                                                    <a href="Add-game.php?game_id=<?php echo $row['game_id'] ?>">
                                                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
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


    <script>
        $(document).ready(function() {
            // ✅ Use delegated event listener for dynamically generated rows
            $(document).on('change', '.toggle-switch', function() {
                var game_id = $(this).data('id');
                var game_status = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: 'update_status.php',
                    type: 'POST',
                    data: {
                        game_id: game_id,
                        game_status: game_status
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Status Updated!',
                            text: 'Game status changed successfully.',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong while updating status.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        console.log('AJAX Error:', error, xhr.responseText);
                    }
                });
            });
        });
    </script>

    <!-- sweetalert for Delete -->
    <script>
        // DELETE GAME
        document.querySelectorAll(".deleteGame").forEach(btn => {
            btn.addEventListener("click", function(e) {
                e.preventDefault();

                let id = this.getAttribute("data-id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you really want to delete this game?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Delete",
                    cancelButtonText: "Cancel"
                }).then((result) => {

                    if (result.isConfirmed) {

                        // redirect to PHP delete code
                        window.location.href = "manage-games.php?game_id=" + id;
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
                text: "Game deleted successfully.",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php } ?>


</body>

</html>