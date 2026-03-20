<?php
include 'tutor/connection.php';
$tables = ['course_notes', 'feedback_tbl', 'enrollments_tbl'];
foreach ($tables as $table) {
    $res = mysqli_query($conn, "DESCRIBE $table");
    if ($res) {
        echo "Columns in $table:\n";
        while ($row = mysqli_fetch_assoc($res)) {
            echo $row['Field'] . " (" . $row['Type'] . ")\n";
        }
        echo "\n";
    } else {
        echo "Error describing $table: " . mysqli_error($conn) . "\n";
    }
}
?>
