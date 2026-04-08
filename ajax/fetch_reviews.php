<?php
header('Content-Type: application/json');
require_once('../connection.php');

if (!isset($_GET['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Course ID missing']);
    exit;
}

$course_id = (int) $_GET['course_id'];

$query = "
    SELECT f.*, u.user_name 
    FROM feedback_tbl f
    LEFT JOIN user_tbl u ON f.user_id = u.user_id
    WHERE f.course_id = $course_id and f.status = 1
    ORDER BY f.created_at DESC
";

$result = mysqli_query($conn, $query);
$reviews = [];

while ($row = mysqli_fetch_assoc($result)) {
    $reviews[] = [
        'user_name' => $row['user_name'],
        'rating' => $row['rating'],
        'comment' => $row['message'],
        'created_at' => date('d M Y', strtotime($row['created_at']))
    ];
}

echo json_encode(['success' => true, 'reviews' => $reviews]);
?>