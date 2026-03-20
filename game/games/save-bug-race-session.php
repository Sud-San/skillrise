<?php
session_start();
header('Content-Type: application/json');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Database configuration
$host = 'localhost';
$dbname = 'codezy';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
        exit;
    }
    
    // Validate required fields
    $required = ['score', 'bugs_fixed', 'max_combo', 'time_taken'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
            exit;
        }
    }
    
    $user_id = intval($_SESSION['user_id']);
    $game_id = 3; // Bug Race game ID
    $score = intval($data['score']);
    $bugs_fixed = intval($data['bugs_fixed']);
    $max_combo = intval($data['max_combo']);
    $time_taken = intval($data['time_taken']);
    $accuracy = isset($data['accuracy']) ? floatval($data['accuracy']) : 0;
    
    // Calculate result
    if ($score >= 1000) {
        $result = 'WIN';
    } elseif ($score >= 500) {
        $result = 'WIN';
    } else {
        $result = 'LOSS';
    }
    
    // Insert session record
    $stmt = $pdo->prepare("
        INSERT INTO game_sessions 
        (user_id, game_id, language, score, time_taken_seconds, completed, result, accuracy_percentage, combo_max, started_at, completed_at) 
        VALUES 
        (:user_id, :game_id, 'PHP', :score, :time_taken, 1, :result, :accuracy, :combo_max, NOW(), NOW())
    ");
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':game_id' => $game_id,
        ':score' => $score,
        ':time_taken' => $time_taken,
        ':result' => $result,
        ':accuracy' => $accuracy,
        ':combo_max' => $max_combo
    ]);
    
    $session_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Game session saved successfully',
        'session_id' => $session_id,
        'score' => $score,
        'debug' => [
            'user_id' => $user_id,
            'game_id' => $game_id,
            'bugs_fixed' => $bugs_fixed,
            'max_combo' => $max_combo
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>