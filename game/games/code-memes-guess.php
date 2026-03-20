<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 11);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "Code Meme Guess";
$preloaderText = "Loading Memes...";
$extraCss = '<link rel="stylesheet" href="../css/code-memes-guess.css">';
include_once("../includes/header.php");
?>
<div class="game-page-header">
    <h1 class="game-page-title">🤣 Code Meme Guess</h1>
    <p class="game-page-description">Guess the coding concept from funny dev memes! Perfect for brain breaks!
    </p>
</div>

<div class="game-progress-bar">
    <div class="progress-info">
        <div class="progress-stat">
            <div class="progress-label">Meme</div>
            <div class="progress-value"><span id="currentMeme">1</span>/10</div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Score</div>
            <div class="progress-value" id="score">0</div>
        </div>
        <div class="progress-stat">
            <div class="progress-label">Streak</div>
            <div class="progress-value" id="streak">0</div>
        </div>
    </div>
    <div class="progress-bar-bg">
        <div class="progress-bar" id="progressBar"></div>
    </div>
</div>

<div class="meme-card">
    <div class="timer-bar">
        <div class="timer-fill" id="timerFill" style="width: 100%;"></div>
    </div>

    <div class="meme-container" id="memeContainer">
        <!-- Meme will be loaded here -->
    </div>

    <div class="options-container" id="optionsContainer">
        <!-- Options will be loaded here -->
    </div>
</div>
</div>

<!-- Result Modal -->
<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon">🎉</div>
        <h2 class="result-title">Game Complete!</h2>
        <p class="result-message" id="resultMessage"></p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="result-stat-value" id="finalScore">0</div>
                <div class="result-stat-label">Final Score</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="correctAnswers">0/10</div>
                <div class="result-stat-label">Correct</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="bestStreak">0</div>
                <div class="result-stat-label">Best Streak</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="accuracy">0%</div>
                <div class="result-stat-label">Accuracy</div>
            </div>
        </div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Play Again</button>
            <button class="btn btn-secondary" onclick="window.location.href='../index.php'">🏠 Dashboard</button>
        </div>
    </div>
</div>

<!-- Feedback Popup -->
<div class="feedback-popup" id="feedbackPopup">
    <div class="popup-header">
        <div class="popup-feedback-title" id="feedbackTitle">
            <span class="feedback-icon" id="feedbackIcon">✓</span>
            <span class="feedback-text" id="feedbackText">Correct!</span>
            <span class="feedback-points" id="feedbackPoints" style="display: none;">+200 points</span>
        </div>
        <button class="popup-close" onclick="closeFeedbackPopup()">×</button>
    </div>
    <p class="popup-explanation" id="explanationText"></p>
</div>

<script src="../js/main.js"></script>
<?php
$query = "SELECT * FROM game_challenges
                  INNER JOIN games 
                  ON games.game_id = game_challenges.game_id
                  WHERE games.game_id = 11";

$result = mysqli_query($conn, $query);

$bugs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    $decoded['challenge_id'] = $row['id'];
    $bugs[] = $decoded;
}
?>
<script>
    const memes = <?php echo json_encode($bugs); ?>;
</script>
<script src="../js/code-memes-guess.js"></script>
<?php include_once("../includes/footer.php"); ?>