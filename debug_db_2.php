<?php
include 'tutor/connection.php';
$res = mysqli_query($conn, "SHOW COLUMNS FROM videos_tbl LIKE 'video_status'");
if (mysqli_num_rows($res) > 0) {
    echo "video_status column EXISTS\n";
    $res2 = mysqli_query($conn, "SELECT video_id, video_status FROM videos_tbl LIMIT 5");
    while($row = mysqli_fetch_assoc($res2)) {
        echo "ID: " . $row['video_id'] . " Status: " . $row['video_status'] . "\n";
    }
} else {
    echo "video_status column MISSING\n";
    $res3 = mysqli_query($conn, "DESCRIBE videos_tbl");
    while($row = mysqli_fetch_assoc($res3)) {
        echo $row['Field'] . "\n";
    }
}
?>
