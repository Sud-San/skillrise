<?php
include_once("../../connection.php");

$session_id = $_POST['session_id'];
$user_id    = $_POST['user_id'];
$game_type  = $_POST['game_type'];
$challenge_id = $_POST['challenge_id'];
$is_correct = $_POST['is_correct'];
$score      = $_POST['score'];
$time_taken = $_POST['time_taken'];

// Update the game_sessions table instead of non-existent attempt tables
$query = "UPDATE `game_sessions` 
          SET `score` = `score` + $score,
              `accuracy_percentage` = IF('$is_correct' = '1', 
                  (IFNULL(`accuracy_percentage`, 100) + 100) / 2, 
                  (IFNULL(`accuracy_percentage`, 100) + 0) / 2
              )
          WHERE `session_id` = '$session_id'";

mysqli_query($conn, $query);

echo json_encode(["status" => "saved", "session_id" => $session_id]);
?>
