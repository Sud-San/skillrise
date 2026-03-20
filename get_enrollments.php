<?php
include 'connection.php';
$res = mysqli_query($conn, "SELECT * FROM enrollments_tbl WHERE course_id = 1 LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) {
    echo json_encode($row) . "\n";
}
?>
