<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'codezy');
// Check connection
$logo = "../SkillRise.png";
$logo1 = "assets/SkillRise_logo1.png";
$company_name = "SkillRise Academy";
$user_profile_path = "admin/assets/images/users/";
$tutor_profile_path = "../admin/assets/images/tutors/";
$admin_profile_path = "../admin/assets/images/admin/";
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

// Razorpay Credentials
if (!defined('RAZORPAY_KEY_ID')) {
	define('RAZORPAY_KEY_ID', 'rzp_test_RegvY7qWQeiLBC');
}

if (!defined('RAZORPAY_KEY_SECRET')) {
	define('RAZORPAY_KEY_SECRET', 'LLpuSxry8Kzyn8y5VWcB5Vu3');
}
