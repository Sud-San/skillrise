<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 9);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "Escape the Server Room";
$preloaderText = "Loading Server Room...";
$extraCss = '<link rel="stylesheet" href="../css/escape-room.css">';
include_once("../includes/header.php");
?>
<div class="game-page-header">
    <h1 class="game-page-title">🔐 Escape the Server Room</h1>
    <p class="game-page-description">Solve code puzzles before time runs out! Fix configs, decode logs, and
        escape!</p>
</div>


<div class="progress-tracker" id="progressTracker"></div>

<div class="game-progress-bar">
    <div class="compact-top-bar">

        <div class="compact-stats">
            <div class="stat-item">
                <div class="stat-label">Challenge</div>
                <div class="stat-value">
                    <span id="currentChallenge">1</span>/5
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-label">Score</div>
                <div class="stat-value" id="score">0</div>
            </div>

            <div class="stat-item">
                <div class="stat-label">Hints</div>
                <div class="stat-value" id="hintsUsed">0</div>
            </div>
        </div>

        <div class="small-timer" id="timerDisplay">10:00</div>
    </div>

    <div class="progress-bar-track">
        <div class="progress-bar-fill" id="progressBar" style="width: 0%"></div>
    </div>
</div>

<div class="challenge-container" id="challengeContainer"></div>
</div>

<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon" id="resultIcon">🎉</div>
        <h2 class="result-title" id="resultTitle">Escaped!</h2>
        <p class="result-message" id="resultMessage"></p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="result-stat-value" id="finalScore">0</div>
                <div class="result-stat-label">Final Score</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="timeUsed">0:00</div>
                <div class="result-stat-label">Time Used</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="challengesSolved">0/5</div>
                <div class="result-stat-label">Challenges Solved</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalHints">0</div>
                <div class="result-stat-label">Hints Used</div>
            </div>
        </div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Try Again</button>
            <button class="btn btn-secondary" onclick="window.location.href='../index.php'">🏠 Dashboard</button>
        </div>
    </div>
</div>

<!-- Side Popup Notification -->
<div class="side-popup" id="sidePopup">
    <button class="popup-close" onclick="closeSidePopup()">×</button>
    <div class="popup-content">
        <div class="popup-icon">⚠️</div>
        <div class="popup-message" id="popupMessage"></div>
    </div>
</div>

<?php
// $extraJs = '<script src="escape-room.js"></script>';
include_once("../includes/footer.php");
?>
<?php
$sql = "SELECT * FROM game_challenges
                    INNER JOIN games 
                    ON games.game_id = game_challenges.game_id
                    WHERE games.game_id = 9";
$result = mysqli_query($conn, $sql);
$challenges = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    if ($decoded) {
        $decoded['challenge_id'] = $row['id'];
        $challenges[] = $decoded;
    }
}
?>
<script>
    const challenges = <?php echo json_encode($challenges); ?>;
</script>
<script src="../js/escape-room.js"></script>
</body>

</html>