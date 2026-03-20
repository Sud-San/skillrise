<?php
include 'tutor/connection.php';
$res = mysqli_query($conn, "SHOW TABLES");
echo "Tables in database:\n";
while ($row = mysqli_fetch_row($res)) {
    echo $row[0] . "\n";
}
?>
