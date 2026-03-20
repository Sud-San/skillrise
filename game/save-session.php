<?php
session_start();
require_once '../connection.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Validate required fields
if (!isset($data['gameId']) || !isset($data['score']) || !isset($data['time_taken'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 (guest) if not logged in
$game_id = intval($data['gameId']);
$score = intval($data['score']);
$time_taken = intval($data['time_taken']);
$language = isset($data['language']) ? mysqli_real_escape_string($conn, $data['language']) : 'PHP';
$accuracy = isset($data['accuracy']) ? floatval($data['accuracy']) : 0;

// Determine result
$result = ($accuracy >= 60 || $score > 500) ? 'WIN' : 'LOSS';

// Insert game session
$query = "INSERT INTO game_sessions 
          (user_id, game_id, language, score, time_taken_seconds, completed, result, accuracy_percentage, started_at, completed_at) 
          VALUES 
          ('$user_id', '$game_id', '$language', '$score', '$time_taken', 1, '$result', '$accuracy', NOW(), NOW())";

if (mysqli_query($conn, $query)) {
    $session_id = mysqli_insert_id($conn);

    // Update user stats - using a common pattern for XP calculation (10% of score)
    $xp_gained = floor($score / 10);
    $update_user = "UPDATE users 
                   SET total_score = total_score + $score,
                       games_played = games_played + 1,
                       xp = xp + $xp_gained
                   WHERE id = '$user_id'";

    mysqli_query($conn, $update_user);

    echo json_encode([
        'success' => true,
        'message' => 'Session saved successfully',
        'session_id' => $session_id,
        'xp_gained' => $xp_gained
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . mysqli_error($conn)
    ]);
}
?>
