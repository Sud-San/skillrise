<?php
require_once('../includes/init.php');
include '../connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $tutor_id = $_SESSION['tutor_id'];

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid assignment ID.']);
        exit;
    }

    // Security check: ensure assignment belongs to the tutor
    $check = mysqli_query($conn, "SELECT file_url FROM assignment_tbl WHERE assignment_id = $id AND tutor_id = $tutor_id");
    if (mysqli_num_rows($check) === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit;
    }
    
    $row = mysqli_fetch_assoc($check);
    $file_url = $row['file_url'];

    // Delete record from database
    $delete = "DELETE FROM assignment_tbl WHERE assignment_id = $id AND tutor_id = $tutor_id";
    if (mysqli_query($conn, $delete)) {
        // Delete physical file if exists
        if (!empty($file_url)) {
            $file_path = '../assets/assignments/' . $file_url;
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
