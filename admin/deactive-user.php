<?php
include("connection.php");

// ======================= AJAX HANDLER ===========================
// Handle Delete FAQ
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $user_id = intval($_POST['user_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM user_tbl WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
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
    $user_id = intval($_POST['user_id']);
    $user_status = intval($_POST['user_status']);

    $stmt = mysqli_prepare($conn, "UPDATE user_tbl SET user_status = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user_status, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }

    mysqli_stmt_close($stmt);
    exit;
}


// ======================= DISPLAY QUERY ===========================
$display_query = "SELECT user_tbl.user_id, user_tbl.user_name, user_tbl.user_email, user_tbl.mobile as user_phone, user_tbl.user_status, user_tbl.created_at as user_created_at, user_tbl.profile_pic as user_profile_pic, user_tbl.gender, city_tbl.city_name
    FROM user_tbl
    LEFT JOIN city_tbl ON city_tbl.city_id = user_tbl.city
    where user_tbl.user_status = 0
    ORDER BY user_id DESC";
$result = mysqli_query($conn, $display_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Deactive Users | <?php echo $company_name; ?></title>
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
                                <h4 class="header-title mb-2">Manage Deactive Users</h4>
                                <p class="text-muted mb-0">
                                    Manage List of Deactived Users
                                </p>
                            </div>
                            <div class="card-body">
                                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100" style="overflow-x:scroll; white-space: nowrap; ">
                                    <thead>
                                        <tr>
                                            <th>Sr No</th>
                                            <th>User ID.</th>
                                            <th>User Image</th>
                                            <th>User Name</th>
                                            <th>User Email</th>
                                            <th>Mobile Number</th>
                                            <th>City</th>
                                            <th>Gender</th>
                                            <!-- <th>About ME</th>
                                        <th>Skills</th>
                                        <th>Languages Known</th> -->
                                            <th>Join Date</th>
                                            <th>User Status</th>
                                            <!-- <th>Action</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        while ($user = mysqli_fetch_array($result)) {
                                            $userJson = htmlspecialchars(
                                                json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                        ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= $user['user_id']; ?></td>
                                                <td data-user="<?= $userJson ?>" class="view-user"><img src="assets/images/users/<?php echo $user['user_profile_pic']; ?>" class="logo" alt="User Profile"></td>
                                                <td data-user="<?= $userJson ?>" style="max-width: 150px; white-space: wrap; overflow: hidden; text-overflow: ellipsis; cursor:pointer" class="view-user text-primary"><?= htmlspecialchars($user['user_name']); ?></td>
                                                <td style="max-width: 100px; white-space: wrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($user['user_email']); ?></td>
                                                <td style="max-width: 100px; white-space: wrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($user['user_phone']); ?></td>
                                                <td style="max-width: 100px; white-space: wrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($user['city_name']); ?></td>
                                                <td style="max-width: 100px; white-space: wrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($user['gender']); ?></td>
                                                <td style="max-width: 100px; white-space: wrap; overflow: hidden; text-overflow: ellipsis;"><?php echo date_format(new DateTime($user['user_created_at']), 'd-m-Y'); ?></td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox"
                                                            class="form-check-input toggle-switch"
                                                            data-id="<?= $user['user_id']; ?>"
                                                            <?= ($user['user_status'] == 1) ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>
                                                <!-- <td>
                                           <a href="" class="delete-btn" data-id="<?= $user['user_id']; ?>">
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
        $(document).ready(function() {

            // Toggle User status
            $(document).on('change', '.toggle-switch', function() {
                var user_id = $(this).data('id');
                var user_status = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        action: 'toggle_status',
                        user_id: user_id,
                        user_status: user_status
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Status Updated!',
                                icon: 'success',
                                text: "User Status updated Successfully.",
                                timer: 1000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "deactive-user.php";
                            });
                        }
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

            // Delete FAQ with SweetAlert
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                var user_id = $(this).data('id');
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
                                user_id: user_id
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'User deleted successfully.',
                                        timer: 1200,
                                        showConfirmButton: false
                                    });
                                    row.fadeOut(800, function() {
                                        $(this).remove();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: 'Failed to delete User.',
                                    });
                                }
                            },
                            error: function() {
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