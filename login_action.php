<?php
session_set_cookie_params(["path" => "/"]);
session_start();
include 'connection.php';

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

// ================= STUDENT LOGIN =================
$student_sql = "SELECT * FROM user_tbl WHERE user_email='$email' LIMIT 1";
$student_result = mysqli_query($conn, $student_sql);

if (mysqli_num_rows($student_result) > 0) {
    $student_row = mysqli_fetch_assoc($student_result);

    if (password_verify($password, $student_row['user_password'])) {

        if ($student_row['user_status'] != 1) {
            echo "Your account is inactive.";
            exit();
        }

        $_SESSION['user_id'] = $student_row['user_id'];
        $_SESSION['user_name'] = $student_row['user_name'];
        $_SESSION['user_email'] = $student_row['user_email'];
        $_SESSION['user_role'] = 'student';
        $_SESSION['user_profile_pic'] = $user_profile_path . ($student_row['profile_pic'] ?? 'assets/images/default.png');
        $_SESSION['game_preloader'] = 0;


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