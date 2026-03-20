<?php
session_start();
include_once("../../connection.php");

define('CURRENT_GAME_ID', 1);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "Debugging Master";
$preloaderText = "Loading Debugging Master...";
$extraCss = '<link rel="stylesheet" href="../css/debugging-master.css">';
include_once("../includes/header.php");
?>
<!-- Game Header -->
<div class="challenge-header" style="text-align: center; margin-bottom: 2rem;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">🐛</div>
    <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Debugging Master</h1>
    <p style="color: var(--text-secondary);">Find and fix bugs in code snippets. Sharpen your debugging skills!
    </p>
</div>

<!-- Language Selector -->
<div class="challenge-card" style="margin-bottom: 2rem;">
    <div class="challenge-header">
        <h3>Select Language</h3>
        <div class="language-selector" id="languageSelector">
            <button class="lang-btn active" data-lang="python">Python</button>
            <button class="lang-btn" data-lang="php">PHP</button>
            <button class="lang-btn" data-lang="java">Java</button>
            <button class="lang-btn" data-lang="html">HTML</button>
            <button class="lang-btn" data-lang="unix">Unix</button>
        </div>
    </div>
</div>

<!-- Progress Bar -->
<div class="game-progress">
    <div class="progress-info">
        <div>
            <div class="progress-label">Challenge</div>
            <div class="progress-value"><span id="currentChallenge">1</span> of <span id="totalChallenges">5</span>
            </div>
        </div>
        <div>
            <div class="progress-label">Time</div>
            <div class="progress-value timer" id="timer">2:00</div>
        </div>
        <div>
            <div class="progress-label">Score</div>
            <div class="progress-value score-glow" id="score">0</div>
        </div>
    </div>
    <div class="progress-bar-container">
        <div class="progress-bar" id="progressBar" style="width: 0%"></div>
    </div>
</div>

<!-- Challenge Card -->
<div class="challenge-card" id="challengeCard">
    <h3 id="challengeTitle">Find the bug in this Python code:</h3>

    <div class="code-editor" id="codeEditor">
        <!-- Code will be inserted here -->
    </div>

    <div style="margin-top: 1.5rem;">
        <h4 style="margin-bottom: 1rem;">What's wrong with this code?</h4>
        <div class="answer-options" id="answerOptions">
            <!-- Options will be inserted here -->
        </div>
    </div>

    <div class="explanation-box" id="explanationBox">
        <h4 style="margin-bottom: 0.5rem; color: var(--accent-green);">✓ Correct!</h4>
        <p id="explanationText"></p>
    </div>

    <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" id="skipBtn">Skip Challenge</button>
        <button class="btn btn-primary" id="nextBtn" style="display: none;">Next Challenge →</button>
    </div>
</div>
</div>

<!-- Result Modal -->
<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon" id="resultIcon">🏆</div>
        <h2 id="resultTitle">Game Complete!</h2>
        <p id="resultMessage" style="color: var(--text-secondary); margin: 1rem 0;"></p>

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
                <div class="result-stat-label">Time Taken</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="bugsFound">0</div>
                <div class="result-stat-label">Bugs Found</div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: center;">
            <button class="btn btn-primary" onclick="location.reload()">Play Again</button>
            <button class="btn btn-secondary" onclick="window.location.href='../games.php'">Back to Games</button>
        </div>
    </div>
</div>


<script src="../js/main.js"></script>
<?php
$sql = "SELECT * FROM game_challenges
                  INNER JOIN games 
                  ON games.game_id = game_challenges.game_id
                  WHERE games.game_id = 1";
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
    const fetchedChallengesFromDB = <?php echo json_encode($challenges); ?>;
</script>
<?php
$extraJs = '<script src="../js/debugging-master.js"></script>';
include_once("../includes/footer.php");
?>