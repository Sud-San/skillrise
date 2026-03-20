<?php
require_once('../includes/init.php');
include '../../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $video_id = intval($_POST['video_id']);
    $course_id = intval($_POST['course_id']);
    $lesson_id = intval($_POST['lesson_id']);
    $tutor_id = $_SESSION['tutor_id'];

    if ($video_id <= 0 || $course_id <= 0 || $lesson_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        exit;
    }

    // Handle File Upload if provided
    $video_url = null;
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../videos/';
        $fileName = time() . '_' . basename($_FILES['video_file']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetPath)) {
            $video_url = 'videos/' . $fileName;
            
            // Delete old file if possible
            $oldRes = mysqli_query($conn, "SELECT video_url FROM videos_tbl WHERE video_id = $video_id AND tutor_id = $tutor_id");
            if ($oldRow = mysqli_fetch_assoc($oldRes)) {
                $oldFile = __DIR__ . '/../../' . $oldRow['video_url'];
                if (file_exists($oldFile)) @unlink($oldFile);
            }
        }
    }

    if ($video_url) {
        $stmt = $conn->prepare("UPDATE videos_tbl SET course_id = ?, lesson_id = ?, video_url = ? WHERE video_id = ? AND tutor_id = ?");
        $stmt->bind_param("iisii", $course_id, $lesson_id, $video_url, $video_id, $tutor_id);
    } else {
        $stmt = $conn->prepare("UPDATE videos_tbl SET course_id = ?, lesson_id = ? WHERE video_id = ? AND tutor_id = ?");
        $stmt->bind_param("iiii", $course_id, $lesson_id, $video_id, $tutor_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}
?>
