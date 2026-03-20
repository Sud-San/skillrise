<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 6);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "SQL Query Master";
$preloaderText = "Loading SQL Query Master...";
$extraCss = '<link rel="stylesheet" href="../css/sql-query-master.css">';
include_once("../includes/header.php");
?>
<div class="game-page-header">
    <h1 class="game-page-title">💾 SQL Query Master</h1>
    <p class="game-page-description">Write SQL queries to retrieve and manipulate data. Master database
        operations!</p>
</div>

<div class="game-progress-bar">
    <div class="progress-info">
        <div class="progress-stat">
            <div class="progress-label">Challenge</div>
            <div class="progress-value"><span id="currentChallenge">1</span>/<span id="totalChallenges">8</span>
            </div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Time</div>
            <div class="progress-value" id="timer">5:00</div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Score</div>
            <div class="progress-value" id="score">0</div>
        </div>
    </div>
    <div class="progress-bar-track">
        <div class="progress-bar-fill" id="progressBar" style="width: 0%"></div>
    </div>
</div>

<div class="challenge-card">
    <div class="challenge-header">
        <h3 id="challengeTitle">Write a SQL query</h3>
        <span class="game-difficulty difficulty-medium" id="difficulty">Medium</span>
    </div>

    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;" id="instruction"></p>

    <div class="schema-viewer" id="schemaViewer">
        <h4 style="margin-bottom: 1rem; color: var(--neon-violet);">📊 Database Schema</h4>
        <div id="schemaContent"></div>
    </div>

    <div style="margin-bottom: 1.5rem;">
        <h4 style="margin-bottom: 0.5rem; color: var(--neon-cyan);">Write your SQL query:</h4>
        <textarea class="query-editor" id="queryEditor" placeholder="SELECT * FROM ..."></textarea>
    </div>

    <div class="explanation-box" id="explanationBox">
        <h4 id="feedbackTitle">✓ Correct!</h4>
        <p id="explanationText"></p>
    </div>

    <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" id="skipBtn">Skip Challenge</button>
        <button class="btn btn-primary" id="submitBtn">Run Query</button>
    </div>
</div>
</div>

<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon" id="resultIcon">🏆</div>
        <h2 class="result-title" id="resultTitle">Game Complete!</h2>
        <p class="result-message" id="resultMessage"></p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="result-stat-value" id="finalScore">0</div>
                <div class="result-stat-label">Final Score</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="accuracy">0%</div>
                <div class="result-stat-label">Accuracy</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="timeTaken">0:00</div>
                <div class="result-stat-label">Time</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="queriesWritten">0</div>
                <div class="result-stat-label">Queries Written</div>
            </div>
        </div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Play Again</button>
            <button class="btn btn-secondary" onclick="window.history.back()">🏠 Dashboard</button>
        </div>
    </div>
</div>
<?php
$sql = "SELECT * FROM game_challenges
                  INNER JOIN games 
                  ON games.game_id = game_challenges.game_id
                  WHERE games.game_id = 6";
$result = mysqli_query($conn, $sql);
$challenges = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    $decoded['challenge_id'] = $row['id'];
    $challenges[] = $decoded;
}
?>
<script>
    const fetchedChallenges = <?php echo json_encode($challenges); ?>;
</script>
<script src="../js/sql-query-master.js"></script>
<?php include_once("../includes/footer.php"); ?>