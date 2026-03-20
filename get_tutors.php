<?php
include 'tutor/connection.php';
$res = mysqli_query($conn, "SELECT tutor_email, password FROM tutor_tbl WHERE tutor_status = 1 LIMIT 5");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['tutor_email'] . " | " . $row['password'] . "\n";
}
?>
