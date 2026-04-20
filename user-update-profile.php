<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    exit("Session expired. Please login again.");
}

$user_id = (int) $_SESSION['user_id'];

/* --------------------------
   Get form data
-------------------------- */
$user_name = trim($_POST['user_name'] ?? '');
$user_email = trim($_POST['user_email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');

if ($user_name == '' || $user_email == '') {
    exit("Name and Email are required.");
}

/* --------------------------
   Update user_tbl (name/email)
-------------------------- */


/* --------------------------
   Profile Picture Upload
-------------------------- */
$profile_pic_path = null;

if (!empty($_FILES['profile_pic']['name'])) {

    $uploadDir = "admin/assets/images/users/";

    $fileTmp = $_FILES['profile_pic']['tmp_name'];
    $fileName = basename($_FILES['profile_pic']['name']);
    $target = $uploadDir . $fileName;

    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        exit("Only JPG, PNG or WEBP allowed.");
    }

    if (move_uploaded_file($fileTmp, $target)) {
        $profile_pic_path = $fileName;
        $_SESSION['user_profile_pic'] = $target;
    }
}

/* --------------------------
   Check if details row exists
-------------------------- */
$stmt = $conn->prepare("UPDATE user_tbl SET user_name=?, user_email=?, mobile=?, profile_pic=? WHERE user_id=?");
if (!$stmt)
    exit("DB Error: " . $conn->error);

$stmt->bind_param("ssisi", $user_name, $user_email, $mobile, $profile_pic_path, $user_id);
$stmt->execute();


if (!$stmt->execute()) {
    exit("Database Error: " . $stmt->error);
}

$stmt->close();
json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
header("Location: user-setting.php");
exit;
?>