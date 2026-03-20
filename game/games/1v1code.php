<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 10);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "1v1 Code Duel";
$preloaderText = "Loading Duel Arena...";
$extraCss = '<link rel="stylesheet" href="../css/1v1code.css">';
include_once("../includes/header.php");
?>
<div class="game-page-header">
    <h1 class="game-page-title">⚔️ 1v1 Code Duel</h1>
    <p class="game-page-description">Race against an opponent to solve coding challenges. Fast + Correct =
        Victory!</p>
</div>

<div id="modeSelection" class="mode-selector">
    <div class="mode-btn" onclick="startGame('vsAI')">
        <div class="mode-icon">🤖</div>
        <div class="mode-title">VS AI</div>
        <div class="mode-desc">Battle against the computer</div>
    </div>
    <div class="mode-btn" onclick="startGame('practice')">
        <div class="mode-icon">🎯</div>
        <div class="mode-title">Practice Mode</div>
        <div class="mode-desc">Solo practice with timer</div>
    </div>
</div>

<div id="gameArea" style="display: none;">
    <div id="countdownDisplay"></div>

    <div class="duel-arena" id="duelArena" style="display: none;">
        <div class="player-side player1">
            <div class="player-header">
                <div class="player-avatar">👨‍💻</div>
                <div class="player-name">You</div>
                <div class="player-progress">
                    <div class="player-progress-fill" id="player1Progress" style="width: 0%">0%</div>
                </div>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-value" id="p1Correct">0</div>
                    <div class="stat-label">Correct</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="p1Wrong">0</div>
                    <div class="stat-label">Wrong</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="p1Time">0s</div>
                    <div class="stat-label">Time</div>
                </div>
            </div>
            <div class="challenge-display">
                <div class="challenge-question" id="p1Question"></div>
                <input type="text" class="answer-input" id="p1Input" placeholder="Type your answer...">
            </div>
        </div>

        <div class="player-side player2">
            <div class="player-header">
                <div class="player-avatar">🤖</div>
                <div class="player-name" id="player2Name">AI Opponent</div>
                <div class="player-progress">
                    <div class="player-progress-fill" id="player2Progress" style="width: 0%">0%</div>
                </div>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-value" id="p2Correct">0</div>
                    <div class="stat-label">Correct</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="p2Wrong">0</div>
                    <div class="stat-label">Wrong</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="p2Time">0s</div>
                    <div class="stat-label">Time</div>
                </div>
            </div>
            <div class="challenge-display">
                <div class="challenge-question" id="p2Question"></div>
                <input type="text" class="answer-input" id="p2Input" placeholder="AI is thinking..." disabled>
            </div>
        </div>
    </div>
</div>
</div>

<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon" id="resultIcon">🏆</div>
        <h2 class="result-title" id="resultTitle">Victory!</h2>
        <p class="result-message" id="resultMessage"></p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="result-stat-value" id="finalCorrect">0</div>
                <div class="result-stat-label">Correct Answers</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalTime">0s</div>
                <div class="result-stat-label">Total Time</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalAccuracy">0%</div>
                <div class="result-stat-label">Accuracy</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalScore">0</div>
                <div class="result-stat-label">Score</div>
            </div>
        </div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Duel Again</button>
            <button class="btn btn-secondary" onclick="window.location.href='../index.php'">🏠 Dashboard</button>
        </div>
    </div>
    <?php
    $query = "SELECT * FROM game_challenges
          INNER JOIN games 
          ON games.game_id = game_challenges.game_id
          WHERE games.game_id = 10";

    $result = mysqli_query($conn, $query);

    $challenges = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $decoded = json_decode($row['challenges'], true);
        $decoded['challenge_id'] = $row['id'];
        $challenges[] = $decoded;
    }
    ?>
    <script>
        const challenges = <?php echo json_encode($challenges); ?>
    </script>
    <script src="../js/1v1code.js"></script>
    <?php include_once("../includes/footer.php"); ?>