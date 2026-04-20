<?php
session_start();

// Only clear frontend login — DO NOT destroy session
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_role']);
unset($_SESSION['user_email']);
unset($_SESSION['user_phone']);
unset($_SESSION['user_avatar']);
unset($_SESSION['game_preloader']);

// DO NOT REMOVE SESSION COOKIE — keeps admin session safe

header("Location: login.php");
exit;
?>