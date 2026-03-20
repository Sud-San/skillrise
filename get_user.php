<?php
include 'connection.php';
$res = mysqli_query($conn, "SELECT user_email, user_password FROM user_tbl WHERE user_id = 2");
$row = mysqli_fetch_assoc($res);
echo $row['user_email'] . " | " . $row['user_password'] . "\n";
?>
