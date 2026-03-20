<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 8);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "Code Refactor Challenge";
$preloaderText = "Loading Code Refactor...";
$extraCss = '<link rel="stylesheet" href="../css/code-refactor.css">';
include_once("../includes/header.php");
?>
<div class="game-page-header">
    <h1 class="game-page-title">🔧 Code Refactor Challenge</h1>
    <p class="game-page-description">Transform messy code into clean, optimized masterpieces!</p>
</div>

<div class="game-progress-bar">
    <div class="progress-info">
        <div class="progress-stat">
            <div class="progress-label">Challenge</div>
            <div class="progress-value"><span id="currentChallenge">1</span>/<span id="totalChallenges">10</span></div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Score</div>
            <div class="progress-value" id="score">0</div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Refactors</div>
            <div class="progress-value" id="refactorCount">0</div>
        </div>
    </div>
    <div class="progress-bar-track">
        <div class="progress-bar-fill" id="progressBar" style="width: 0%"></div>
    </div>
</div>

<div class="issues-panel" id="issuesPanel">
    <h3 style="margin-bottom: 1rem; color: var(--neon-pink);">🐛 Code Issues to Fix</h3>
    <div id="issuesList"></div>
</div>

<div class="refactor-container">
    <div class="code-panel">
        <div class="panel-header">
            <div class="panel-title">
                😱 Messy Code
            </div>
            <span class="panel-badge badge-messy">BEFORE</span>
        </div>
        <div class="code-editor">
            <div class="code-display" id="messyCode"></div>
        </div>
    </div>

    <div class="code-panel">
        <div class="panel-header">
            <div class="panel-title">
                ✨ Your Refactored Code
            </div>
            <span class="panel-badge badge-clean">AFTER</span>
        </div>
        <div class="code-editor">
            <textarea class="code-textarea" id="refactoredCode" placeholder="Type your clean code here..."></textarea>
        </div>
    </div>
</div>

<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">Lines of Code</div>
        <div class="metric-value" id="linesCount">0</div>
        <div class="comparison-indicator" id="linesComparison"></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Code Quality</div>
        <div class="metric-value" id="qualityScore">0%</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Issues Fixed</div>
        <div class="metric-value" id="issuesFixed">0/<span id="totalIssues">0</span></div>
    </div>
</div>

<div class="hint-box" id="hintBox">
    <div class="hint-title">💡 Hint</div>
    <div id="hintText"></div>
</div>

<div class="action-buttons">
    <button class="btn btn-secondary" id="showHintBtn">💡 Show Hint</button>
    <button class="btn btn-secondary" id="resetBtn">🔄 Reset Code</button>
    <button class="btn btn-primary" id="submitBtn" style="flex: 1;">✓ Check Refactor</button>
</div>
</div>

<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon" id="resultIcon">🏆</div>
        <h2 class="result-title" id="resultTitle">Refactoring Complete!</h2>
        <p class="result-message" id="resultMessage"></p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="result-stat-value" id="finalScore">0</div>
                <div class="result-stat-label">Final Score</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="totalRefactors">0</div>
                <div class="result-stat-label">Successful Refactors</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="avgQuality">0%</div>
                <div class="result-stat-label">Avg Quality</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="completionRate">0%</div>
                <div class="result-stat-label">Completion</div>
            </div>
        </div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Play Again</button>
            <button class="btn btn-secondary" onclick="window.location.href='../index.php'">🏠 Dashboard</button>
        </div>
    </div>
</div>

<?php
$sql = "SELECT * FROM game_challenges
                  INNER JOIN games 
                  ON games.game_id = game_challenges.game_id
                  WHERE games.game_id = 8";
$result = mysqli_query($conn, $sql);
$fetchedRefactors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    if ($decoded) {
        $decoded['challenge_id'] = $row['id'];
        $fetchedRefactors[] = $decoded;
    }
}
?>
<script>
    const fetchedRefactorsFromDB = <?php echo json_encode($fetchedRefactors); ?>;
</script>
<?php
$extraJs = '<script src="../js/code-refactor.js"></script>';
include_once("../includes/footer.php");
?>