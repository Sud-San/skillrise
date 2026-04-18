<?php
/**
 * tutor/includes/package_check.php
 * 
 * Centralized logic to verify if a tutor has an active (non-expired) package.
 * Redirects to account.php if the package is expired or missing.
 */

// Basic session check if not already handled
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if session is missing
if (!isset($_SESSION['tutor_id'])) {
    header('Location: login.php');
    exit();
}

// We rely on the calling script to have included connection.php, 
// but we include it once here to be safe and independent.
include_once(dirname(__DIR__) . '/connection.php');

$t_id = (int)$_SESSION['tutor_id'];

// Query for the latest successfully paid package
$p_query = "SELECT end_date FROM tutor_package_tbl WHERE tutor_id = $t_id AND payment_status = 1 ORDER BY created_at DESC LIMIT 1";
$p_res = mysqli_query($conn, $p_query);
$p_data = mysqli_fetch_assoc($p_res);

$has_active_pkg = false;

if ($p_data) {
    $expiry = new DateTime($p_data['end_date']);
    $expiry->setTime(23, 59, 59); // Active until the very end of the day
    $today = new DateTime();
    
    if ($today <= $expiry) {
        $has_active_pkg = true;
    }
}

if (!$has_active_pkg) {
    // Redirect to account page with a flag to show an alert
    header('Location: account.php?package_expired=1');
    exit();
}
