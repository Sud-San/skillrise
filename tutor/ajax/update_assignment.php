<?php
require_once('../includes/init.php');
include '../connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = (int)($_POST['assignment_id'] ?? 0);
    $course_id = (int)($_POST['course_id'] ?? 0);
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $tutor_id = $_SESSION['tutor_id'];

    if (!$assignment_id || !$course_id || empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
        exit;
    }

    // Security check: ensure assignment belongs to the tutor
    $check = mysqli_query($conn, "SELECT file_url FROM assignment_tbl WHERE assignment_id = $assignment_id AND tutor_id = $tutor_id");
    if (mysqli_num_rows($check) === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit;
    }
    $current_assignment = mysqli_fetch_assoc($check);
    $file_url = $current_assignment['file_url'];

    // Handle file upload if provided
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/assignments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($_FILES['assignment_file']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_file)) {
            // Delete old file if exists
            if (!empty($file_url) && file_exists($upload_dir . $file_url)) {
                @unlink($upload_dir . $file_url);
            }
            $file_url = $file_name;
        } else {
            echo json_encode(['success' => false, 'message' => 'File upload failed.']);
            exit;
        }
    }

    $update = "UPDATE assignment_tbl SET 
        course_id = $course_id,
        title = '$title',
        description = '$description',
        file_url = '$file_url'
        WHERE assignment_id = $assignment_id AND tutor_id = $tutor_id";

    if (mysqli_query($conn, $update)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
