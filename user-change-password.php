<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) exit("Session expired");

$user_id = (int) $_SESSION['user_id'];

$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new !== $confirm) exit("New passwords do not match");

/* Get current hash */
$stmt = $conn->prepare("SELECT user_password FROM user_tbl WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result || !password_verify($old, $result['user_password'])) {
    exit("Old password is incorrect");
}

/* Update new password */
$newHash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE user_tbl SET user_password=? WHERE user_id=?");
$stmt->bind_param("si", $newHash, $user_id);
$stmt->execute();
$stmt->close();

echo "Password changed successfully!";
?>
