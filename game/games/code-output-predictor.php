<?php
session_start();
include("../../connection.php");

define('CURRENT_GAME_ID', 2);  // ← change per game
include_once("../includes/game_access.php");
enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php
$pageTitle = "Code Output Predictor";
$preloaderText = "Loading Code Output Predictor...";
$extraCss = '<link rel="stylesheet" href="../css/code-output-predictor.css">';
include_once("../includes/header.php");
?>
<!-- Game Header -->
<div class="challenge-header" style="text-align: center; margin-bottom: 2rem;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">🔮</div>
    <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Code Output Predictor</h1>
    <p style="color: var(--text-secondary);">Predict what the code will output. Test your code reading and
        execution skills!</p>
</div>

<!-- Language & Progress Card -->
<div class="challenge-card" style="
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:1.2rem;
    flex-wrap:wrap;
    margin-bottom:1.2rem;
    padding:1rem 1.2rem;
    min-height:auto;
">


    <!-- Select Language -->
    <div style="display:flex; align-items:center; gap:1rem;">
        <h3 style="margin:0;">Select Language</h3>

        <select id="languageDropdown" style="
            background:#1a1f3a;
            color:#fff;
            border:1px solid rgba(255,255,255,0.2);
            border-radius:8px;
            padding:0.6rem 1rem;
            font-size:1rem;
            cursor:pointer;
        ">
            <option value="python" selected>🐍 Python</option>
            <option value="php">🐘 PHP</option>
            <option value="java">☕ Java</option>
        </select>
    </div>

    <!-- Progress Info -->
    <div class="progress-info" style="gap:2rem;">
        <div>
            <div class="progress-label">Challenge</div>
            <div class="progress-value">
                <span id="currentChallenge">1</span> of
                <span id="totalChallenges">5</span>
            </div>
        </div>

        <div>
            <div class="progress-label">Time</div>
            <div class="progress-value timer" id="timer">1:00</div>
        </div>

        <div>
            <div class="progress-label">Score</div>
            <div class="progress-value score-glow" id="score">0</div>
        </div>
    </div>
</div>
<!-- Challenge Card -->
<div class="challenge-card" id="challengeCard" style="
    padding:1.5rem;
    max-width:900px;
    margin:0 auto;">
    <h3 style="margin-bottom:0.8rem;">What will this code output?</h3>

    <div class="code-block" id="codeBlock" style="
    max-height:220px;
    overflow:auto;
    padding:1rem;
    font-size:0.95rem;
">

        <!-- Code will be inserted here -->
    </div>


    <div style="margin-top:1rem;">
        <h4 style="margin-bottom: 1rem;">Select the correct output:</h4>
        <div class="answer-options" id="answerOptions">
            <!-- Options will be inserted here -->
        </div>
    </div>

    <div style="margin-top:1.2rem; display:flex; justify-content:space-between;">

        <button class="btn btn-secondary" id="skipBtn">Skip Challenge</button>
        <button class="btn btn-primary" id="nextBtn" style="display: none;">Next Challenge →</button>
    </div>
</div>
</div>
<div class="progress-bar-container">
    <div class="progress-bar" id="progressBar" style="width: 0%"></div>
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
                <div class="result-stat-value" id="correctPredictions">0</div>
                <div class="result-stat-label">Correct Predictions</div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: center;">
            <button class="btn btn-primary" onclick="location.reload()">Play Again</button>
            <button class="btn btn-secondary" onclick="window.location.href='../games.php'">Back to Games</button>
        </div>
    </div>
</div>

<!-- Feedback Popup -->
<div class="feedback-popup" id="feedbackPopup">
    <div class="popup-header">
        <div class="popup-feedback-title" id="feedbackTitle">
            <span class="feedback-icon" id="feedbackIcon">✓</span>
            <span class="feedback-text" id="feedbackText">Correct!</span>
            <span class="feedback-points" id="feedbackPoints" style="display: none;">+150 points</span>
        </div>
        <button class="popup-close" onclick="closeFeedbackPopup()">×</button>
    </div>
    <p class="popup-explanation" id="explanationText"></p>
</div>

<!-- Hidden element for backward compatibility -->
<div id="explanationBox" style="display: none !important;"></div>

<script src="../js/main.js"></script>
<?php
$query = "SELECT * FROM game_challenges
                  INNER JOIN games 
                  ON games.game_id = game_challenges.game_id
                  WHERE games.game_id = 2";

$result = mysqli_query($conn, $query);
$challenges_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $lang => $data) {
            if (!isset($challenges_data[$lang])) {
                $challenges_data[$lang] = [];
            }
            // Add ID to each challenge in the language array
            foreach ($data as &$item) {
                $item['challenge_id'] = $row['id'];
            }
            $challenges_data[$lang] = array_merge($challenges_data[$lang], $data);
        }
    }
}
?>
<script>
    const challenges = <?php echo json_encode($challenges_data); ?>;
</script>
<script src="../js/code-output-predictor.js"></script>

<?php include_once("../includes/footer.php"); ?>