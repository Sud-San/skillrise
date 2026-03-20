<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 12);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "Guess the Error Message";
$extraCss = '<link rel="stylesheet" href="../css/guess-error.css">';
include_once("../includes/header.php");
?>
<div class="game-page-header">
    <h1 class="game-page-title">🐛 Guess the Error Message</h1>
    <p class="game-page-description">See the error → guess the cause. Master debugging like a pro!</p>
</div>

<div class="game-progress-bar">
    <div class="progress-info">
        <div class="progress-stat">
            <div class="progress-label">Error</div>
            <div class="progress-value"><span id="currentError">1</span>/10</div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Score</div>
            <div class="progress-value" id="score">0</div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Correct</div>
            <div class="progress-value" id="correctCount">0</div>
        </div>
    </div>
    <div class="progress-bar-track">
        <div class="progress-bar-fill" id="progressBar" style="width: 10%"></div>
    </div>
</div>

<div class="error-card">
    <div style="text-align: center;">
        <div class="timer-circle" id="timerCircle">15</div>
        <span id="difficultyBadge" class="difficulty-badge difficulty-easy">Easy</span>
    </div>

    <div class="error-display" id="errorDisplay"></div>

    <h3 style="color: var(--neon-cyan); margin: 2rem 0 1rem;">What's causing this error?</h3>
    <div class="causes-grid" id="causesGrid"></div>

    <button class="next-btn" id="nextBtn" onclick="nextError()" disabled>Next Error →</button>
</div>
</div>

<!-- Side Popup -->
<div class="side-popup" id="sidePopup">
    <button class="popup-close" onclick="closeSidePopup()">×</button>
    <div class="popup-header">
        <span class="popup-icon" id="popupIcon">✓</span>
        <span class="popup-title" id="popupTitle">Correct!</span>
    </div>
    <div id="popupContent"></div>
</div>

<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon">🎯</div>
        <h2 class="result-title">Debug Master!</h2>
        <p class="result-message" id="resultMessage"></p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="result-stat-value" id="finalScore">0</div>
                <div class="result-stat-label">Final Score</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalCorrect">0</div>
                <div class="result-stat-label">Correct Answers</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalAccuracy">0%</div>
                <div class="result-stat-label">Accuracy</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="avgTime">0s</div>
                <div class="result-stat-label">Avg Time</div>
            </div>
        </div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Try Again</button>
            <button class="btn btn-secondary" onclick="window.location.href='../index.php'">🏠 Dashboard</button>
        </div>
    </div>
</div>

<script src="../js/main.js"></script>
<?php
$sql = "SELECT * FROM game_challenges
                  INNER JOIN games 
                  ON games.game_id = game_challenges.game_id
                  WHERE games.game_id = 12";
$result = mysqli_query($conn, $sql);
$errors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    if ($decoded) {
        $decoded['challenge_id'] = $row['id'];
        $errors[] = $decoded;
    }
}
?>
<script>
    const errors = <?php echo json_encode($errors); ?>;
</script>
<script src="../js/guess-error.js"></script>
<?php include_once("../includes/footer.php"); ?>