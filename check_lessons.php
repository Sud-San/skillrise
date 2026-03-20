<?php
include 'connection.php';
$res = $conn->query("SELECT * FROM lessons_tbl WHERE course_id=178");
echo "Total lessons: " . $res->num_rows . "\n";
while($row = $res->fetch_assoc()) {
    echo $row['lesson_id'] . " - " . $row['lesson_title'] . "\n";
}
?>
