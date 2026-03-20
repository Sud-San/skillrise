<?php
include 'connection.php';
$res = $conn->query("SELECT * FROM lessons_tbl LIMIT 1");
if ($row = $res->fetch_assoc()) {
    foreach ($row as $k => $v) {
        echo "$k\n";
    }
} else {
    echo "No rows in lessons_tbl\n";
}
?>
