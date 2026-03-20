<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 3);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "Code Complete";
$preloaderText = "Loading Code Complete...";
$extraCss = '<link rel="stylesheet" href="../css/code-complete.css">';
include_once("../includes/header.php");
?>
<!-- Game Header -->
<div class="game-page-header">
    <h1 class="game-page-title">✏️ Code Complete</h1>
    <p class="game-page-description">
        Fill in the missing code to complete the function. W3Schools style challenges!
    </p>
</div>

<!-- Progress Bar -->
<div class="game-progress-bar">
    <div class="progress-info">
        <div class="progress-stat">
            <div class="progress-label">Timer</div>
            <div class="progress-value" id="timer">5:00</div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Score</div>
            <div class="progress-value" id="score">0</div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Streak</div>
            <div class="progress-value" id="streak">1x</div>
        </div>
    </div>
    <div class="progress-bar-track">
        <div class="progress-bar-fill" id="progressBar" style="width: 0%"></div>
    </div>
    <div class="progress-stat">
        <div class="progress-label">Progress</div>
        <div class="progress-value" id="progress">0/15</div>
    </div>
</div>

<!-- Challenge Card -->
<div class="challenge-card">
    <div class="challenge-header">
        <h3 id="challengeTitle">Complete the Code</h3>
        <span class="game-difficulty difficulty-easy" id="difficulty">Easy</span>
    </div>

    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;" id="instruction">
        Fill in the blank to complete the function
    </p>

    <div class="code-block" id="codeBlock">
        <!-- Code will be inserted here -->
    </div>

    <div style="margin-top: 1.5rem;">
        <h4 style="margin-bottom: 1rem; color: var(--neon-cyan);">Type the missing code:</h4>
        <input type="text" id="answerInput" placeholder="Type your answer here..."
            style="width: 100%; padding: 1rem; background: var(--bg-secondary); border: 2px solid var(--border-color); 
                    border-radius: var(--radius-lg); color: white; font-family: 'Courier New', monospace; font-size: 1rem;">
    </div>

    <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" id="skipBtn">Skip Challenge</button>
        <button class="btn btn-primary" id="submitBtn">Submit Answer</button>
    </div>
</div>
</div>

<!-- Result Modal -->
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
                <div class="result-stat-value" id="completed">0/15</div>
                <div class="result-stat-label">Completed</div>
            </div>
        </div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Play Again</button>
            <button class="btn btn-secondary" onclick="window.history.back()">🏠 Dashboard</button>
        </div>
    </div>
</div>

<!-- Feedback Popup -->
<div class="feedback-popup" id="feedbackPopup">
    <div class="popup-header">
        <h4 class="popup-feedback-title" id="feedbackTitle">✓ Correct!</h4>
        <button class="popup-close" onclick="closeFeedbackPopup()">×</button>
    </div>
    <p class="popup-explanation" id="explanationText"></p>
</div>
<?php
$query = "SELECT * FROM game_challenges
              INNER JOIN games 
              ON games.game_id = game_challenges.game_id
              WHERE games.game_id = 3";

$result = mysqli_query($conn, $query);

$bugs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    if ($decoded) {
        $decoded['challenge_id'] = $row['id'];
        $bugs[] = $decoded;
    }
}
?>
<script>
    let challenges = <?php echo json_encode($bugs); ?>;
</script>
<script src="../js/code-complete.js"></script>
<?php include_once("../includes/footer.php"); ?>