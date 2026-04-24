<?php
include_once("connection.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $key = mysqli_real_escape_string($conn, $key);
        $value = mysqli_real_escape_string($conn, $value);

        $update_q = "UPDATE settings_tbl SET setting_value = '$value' WHERE setting_key = '$key'";
        mysqli_query($conn, $update_q);
    }
    $success_msg = "Settings updated successfully!";
}

// Fetch all settings
$settings_q = mysqli_query($conn, "SELECT * FROM settings_tbl");
$settings = [];
while ($row = mysqli_fetch_assoc($settings_q)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>System Settings |
        <?php echo $company_name; ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SkillRise_logo1.png">
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
                            <h4 class="page-title">System Settings</h4>
                        </div>
                    </div>
                </div>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success mt-2">
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post" class="mt-3">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header border-bottom border-dashed">
                                    <h4 class="header-title">General Settings</h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Website Name</label>
                                        <input type="text" name="settings[website_name]" class="form-control"
                                            value="<?php echo $settings['website_name'] ?? ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Currency Symbol</label>
                                        <input type="text" name="settings[currency_symbol]" class="form-control"
                                            value="<?php echo $settings['currency_symbol'] ?? '₹'; ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header border-bottom border-dashed">
                                    <h4 class="header-title">Payment & API Settings</h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Razorpay API Key</label>
                                        <input type="text" name="settings[key_id]" class="form-control"
                                            value="<?php echo $settings['key_id'] ?? ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Razorpay Key Secret</label>
                                        <input type="text" name="settings[key_secret]" class="form-control"
                                            value="<?php echo $settings['key_secret'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="submit" name="update_settings" class="btn btn-primary px-4">Save All
                                Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php include_once("footer.php"); ?>
    </div>

    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>