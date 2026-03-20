<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Disable caching for restricted pages to prevent "Back" button issues
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if admin is logged in
if (!isset($_SESSION['admin']) || !isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    
    // If admin ID is set but not logged (locked state), redirect to lock-screen
    if (isset($_SESSION['admin']) && (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true)) {
        // Prevent infinite loop
        if (basename($_SERVER['PHP_SELF']) !== 'lock-screen.php') {
            header("Location: lock-screen.php");
            exit();
        }
    } else {
        // Not logged in at all, redirect to login
        if (basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'logout.php') {
            header("Location: login.php");
            exit();
        }
    }
}
