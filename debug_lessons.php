<?php
include 'tutor/connection.php';
$res = mysqli_query($conn, "DESCRIBE lessons_tbl");
if ($res) {
    echo "Columns in lessons_tbl:\n";
    while ($row = mysqli_fetch_assoc($res)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
