<?php
include 'connection.php';
$course_id = 178;
$res = $conn->query("
    SELECT l.*, v.video_id, v.video_url, v.video_status, v.uploaded_at 
    FROM lessons_tbl l
    LEFT JOIN videos_tbl v ON l.lesson_id = v.lesson_id 
    WHERE l.course_id = 178 
    ORDER BY l.lesson_order ASC
");
while($row = $res->fetch_assoc()) {
    echo "Lesson ID " . $row['lesson_id'] . " Name: " . $row['lesson_title'] . "\n";
}
?>
