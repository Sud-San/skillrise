<?php
session_start();
include_once("../../connection.php");


header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = $_POST['game_id'] ?? 0;
$language = $_POST['language'] ?? 'mixed';

$stmt = $conn->prepare("
    INSERT INTO `game_sessions` (`user_id`, `game_id`, `language`, `started_at`)
    VALUES (?, ?, ?, NOW())
");

$stmt->bind_param("iis", $user_id, $game_id, $language);
$stmt->execute();

echo json_encode([
    "status" => "success",
    "session_id" => $stmt->insert_id
]);
