<?php
require_once('includes/init.php');
include 'connection.php';

// ============== HANDLE STATUS TOGGLE (CHECK THIS FIRST!) ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {

    $videoId = (int) $_POST['video_id'];
    $newStatus = (int) $_POST['status']; // 1 or 0

    $query = "UPDATE videos_tbl SET video_status = $newStatus WHERE video_id = $videoId";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        error_log("Status toggle failed for video $videoId: " . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
    exit; // Stop execution after handling status toggle
}


?>