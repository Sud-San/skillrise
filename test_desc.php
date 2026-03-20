<?php
include 'connection.php';
$res = $conn->query("DESCRIBE lessons_tbl");
while($row = $res->fetch_assoc()) { print_r($row); }
$res = $conn->query("DESCRIBE videos_tbl");
while($row = $res->fetch_assoc()) { print_r($row); }
$res = $conn->query("DESCRIBE course_tbl");
// let's just get the column names of course_tbl
while($row = $res->fetch_assoc()) { echo $row['Field'] . ", "; }
?>
