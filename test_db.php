<?php
include 'connection.php';
$course_id = 178; // from screenshot
$res = $conn->query("SELECT * FROM lessons_tbl WHERE course_id = $course_id");
echo "LESSONS for $course_id:\n";
while($row = $res->fetch_assoc()) { print_r($row); }

$res2 = $conn->query("SELECT * FROM videos_tbl WHERE course_id = $course_id");
echo "VIDEOS for $course_id:\n";
while($row = $res2->fetch_assoc()) { print_r($row); }

$res3 = $conn->query("SELECT * FROM lessons_tbl LIMIT 5");
echo "SAMPLE LESSONS:\n";
while($row = $res3->fetch_assoc()) { print_r($row); }
?>
