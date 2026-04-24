<?php
include("connection.php");

// ======================= AJAX HANDLER ===========================
// Handle Delete FAQ
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $tutor_id = intval($_POST['tutor_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM tutor_tbl WHERE tutor_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $tutor_id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// Handle Toggle Status
// Handle Toggle Status
if (isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
    $tutor_id = intval($_POST['tutor_id']);
    $tutor_status = intval($_POST['tutor_status']);

    $stmt = mysqli_prepare($conn, "UPDATE tutor_tbl SET tutor_status = ? WHERE tutor_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $tutor_status, $tutor_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }

    mysqli_stmt_close($stmt);
    exit;
}


// ======================= DISPLAY QUERY ===========================
$display_query = "
    SELECT 
        t.tutor_id,
        t.tutor_name,
        t.tutor_email,
        t.tutor_phone,
        t.tutor_status,
        t.created_at,
        p.*
    FROM tutor_tbl t
    LEFT JOIN tutor_profile_tbl p ON t.tutor_id = p.tutor_id
    where t.tutor_status = 0
    ORDER BY t.tutor_id DESC

";
$result = mysqli_query($conn, $display_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Deactive Tutors | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SkillRise_logo1.png">
    <script src="assets/js/config.js"></script>
    <link href="assets/css/vendor.min.css" rel="stylesheet" />
    <link href="assets/css/app.min.css" rel="stylesheet" id="app-style" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="assets/css/icons.min.css" rel="stylesheet" />
    <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        img.logo {
            width: 100px;
            border-image-repeat: none;
            height: 100px;
            padding: initial;
            border-radius: 5px;
            object-fit: cover;
            border: 1px solid #ddd;
        }
    </style>
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
                                <h4 class="header-title mb-2">Manage Deactive Tutors</h4>
                                <p class="text-muted mb-0">
                                    Manage List of Deactived Tutors
                                </p>
                            </div>
                            <div class="card-body">
                                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100"
                                    style="overflow-x:scroll; white-space: nowrap; ">
                                    <thead>
                                        <tr>
                                            <th>Sr No.</th>
                                            <th>Tutor ID</th>
                                            <th>Tutor Image</th>
                                            <th>Tutor Name</th>
                                            <th>Tutor Email</th>
                                            <th>Mobile Number</th>
                                            <th>Country</th>
                                            <th>View Profile</th>
                                            <th>Join Date</th>
                                            <!-- <th>Status</th>
                                            <th>Action</th> -->
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $no = 1;
                                        while ($tutor = mysqli_fetch_assoc($result)) {
                                            $tutorJson = htmlspecialchars(
                                                json_encode($tutor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($tutor['tutor_id']); ?></td>
                                                <td><img src="assets/images/tutors/<?php echo $tutor['profile_pic']; ?>"
                                                        style="cursor: pointer;" class="logo view-tutor" alt="Tutor Profile"
                                                        data-tutor="<?= $tutorJson ?>"></td>
                                                <td><?php echo htmlspecialchars($tutor['tutor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($tutor['tutor_email']); ?></td>
                                                <td><?php echo htmlspecialchars($tutor['tutor_phone']); ?></td>
                                                <td><?php echo htmlspecialchars($tutor['country'] ?? ''); ?></td>
                                                <td style="text-align: center;">
                                                    <a href="#" class="view-tutor" data-tutor="<?= $tutorJson ?>">
                                                        <i class="fa-solid fa-eye fa-xl"></i>
                                                    </a>
                                                </td>
                                                <td><?php echo date('d-m-Y', strtotime($tutor['created_at'])); ?></td>
                                                <!-- <td>
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox"
                                                            class="form-check-input toggle-switch"
                                                            data-id="<?= $tutor['tutor_id']; ?>"
                                                            <?= ($tutor['tutor_status'] == 1) ? 'checked' : ''; ?>>
                                                    </div>
                                                </td> -->
                                                <!-- <td>
                                           <a href="" class="delete-btn" data-id="<?= $tutor['tutor_id']; ?>">
                                                <i class="fa-solid fa-trash text-danger"></i>
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
        $(document).ready(function () {

            // Toggle tutor status
            $(document).on('change', '.toggle-switch', function () {
                var tutor_id = $(this).data('id');
                var tutor_status = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        action: 'toggle_status',
                        tutor_id: tutor_id,
                        tutor_status: tutor_status
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Status Updated!',
                                icon: 'success',
                                text: "tutor Status updated Successfully.",
                                timer: 1000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "deactive-tutor.php";
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong while updating status.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Delete FAQ with SweetAlert
            $(document).on('click', '.delete-btn', function (e) {
                e.preventDefault();
                var tutor_id = $(this).data('id');
                var row = $(this).closest('tr');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '',
                            type: 'POST',
                            data: {
                                action: 'delete',
                                tutor_id: tutor_id
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Tutor deleted successfully.',
                                        timer: 1200,
                                        showConfirmButton: false
                                    });
                                    row.fadeOut(800, function () {
                                        $(this).remove();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: 'Failed to delete tutor.',
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Something went wrong.',
                                });
                            }
                        });
                    }
                });
            });

        });
    </script>
    <script src="assets/js/view-modals.js"></script>

</body>

</html>