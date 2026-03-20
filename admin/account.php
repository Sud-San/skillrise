<?php
include_once("connection.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin'];
$success_msg = "";
$error_msg = "";

// Fetch current admin data
$admin_q = mysqli_query($conn, "SELECT * FROM admin_tbl WHERE admin_id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_q);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = mysqli_real_escape_string($conn, $_POST['admin_name']);
        $email = mysqli_real_escape_string($conn, $_POST['admin_email']);

        // Handle image upload
        $image_name = $admin_data['admin_image'];
        if (isset($_FILES['admin_image']) && $_FILES['admin_image']['error'] === 0) {
            $ext = pathinfo($_FILES['admin_image']['name'], PATHINFO_EXTENSION);
            $new_name = "admin_" . time() . "." . $ext;
            $upload_path = "assets/images/admin/" . $new_name;

            if (move_uploaded_file($_FILES['admin_image']['tmp_name'], $upload_path)) {
                $image_name = $new_name;
                $_SESSION['admin_image'] = $image_name;
            }
        }

        $update_q = "UPDATE admin_tbl SET admin_name = '$name', admin_email = '$email', admin_image = '$image_name' WHERE admin_id = '$admin_id'";
        if (mysqli_query($conn, $update_q)) {
            $_SESSION['admin_name'] = $name;
            $success_msg = "Profile updated successfully!";
            // Refresh data
            $admin_q = mysqli_query($conn, "SELECT * FROM admin_tbl WHERE admin_id = '$admin_id'");
            $admin_data = mysqli_fetch_assoc($admin_q);
        } else {
            $error_msg = "Failed to update profile.";
        }
    }

    if (isset($_POST['change_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($current_pass !== $admin_data['admin_password']) {
            $error_msg = "Current password is incorrect.";
        } elseif ($new_pass !== $confirm_pass) {
            $error_msg = "New passwords do not match.";
        } else {
            $update_pass_q = "UPDATE admin_tbl SET admin_password = '$new_pass' WHERE admin_id = '$admin_id'";
            if (mysqli_query($conn, $update_pass_q)) {
                $success_msg = "Password changed successfully!";
            } else {
                $error_msg = "Failed to change password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>My Account |
        <?php echo $company_name; ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <script src="assets/js/config.js"></script>
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
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
                        <div class="page-title-box">
                            <h4 class="page-title">My Account</h4>
                        </div>
                    </div>
                </div>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success mt-2">
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="alert alert-danger mt-2">
                        <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="row mt-3">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title">Profile Information</h4>
                            </div>
                            <div class="card-body">
                                <form action="" method="post" enctype="multipart/form-data">
                                    <div class="mb-3 text-center">
                                        <img src="assets/images/admin/<?php echo $admin_data['admin_image']; ?>"
                                            alt="user-image" class="rounded-circle avatar-xl img-thumbnail mb-2">
                                        <div>
                                            <input type="file" name="admin_image"
                                                class="form-control d-inline-block w-auto">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="admin_name" class="form-control"
                                            value="<?php echo $admin_data['admin_name']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="admin_email" class="form-control"
                                            value="<?php echo $admin_data['admin_email']; ?>" required>
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-primary">Update
                                        Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title">Change Password</h4>
                            </div>
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-danger">Change
                                        Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once("footer.php"); ?>
    </div>

    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>