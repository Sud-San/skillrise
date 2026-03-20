<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) exit("Session expired");

$user_id = (int) $_SESSION['user_id'];
$mobile  = trim($_POST['number'] ?? '');
$address = trim($_POST['addr_short'] ?? '');

$stmt = $conn->prepare("UPDATE user_details SET mobile=?, address=? WHERE user_id=?");
$stmt->bind_param("ssi", $mobile, $address, $user_id);

if ($stmt->execute()) {
    echo "Contact updated successfully!";
} else {
    echo "Database error";
}

$stmt->close();
?>
