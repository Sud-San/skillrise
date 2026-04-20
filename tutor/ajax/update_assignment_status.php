<?php
require_once('../includes/init.php');
include '../connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = (int) ($_POST['id'] ?? 0);
    $status = (int) ($_POST['status'] ?? 0);
    $tutor_id = $_SESSION['tutor_id'];

    if (!$assignment_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid assignment ID.']);
        exit;
    }

    // Security check: ensure assignment belongs to the tutor
    $update = "UPDATE assignment_tbl SET status = $status WHERE assignment_id = $assignment_id AND course_id IN (SELECT course_id FROM course_tbl WHERE tutor_id = $tutor_id)";

    if (mysqli_query($conn, $update)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>