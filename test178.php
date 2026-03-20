<?php
include 'connection.php';
$course_id = 178;
$res = $conn->query("SELECT * FROM lessons_tbl WHERE course_id = $course_id");
echo "Lessons for $course_id:\n";
while($row = $res->fetch_assoc()) { print_r($row); }
$res = $conn->query("SELECT * FROM videos_tbl WHERE course_id = $course_id");
echo "Videos for $course_id:\n";
while($row = $res->fetch_assoc()) { print_r($row); }
?>
