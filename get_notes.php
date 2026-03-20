<?php
include 'tutor/connection.php';
$res = mysqli_query($conn, "SELECT * FROM course_notes LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) {
    echo json_encode($row) . "\n";
}
?>
