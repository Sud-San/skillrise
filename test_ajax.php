<?php
$_POST['id'] = 1;
$_POST['status'] = 1;
$_SESSION['tutor_id'] = 1;
include 'tutor/ajax/update_assignment_status.php';
echo "DONE";
