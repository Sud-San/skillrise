<?php
// Start session so we can access and destroy it
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy the session completely
session_destroy();

// Redirect to login page with logout success message
header('Location: login.php?logout=1');
exit();
?>