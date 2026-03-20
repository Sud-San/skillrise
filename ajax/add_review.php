<?php
header('Content-Type: application/json');
session_start();
require_once('../connection.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = (int)$_POST['course_id'];
$rating = (int)$_POST['rating'];
$comment = mysqli_real_escape_string($conn, $_POST['comment']);

// Verify enrollment
$checkQuery = "SELECT enrollment_id FROM enrollments_tbl WHERE user_id = $user_id AND course_id = $course_id LIMIT 1";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) === 0) {
    echo json_encode(['success' => false, 'message' => 'Only enrolled users can submit reviews']);
    exit;
}

// Insert review (feedback_tbl)
$insertQuery = "INSERT INTO feedback_tbl (user_id, course_id, rating, comment, status, created_at) 
                VALUES ($user_id, $course_id, $rating, '$comment', 1, NOW())";

if (mysqli_query($conn, $insertQuery)) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>
