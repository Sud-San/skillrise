<?php
require_once('includes/init.php');
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {

    $lessonId = (int) $_POST['lesson_id'];
    $newStatus = (int) $_POST['status']; // 1 or 0

    $query = "UPDATE lessons_tbl SET status = $newStatus WHERE lesson_id = $lessonId";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        error_log("Status toggle failed for lesson $lessonId: " . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
    exit;
}
?>
