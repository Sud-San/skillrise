<?php
// auth_check.php - Include this at the top of every protected page
// This file ensures only logged-in tutors can access the page

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if tutor is logged in
if (!isset($_SESSION['tutor_logged']) || $_SESSION['tutor_logged'] !== true) {
    // Not logged in, redirect to login page
    header('Location: login.php');
    exit();
}

// Optional: Check for session timeout (30 minutes of inactivity)
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout_duration) {
    // Session expired
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}

// Update last activity time
$_SESSION['login_time'] = time();
?>
