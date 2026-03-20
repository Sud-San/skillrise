<?php
include("connection.php");
if (isset($_GET['del_id'])) {

    $delete_id = $_GET['del_id'];

    $delete_query = "DELETE FROM college_course WHERE college_course_id = '$delete_id'";
    $delete_result = mysqli_query($conn, $delete_query);

    if ($delete_result) {
        echo "<script>
                window.location.href = 'all_package.php?deleted=1';
              </script>";
        exit;
    } else {
        echo "<script>
                window.location.href = 'all_package.php?deleted=0';
              </script>";
        exit;
    }
}



// Display Code
$str = "SELECT * FROM package_tbl ORDER BY package_id DESC";
$result = mysqli_query($conn, $str);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Manage Packages | <?php echo $company_name; ?></title>
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
                                <h4 class="header-title mb-2">Manage Packages</h4>
                                <p class="text-muted mb-0">
                                    Manage List Of Packages.
                                </p>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-start mb-0 ms-0">
                                        <a href="package.php" class="btn btn-primary">
                                            Add Package
                                        </a>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="datatable-buttons" class="table table-bordered table-striped display nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px; text-align: justify; vertical-align:middle; white-space: wrap;">Sr No</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Package ID</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Package Name</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Price</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Valid Months</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Maximum Upload Courses</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Maximum Upload Videos</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Upload Courses</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Upload Videos</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Upload Quiz</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Upload Assignments</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Upload Games</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Status</th>
                                                    <th style="width: 100px; text-align: justify; vertical-align:middle; white-space: wrap;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sr = 1;
                                                while ($package = mysqli_fetch_assoc($result)) {
                                                    $packageJson = htmlspecialchars(
                                                        json_encode($package, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    );
                                                ?>
                                                    <tr>
                                                        <td><?php echo $sr++; ?></td>
                                                        <td><?php echo $package['package_id'] ?></td>
                                                        <td class="viewPackage text-primary" id="viewPackage" style="cursor: pointer;" data-package="<?= $packageJson ?>"><?php echo $package['package_name'] ?></td>
                                                        <td><?php echo $package['price'] ?></td>
                                                        <td><?php echo $package['valid_months'] ?></td>
                                                        <td><?php echo $package['max_course'] ?></td>
                                                        <td><?php echo $package['max_video_upload'] ?></td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input disabled type="checkbox" data-field="can_add_courses" class="form-check-input toggle-switch" id="switch_<?php echo $package['package_id']; ?>"
                                                                    data-id="<?php echo $package['package_id']; ?>"
                                                                    <?php echo ($package['can_add_courses'] == 1) ? 'checked' : ''; ?>>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input disabled type="checkbox" data-field="can_add_videos" class="form-check-input toggle-switch" id="switch_<?php echo $package['package_id']; ?>"
                                                                    data-id="<?php echo $package['package_id']; ?>"
                                                                    <?php echo ($package['can_add_videos'] == 1) ? 'checked' : ''; ?>>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input <?php if($package['package_name']=='Booster'){ echo 'disabled'; }?> type="checkbox" data-field="can_add_quiz" class="form-check-input toggle-switch" id="switch_<?php echo $package['package_id']; ?>"
                                                                    data-id="<?php echo $package['package_id']; ?>"
                                                                    <?php echo ($package['can_add_quiz'] == 1) ? 'checked' : ''; ?>>
                                                            </div>
                                                        </td>
                                                        
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input <?php if($package['package_name']=='Booster'){ echo 'disabled'; }?> type="checkbox" data-field="can_add_assignments" class="form-check-input toggle-switch" id="switch_<?php echo $package['package_id']; ?>"
                                                                    data-id="<?php echo $package['package_id']; ?>"
                                                                    <?php echo ($package['can_add_assignments'] == 1) ? 'checked' : ''; ?>>
                                                            </div>
                                                        </td>
                                                        
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input <?php if($package['package_name']=='Booster'){ echo 'disabled'; }?> type="checkbox" data-field="can_add_games" class="form-check-input toggle-switch" id="switch_<?php echo $package['package_id']; ?>"
                                                                    data-id="<?php echo $package['package_id']; ?>"
                                                                    <?php echo ($package['can_add_games'] == 1) ? 'checked' : ''; ?>>
                                                            </div>
                                                        </td>
                                                        
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input <?php if($package['package_name']=='Booster'){ echo 'disabled'; }?> type="checkbox" data-field="package_status" class="form-check-input toggle-switch" id="switch_<?php echo $package['package_id']; ?>"
                                                                    data-id="<?php echo $package['package_id']; ?>"
                                                                    <?php echo ($package['package_status'] == 1) ? 'checked' : ''; ?>>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            
                                                            <a href="#" class="deletePackage" data-id="<?php echo $package['package_id']; ?>">
                                                                <i class="fa-solid fa-trash text-danger"></i>
                                                            </a>&nbsp;|&nbsp;
                                                            <a href="package.php?update_id=<?php echo $package['package_id']; ?>">
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


    <!-- TOGGLE BUUTTON CODE -->
    <script>
        $(document).ready(function() {

            // ✅ Use delegated event listener for dynamically generated rows
            $(document).on('change', '.toggle-switch', function() {
                var package_id = $(this).data('id');
                var field = $(this).data('field');
                var value = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: 'update_status.php',
                    type: 'POST',
                    data: {
                        package_id: package_id,
                        field: field,
                        value: value
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status == 'success'){
                            Swal.fire({
                                    title: 'Status Updated!',
                                    text: 'Package ' + field + ' changed successfully.',
                                    icon: 'success',
                                    timer: 1000,
                                    showConfirmButton: false
                                });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Something went wrong while updating ' + field + ' status.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
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

            // View package details
            $(document).on('click', '.viewPackage', function(e) {
                e.preventDefault();

                var pkg;
                try {
                    pkg = JSON.parse($(this).attr('data-package'));
                } catch (err) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Unable to load package details.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                function escapeHtml(value) {
                    return String(value ?? '').replace(/[&<>"']/g, function(ch) {
                        return ({
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#39;'
                        })[ch];
                    });
                }

                function boolIcon(key, value) {
                    var v = String(value);
                    var isStatus = key === 'package_status';

                    if (v === '1') {
                        return '<span style="color:#16a34a; font-weight:700;"><i class="fa-solid fa-circle-check"></i> ' + (isStatus ? 'Active' : 'Yes') + '</span>';
                    }
                    if (v === '0') {
                        return '<span style="color:#dc2626; font-weight:700;"><i class="fa-solid fa-circle-xmark"></i> ' + (isStatus ? 'Inactive' : 'No') + '</span>';
                    }
                    return escapeHtml(value);
                }

                var booleanKeys = {
                    can_add_courses: true,
                    can_add_videos: true,
                    can_add_quiz: true,
                    can_add_assignments: true,
                    can_add_games: true,
                    package_status: true
                };

                var preferredOrder = [
                    'package_id',
                    'package_name',
                    'price',
                    'valid_months',
                    'max_course',
                    'max_video_upload',
                    'can_add_courses',
                    'can_add_videos',
                    'can_add_quiz',
                    'can_add_assignments',
                    'can_add_games',
                    'package_status'
                ];

                var labelMap = {
                    package_id: 'Package ID',
                    package_name: 'Package Name',
                    price: 'Price',
                    valid_months: 'Valid Months',
                    max_course: 'Maximum Upload Courses',
                    max_video_upload: 'Maximum Upload Videos',
                    can_add_courses: 'Upload Courses',
                    can_add_videos: 'Upload Videos',
                    can_add_quiz: 'Upload Quiz',
                    can_add_assignments: 'Upload Assignments',
                    can_add_games: 'Upload Games',
                    package_status: 'Status'
                };

                var rowsHtml = '';

                preferredOrder.forEach(function(key) {
                    if (pkg[key] === undefined) return;
                    var val = booleanKeys[key] ? boolIcon(key, pkg[key]) : escapeHtml(pkg[key]);
                    rowsHtml += '<tr>' +
                        '<td style="padding:8px 10px; color:#6c757d; width:200px;">' + escapeHtml(labelMap[key] || key.replace(/_/g, ' ')) + '</td>' +
                        '<td style="padding:8px 10px; font-weight:600;">' + val + '</td>' +
                        '</tr>';
                });

                Object.keys(pkg).forEach(function(key) {
                    if (preferredOrder.indexOf(key) !== -1) return;
                    if (key.toLowerCase().includes('password')) return;
                    var val = (String(pkg[key]) === '1' || String(pkg[key]) === '0') ? boolIcon(key, pkg[key]) : escapeHtml(pkg[key]);
                    rowsHtml += '<tr>' +
                        '<td style="padding:8px 10px; color:#6c757d; width:200px;">' + escapeHtml(labelMap[key] || key.replace(/_/g, ' ')) + '</td>' +
                        '<td style="padding:8px 10px; font-weight:600;">' + val + '</td>' +
                        '</tr>';
                });

                Swal.fire({
                    title: pkg.package_name ? pkg.package_name : 'Package Details',
                    html: '<div style="text-align:left;">' +
                        '<table style="width:100%; border-collapse:collapse;">' + rowsHtml + '</table>' +
                        '</div>',
                    width: 520,
                    showCloseButton: true,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#3085d6'
                });
            });

        });
    </script>

    <!-- DELETE CONFIRMATION -->
    <script>
        document.querySelectorAll(".deleteCourse").forEach(btn => {
            btn.addEventListener("click", function(e) {
                e.preventDefault();

                let id = this.getAttribute("data-id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you really want to delete this package?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Delete",
                    cancelButtonText: "Cancel"
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = "all_package.php?del_id=" + id;
                    }
                });
            });
        });
    </script>

    <!-- SHOW DELETE SUCCESS POPUP -->
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1) { ?>
        <script>
            Swal.fire({
                icon: "success",
                title: "Deleted!",
                text: "College course deleted successfully.",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php } ?>


</body>

</html>