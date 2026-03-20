<?php
include_once("../../connection.php");

$session_id = $_POST['session_id'];
$final_score = $_POST['final_score'];
$time_taken  = $_POST['time_taken'];
$accuracy    = $_POST['accuracy'];

// Align result values with enum ('WIN', 'LOSS', 'INCOMPLETE')
$result_val = $_POST['result'] ?? 'INCOMPLETE'; 

$query = "UPDATE `game_sessions` 
          SET `score` = '$final_score',
              `time_taken_seconds` = '$time_taken',
              `completed` = 1,
              `result` = '$result_val',
              `accuracy_percentage` = '$accuracy',
              `completed_at` = NOW()
          WHERE `session_id` = '$session_id'";

mysqli_query($conn, $query);

// Get user_id for the stored procedure
$user_fetch = mysqli_query($conn, "SELECT `user_id` FROM `game_sessions` WHERE `session_id` = '$session_id'");
$user_data = mysqli_fetch_assoc($user_fetch);
$user_id = $user_data['user_id'];

// Leverage stored procedure for user statistics and level-ups
mysqli_query($conn, "CALL sp_update_user_stats($user_id, $session_id)");

echo json_encode(["status" => "completed", "session_id" => $session_id]);
?>
