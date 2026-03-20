<?php
include 'connection.php';
$res = mysqli_query($conn, "SELECT course_id FROM course_notes LIMIT 1");
$row = mysqli_fetch_assoc($res);
echo $row ? $row['course_id'] : '0';
?>
