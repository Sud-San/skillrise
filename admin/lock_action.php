<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set logged to false to trigger auth guard, but keep admin_id for lock-screen context
$_SESSION['admin_logged'] = false;

// Redirect 1: Go to the lock screen page
header("Location: lock-screen.php");
exit();
?>
