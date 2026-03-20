<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 5);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "Code Maze";
$preloaderText = "Loading Code Maze...";
$extraCss = '<link rel="stylesheet" href="../css/code-maze.css">';
include_once("../includes/header.php");
?>
<div class="game-page-header">
    <h1 class="game-page-title">🧩 Code Maze</h1>
    <p class="game-page-description">Navigate through mazes using code commands. Solve puzzles to reach the
        goal!</p>
</div>

<div class="game-progress-bar">
    <div class="progress-info">
        <div class="progress-stat">
            <div class="progress-label">Level</div>
            <div class="progress-value"><span id="currentLevel">1</span>/<span id="totalLevels">8</span></div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Moves</div>
            <div class="progress-value" id="movesCount">0</div>
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

<div class="level-info">
    <div class="level-number" id="levelTitle">Level 1: Simple Path</div>
    <p style="color: var(--text-secondary);" id="levelDescription">Move the player (🤖) to the goal (🎯)</p>
</div>

<div class="maze-container">
    <div class="maze-grid" id="mazeGrid"></div>
</div>

<div class="commands-panel">
    <h3 style="margin-bottom: 1rem; color: var(--neon-cyan);">Movement Commands</h3>
    <div class="commands-grid">
        <button class="command-btn" id="moveUpBtn">⬆️ MOVE UP</button>
        <button class="command-btn" id="moveDownBtn">⬇️ MOVE DOWN</button>
        <button class="command-btn" id="moveLeftBtn">⬅️ MOVE LEFT</button>
        <button class="command-btn" id="moveRightBtn">➡️ MOVE RIGHT</button>
    </div>

    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <button class="btn btn-secondary" id="resetBtn" style="flex: 1;">🔄 Reset Level</button>
        <button class="btn btn-primary" id="skipBtn" style="flex: 1;">⏭️ Skip Level</button>
    </div>

    <div class="code-display" id="codeDisplay">
        <div style="color: var(--text-muted); margin-bottom: 0.5rem;">Your Code:</div>
        <div id="codeLines"></div>
    </div>
</div>
</div>

<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon" id="resultIcon">🏆</div>
        <h2 class="result-title" id="resultTitle">All Levels Complete!</h2>
        <p class="result-message" id="resultMessage"></p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="result-stat-value" id="finalScore">0</div>
                <div class="result-stat-label">Final Score</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="totalMoves">0</div>
                <div class="result-stat-label">Total Moves</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="levelsCompleted">0/8</div>
                <div class="result-stat-label">Levels Completed</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="efficiency">0%</div>
                <div class="result-stat-label">Efficiency</div>
            </div>
        </div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Play Again</button>
            <button class="btn btn-secondary" onclick="window.location.href='../index.php'">🏠 Dashboard</button>
        </div>
    </div>
</div>

<script src="../js/main.js"></script>
<?php
$sql = "SELECT * FROM game_challenges
                  INNER JOIN games 
                  ON games.game_id = game_challenges.game_id
                  WHERE games.game_id = 5";
$result = mysqli_query($conn, $sql);
$fetchedLevels = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    if ($decoded) {
        $decoded['challenge_id'] = $row['id'];
        $fetchedLevels[] = $decoded;
    }
}
?>
<script>
    const fetchedLevelsFromDB = <?php echo json_encode($fetchedLevels); ?>;
</script>
<?php
$extraJs = '<script src="../js/code-maze.js"></script>';
include_once("../includes/footer.php");
?>