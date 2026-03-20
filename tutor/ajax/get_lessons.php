<?php
session_start();
include '../../connection.php';

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    $tutor_id = $_SESSION['tutor_id'] ?? 0;

    // Verify course belongs to tutor
    $check = $conn->prepare("SELECT course_id FROM course_tbl WHERE course_id = ? AND tutor_id = ?");
    $check->bind_param("ii", $course_id, $tutor_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("SELECT lesson_id, lesson_title FROM lessons_tbl WHERE course_id = ? ORDER BY lesson_id ASC");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $lessons = [];
        while ($row = $result->fetch_assoc()) {
            $lessons[] = $row;
        }
        echo json_encode($lessons);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>
