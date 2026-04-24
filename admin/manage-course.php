<?php include("connection.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <meta charset="utf-8" />
    <title>Manage Course | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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

    <!-- Datatables css -->
    <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/fixedColumns.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css" />

    <!-- Optional duplicate Font Awesome include removed for cleanliness -->

    <style>
        /* smaller toggle (kept from your previous change) */
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
            background-color: #ccc;
            border-radius: 20px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            transition: .3s;
        }

        .slider:before {
            position: absolute;
            content: "";
            width: 12px;
            height: 12px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            border-radius: 50%;
            transition: .3s;
        }

        input:checked+.slider {
            background-color: #0d6efd;
            /* SAME BLUE AS DELETE ICON */
        }

        input:checked+.slider:before {
            transform: translateX(16px);
        }

        .slider.round {
            border-radius: 20px;
        }


        /* small spacing for icons in actions cell */
        td .fa-trash,
        td .fa-pen-to-square {
            color: #0d6efd;
            cursor: pointer;
        }

        td .fa-trash:hover {
            color: #d33;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <!-- Sidenav -->
        <?php include_once("sidebar.php"); ?>

        <!-- Topbar -->
        <?php include_once("header.php"); ?>
        <?php
        $i = 0;
        $display_query = "SELECT course_tbl.*, category_tbl.*, tutor_tbl.*, tutor_profile_tbl.* FROM course_tbl 
                INNER JOIN category_tbl ON course_tbl.category_id = category_tbl.category_id
                INNER JOIN tutor_tbl ON course_tbl.tutor_id = tutor_tbl.tutor_id
                INNER JOIN tutor_profile_tbl ON tutor_tbl.tutor_id = tutor_profile_tbl.tutor_id
                ORDER BY course_id DESC";
        $result = mysqli_query($conn, $display_query);
        ?>
        <?php
        // Delete code (unchanged) - server side deletion still uses GET param course-id
        if (isset($_GET['course-id'])) {
            $course_id = $_GET['course-id']; // variable named course_id
            $str = "DELETE FROM course_tbl WHERE course_id=" . intval($course_id) . "";
            mysqli_query($conn, $str);
            header('location:manage-course.php');
            exit;
        }
        ?>

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

        <!-- Page content -->
        <div class="page-content">
            <div class="page-container">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title mb-2">Manage Course</h4>
                                <p class="text-muted mb-0">
                                    Manage List Of Courses
                                </p>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-start mb-0 ms-0">
                                    <!-- <a href="Add-course.php" class="btn btn-primary">
                                            Add Course
                                        </a> -->
                                </div>
                                <div class="table-responsive">
                                    <table id="datatable-buttons"
                                        class="table table-bordered table-striped display nowrap" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Sr No</th>
                                                <th>Course Id</th>
                                                <th>Category</th>
                                                <th>Tutor</th>
                                                <th>Thumbnail</th>
                                                <th>Course Title</th>
                                                <th>Course Slug</th>
                                                <th>Description</th>

                                                <th>Course Level</th>
                                                <th>Price</th>
                                                <th>Total Lesson</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php while ($row = mysqli_fetch_array($result)) {
                                                $tutorJson = htmlspecialchars(
                                                    json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>
                                                <tr>
                                                    <td><?php echo ++$i; ?></td>
                                                    <td
                                                        style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        <?php echo htmlspecialchars($row['course_id']); ?>
                                                    </td>
                                                    <td
                                                        style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                                    </td>
                                                    <td style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap; cursor: pointer;"
                                                        class="view-tutor text-primary" data-tutor="<?= $tutorJson ?>">
                                                        <?php echo htmlspecialchars($row['tutor_name']); ?>
                                                    </td>
                                                    <td
                                                        style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        <img src="<?php echo "../assets/images/thumbnail/" . htmlspecialchars($row['course_thumbnail']); ?>"
                                                            alt="<?php echo htmlspecialchars($row['course_title']); ?>"
                                                            width="80px">
                                                    </td>
                                                    <td
                                                        style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        <?php echo htmlspecialchars($row['course_title']); ?>
                                                    </td>
                                                    <td
                                                        style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        <?php echo htmlspecialchars($row['course_slug']); ?>
                                                    </td>
                                                    <td
                                                        style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        <?php echo htmlspecialchars($row['course_description']); ?>
                                                    </td>
                                                    <td
                                                        style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        <?php echo htmlspecialchars($row['course_level']); ?>
                                                    </td>
                                                    <td
                                                        style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        ₹<?php echo htmlspecialchars($row['price']); ?></td>
                                                    <td
                                                        style="width: 100px; text-align: center; vertical-align:middle; white-space: wrap;">
                                                        <?php echo htmlspecialchars($row['total_lesson']); ?>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" class="form-check-input toggle-switch"
                                                                data-id="<?= $row['course_id']; ?>"
                                                                <?= ($row['course_status'] == 1) ? 'checked' : ''; ?>>
                                                        </div>
                                                    </td>

                                                    <!-- ========== REPLACED ACTIONS CELL ========== -->
                                                    <!-- <td> -->
                                                    <!-- Delete handled by SweetAlert (class + data-id). href="#" prevents immediate navigation. -->
                                                    <!-- <a href="#" class="delete-btn" data-id="<?php //echo $row['course_id']; ?>" title="Delete">
                                                        <i class="fa-solid fa-trash text-danger"></i>
                                                    </a>
                                                    &nbsp; | &nbsp;
                                                    <a href="Add-course.php?course-id=<?php //echo $row['course_id']; ?>" title="Edit">
                                                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                                                    </a>
                                                </td> -->
                                                    <!-- ============================================ -->
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div> <!-- end card body -->
                        </div> <!-- end card -->
                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- container -->
        </div> <!-- page-content -->

        <!-- Footer -->
        <?php include_once("footer.php"); ?>

    </div> <!-- wrapper -->
    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <!-- Apex Chart js -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- DataTables -->
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/view-modals.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Use event delegation so dynamically created rows (DataTables) still fire the handler
            document.body.addEventListener('click', function (e) {
                const el = e.target.closest('.delete-btn');
                if (!el) return;

                e.preventDefault();
                const courseId = el.getAttribute('data-id');
                if (!courseId) return;

                Swal.fire({
                    title: "Are you sure?",
                    text: "This course will be permanently deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel",
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Showing a small feedback toast (optional) before redirect
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Course Deleted Successffully.',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 800,
                            willClose: () => {
                                // After toast close, navigate to your delete endpoint (the PHP at top handles it)
                                window.location.href = "manage-course.php?course-id=" + encodeURIComponent(courseId);
                            }
                        });
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function () {

            // ✅ Use delegated event listener for dynamically generated rows
            $(document).on('change', '.toggle-switch', function () {
                var course_id = $(this).data('id');
                var course_status = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: 'update_status.php',
                    type: 'POST',
                    data: {
                        course_id: course_id,
                        course_status: course_status
                    },
                    success: function (response) {
                        Swal.fire({
                            title: 'Status Updated!',
                            text: 'Course status changed successfully.',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr, status, error) {
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
</body>

</html>