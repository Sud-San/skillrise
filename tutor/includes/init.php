<?php
// includes/init.php
// Include this ONE file at the top of every page

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['tutor_id'])) {
    header('Location: login.php');
    exit();
}
