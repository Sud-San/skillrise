<?php  
session_set_cookie_params(["path" => "/"]);
session_start();
require 'connection.php';

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);


// ================= ADMIN LOGIN =================
$admin_sql = "SELECT * FROM admin_tbl WHERE admin_email='$email' LIMIT 1";
$admin_result = mysqli_query($conn, $admin_sql);

if (mysqli_num_rows($admin_result) > 0) {
    $admin_row = mysqli_fetch_assoc($admin_result);

    if ($password === $admin_row['admin_password']) {
        $_SESSION['user_id']   = $admin_row['admin_id'];
        $_SESSION['user_name'] = $admin_row['admin_name'];
        $_SESSION['user_email']= $admin_row['admin_email'];
        $_SESSION['user_role'] = 'admin';

        echo "admin";
        exit();
    } else {
        echo "Incorrect password.";
        exit();
    }
}


// ================= TUTOR LOGIN =================
$tutor_sql = "SELECT * FROM tutor_tbl WHERE tutor_email='$email' LIMIT 1";
$tutor_result = mysqli_query($conn, $tutor_sql);

if (mysqli_num_rows($tutor_result) > 0) {
    $tutor_row = mysqli_fetch_assoc($tutor_result);

    // If hashed passwords exist, use password_verify()
    if ($password === $tutor_row['password']) {

        if ($tutor_row['verification_status'] !== 'approved') {
            echo "Your tutor account is pending approval.";
            exit();
        }

        if ($tutor_row['tutor_status'] != 1) {
            echo "Your tutor account is inactive.";
            exit();
        }

        $_SESSION['user_id']   = $tutor_row['tutor_id'];
        $_SESSION['user_name'] = $tutor_row['tutor_name'];
        $_SESSION['user_email']= $tutor_row['tutor_email'];
        $_SESSION['user_role'] = 'tutor';

        echo "tutor";
        exit();
    } else {
        echo "Incorrect password.";
        exit();
    }
}


// ================= STUDENT LOGIN =================
$student_sql = "SELECT * FROM user_tbl WHERE user_email='$email' LIMIT 1";
$student_result = mysqli_query($conn, $student_sql);

if (mysqli_num_rows($student_result) > 0) {
    $student_row = mysqli_fetch_assoc($student_result);

    if ($password === $student_row['user_password']) {

        if ($student_row['user_status'] != 1) {
            echo "Your account is inactive.";
            exit();
        }

        $_SESSION['user_id']   = $student_row['user_id'];
        $_SESSION['user_name'] = $student_row['user_name'];
        $_SESSION['user_email']= $student_row['user_email'];
        $_SESSION['user_role'] = 'student';

        echo "student";
        exit();
    } else {
        echo "Incorrect password.";
        exit();
    }
}


// ================= NO USER FOUND =================
echo "Email not registered.";
exit();
?>
