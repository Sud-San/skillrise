<?php
session_start();
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['id'] = 1; 
$_POST['status'] = 1;
$_SESSION['tutor_id'] = 6;
include 'update_assignment_status.php';
