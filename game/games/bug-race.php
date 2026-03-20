<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 7);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {

    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "Bug Race";
$preloaderText = "Loading Bug Race...";
$extraCss = '<link rel="stylesheet" href="../css/bug-race.css">';
$backUrl = "index.php";
include_once("../includes/header.php");
?>
<div class="game-page-header">
    <h1 class="game-page-title">🐛 Bug Race</h1>
    <p class="game-page-description">Race against time to fix bugs! Faster fixes = higher score. Build combos!
    </p>
</div>

<div class="compact-stats-box">
    <div class="compact-timer" id="raceTimer">1:00</div>

    <div class="compact-stats-row">
        <div class="compact-stat">
            <span id="bugsFixed">0</span>
            <small>Bugs</small>
        </div>
        <div class="compact-stat">
            <span id="score">0</span>
            <small>Score</small>
        </div>
        <div class="compact-stat">
            <span id="combo">0x</span>
            <small>Combo</small>
        </div>
    </div>
</div>


<div class="challenge-card" id="challengeCard" style="display: none;">
    <div class="challenge-header">
        <h3 id="challengeTitle">Find and fix the bug!</h3>
        <span class="bug-indicator">🐛 Bug Detected!</span>
        <button class="btn btn-hint" id="hintBtn">💡 Hint</button>
        <div class="hint-modal" id="hintModal">
            <div class="hint-box">
                <h3>💡 Hint</h3>
                <p id="hintText"></p>
                <button class="btn btn-primary" onclick="closeHint()">Got it</button>
            </div>
        </div>
    </div>


    <div class="code-block" id="codeBlock"></div>

    <div style="margin-top: 1.5rem;">
        <h4 style="margin-bottom: 1rem;">What's the bug?</h4>
        <div class="answer-options" id="answerOptions"></div>
    </div>

    <div class="explanation-box" id="explanationBox">
        <h4 id="feedbackTitle">✓ Fixed!</h4>
        <p id="explanationText"></p>
    </div>
</div>

<div class="challenge-card" id="startCard">
    <h2 style="text-align: center; color: var(--neon-cyan); margin-bottom: 1.5rem;">Ready to Race?</h2>
    <p style="text-align: center; color: var(--text-secondary); font-size: 1.125rem; margin-bottom: 2rem;">
        Fix as many bugs as you can in 2 minutes! Build combos for bonus points!
    </p>
    <div style="text-align: center;">
        <button class="btn btn-primary" id="startBtn" style="font-size: 1.25rem; padding: 1.25rem 3rem;">
            🏁 Start Race!
        </button>
    </div>
</div>
</div>

<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon" id="resultIcon">🏆</div>
        <h2 class="result-title" id="resultTitle">Race Complete!</h2>
        <p class="result-message" id="resultMessage"></p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="result-stat-value" id="finalScore">0</div>
                <div class="result-stat-label">Final Score</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalBugs">0</div>
                <div class="result-stat-label">Bugs Fixed</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalCombo">0x</div>
                <div class="result-stat-label">Max Combo</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="avgTime">0.0s</div>
                <div class="result-stat-label">Avg Fix Time</div>
            </div>
        </div>

        <div class="save-status" id="saveStatus" style="display: none;"></div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Race Again</button>
            <button class="btn btn-secondary" onclick="window.location.href='index.php'">🏠 Dashboard</button>
        </div>
    </div>
</div>
<?php
$query = "SELECT * FROM game_challenges
              INNER JOIN games 
              ON games.game_id = game_challenges.game_id
              WHERE games.game_id = 7";

$result = mysqli_query($conn, $query);

$bugs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    $decoded['challenge_id'] = $row['id']; // Include ID for API tracking
    $bugs[] = $decoded;
}
?>
<script>
    console.log(
        "%c🐛 Bug Race Initialized",
        "color: #00fff2; font-size: 16px; font-weight: bold",
    );
    const bugs = <?php echo json_encode($bugs); ?>
</script>
<script src="../js/bug-race.js"></script>
<?php include_once("../includes/footer.php"); ?>
</body>

</html>