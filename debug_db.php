<?php
include 'tutor/connection.php';
$res = mysqli_query($conn, "DESCRIBE videos_tbl");
if (!$res) {
    echo "Error describing videos_tbl: " . mysqli_error($conn);
} else {
    echo "Columns in videos_tbl:\n";
    while ($row = mysqli_fetch_assoc($res)) {
        echo $row['Field'] . " (" . $row['Type'] . ") " . ($row['Key'] == 'PRI' ? '[PRI]' : '') . "\n";
    }
}
?>
