<?php
require_once('../includes/init.php');
include '../../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_id'])) {
    $video_id = intval($_POST['video_id']);
    $tutor_id = $_SESSION['tutor_id'];

    // Get file path before deleting
    $res = mysqli_query($conn, "SELECT video_url FROM videos_tbl WHERE video_id = $video_id AND tutor_id = $tutor_id");
    if ($row = mysqli_fetch_assoc($res)) {
        $filePath = __DIR__ . '/../../' . $row['video_url'];
        
        $del = mysqli_query($conn, "DELETE FROM videos_tbl WHERE video_id = $video_id AND tutor_id = $tutor_id");
        if ($del) {
            if (file_exists($filePath)) @unlink($filePath);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Video not found or access denied.']);
    }
}
?>
