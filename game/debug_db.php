<?php
include_once("../connection.php");

echo "--- GAMES ---\n";
$games_res = mysqli_query($conn, "SELECT * FROM games");
while ($row = mysqli_fetch_assoc($games_res)) {
    print_r($row);
}

echo "\n--- GAME_CHALLENGES SCHEMA ---\n";
$schema_res = mysqli_query($conn, "DESCRIBE game_challenges");
while ($row = mysqli_fetch_assoc($schema_res)) {
    print_r($row);
}

echo "\n--- SAMPLE CHALLENGE FOR SQL-QUERY-MASTER (game_id fallback check) ---\n";
$sample_res = mysqli_query($conn, "SELECT * FROM game_challenges LIMIT 1");
$row = mysqli_fetch_assoc($sample_res);
print_r($row);
?>