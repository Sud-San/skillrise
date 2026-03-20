<?php
include '../connection.php';
$res = $conn->query("DESCRIBE notes_tbl");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | NULL:" . $row['Null'] . " | KEY:" . $row['Key'] . " | DEFAULT:" . $row['Default'] . "\n";
    }
} else {
    echo "Error (notes_tbl): " . $conn->error . "\n";
}

// Also check if there is a course_notes table
$res2 = $conn->query("DESCRIBE course_notes");
if ($res2) {
    echo "\ncourse_notes columns:\n";
    while ($row = $res2->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | NULL:" . $row['Null'] . " | KEY:" . $row['Key'] . " | DEFAULT:" . $row['Default'] . "\n";
    }
} else {
    echo "\ncourse_notes doesn't exist: " . $conn->error . "\n";
}

// Check all tables in the DB that have 'note' in them
$res3 = $conn->query("SHOW TABLES LIKE '%note%'");
echo "\nTables with 'note':\n";
while ($row = $res3->fetch_row()) { echo $row[0] . "\n"; }
?>
