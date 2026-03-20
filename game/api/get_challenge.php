<?php
require 'db.php';

$game_type = $_GET['type']; // bug_race, debugging, sql, etc.
$language  = $_GET['language'];
$difficulty = $_GET['difficulty'];

// Map string types to game_ids from the games table in SQL
$game_ids = [
    "bug_race" => 7,
    "debugging" => 1,
    "sql" => 6,
    "code_predictor" => 2,
    "1v1" => 10
];

$game_id = $game_ids[$game_type] ?? 10; // Default to 1v1 if unknown

$query = "SELECT * FROM `game_challenges` 
          WHERE `game_id` = '$game_id' 
          AND `difficulty` = '$difficulty' 
          ORDER BY RAND() LIMIT 1";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// The challenges column is JSON in the schema
if ($data && isset($data['challenges'])) {
    $challenge_data = json_decode($data['challenges'], true);
    // Merge with top level for compatibility
    $data = array_merge($data, $challenge_data);
}

echo json_encode($data);
?>
