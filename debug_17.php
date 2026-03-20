<?php
include 'connection.php';
$course_id = 17;

echo "--- NOTES for COURSE $course_id ---\n";
$res = mysqli_query($conn, "SELECT * FROM course_notes WHERE course_id = $course_id");
if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        echo json_encode($row) . "\n";
    }
} else {
    echo "No notes found.\n";
}

echo "\n--- REVIEWS for COURSE $course_id ---\n";
$res2 = mysqli_query($conn, "SELECT f.*, u.user_name FROM feedback_tbl f JOIN user_tbl u ON f.user_id = u.user_id WHERE f.course_id = $course_id");
if (mysqli_num_rows($res2) > 0) {
    while ($row2 = mysqli_fetch_assoc($res2)) {
        echo json_encode($row2) . "\n";
    }
} else {
    echo "No reviews found.\n";
}
?>
