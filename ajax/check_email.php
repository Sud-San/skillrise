<?php
header('Content-Type: application/json');
require_once('../connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    $safe_email = mysqli_real_escape_string($conn, $email);
    $query = "SELECT user_id FROM user_tbl WHERE user_email = '$safe_email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo json_encode(['success' => true, 'exists' => true, 'message' => 'Email already registered']);
        } else {
            echo json_encode(['success' => true, 'exists' => false, 'message' => 'Email available']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
