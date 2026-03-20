<?php
include '../connection.php';
$course_id = 178;
$videosStmt = $conn->prepare("
    SELECT l.*, v.video_id, v.video_url, v.video_status, v.uploaded_at 
    FROM lessons_tbl l
    LEFT JOIN videos_tbl v ON l.lesson_id = v.lesson_id 
    WHERE l.course_id = ? 
    ORDER BY l.lesson_order ASC
");
$videosStmt->bind_param("i", $course_id);
$videosStmt->execute();
$videosResult = $videosStmt->get_result();
$videos = [];
while ($row = $videosResult->fetch_assoc()) {
    $videos[] = $row;
}
header('Content-Type: application/json');
echo json_encode($videos);
?>
