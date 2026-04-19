<?php
include __DIR__ . '/../connection.php';

$res = mysqli_query($conn, "SHOW COLUMNS FROM tutor_tbl");
if (!$res) {
    echo "Error: " . mysqli_error($conn);
} else {
    while ($row = mysqli_fetch_assoc($res)) {
        echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
    }
}

$res = mysqli_query($conn, "SELECT tutor_id, tutor_password FROM tutor_tbl LIMIT 1");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    echo "\nSample Data:\n";
    echo "Tutor ID: " . $row['tutor_id'] . "\n";
    echo "Password Length: " . strlen($row['tutor_password']) . "\n";
    echo "Password Content (Start): " . substr($row['tutor_password'], 0, 5) . "...\n";
}
?>
