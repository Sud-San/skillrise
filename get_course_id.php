<?php
include 'connection.php';
$res = mysqli_query($conn, "SELECT course_id FROM course_tbl WHERE course_status = 1 LIMIT 1");
$row = mysqli_fetch_assoc($res);
echo $row ? $row['course_id'] : '0';
?>
