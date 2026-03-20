<?php
include_once("../../connection.php");
$sql = "SELECT * FROM game_challenges WHERE game_id = 4";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    echo "ID: " . $row['game_id'] . "\n";
    echo "Challenges: " . $row['challenges'] . "\n\n";
}
?>