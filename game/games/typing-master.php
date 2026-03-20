<?php
session_start();
include_once("../../connection.php");

// define('CURRENT_GAME_ID', 4);  // ← change per game
// include_once("../includes/game_access.php");
// enforceGameAccess($conn, $_SESSION['user_id'], CURRENT_GAME_ID);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$pageTitle = "Typing Master";
$preloaderText = "Loading Typing Master...";
$extraCss = '<link rel="stylesheet" href="../css/typing-master.css">';
include_once("../includes/header.php");
?>

<div class="game-container">
    <!-- Game Header -->
    <div class="game-page-header">
        <h1 class="game-page-title">⌨️ Typing Master</h1>
        <p class="game-page-description">
            Type code snippets as fast as possible! Improve your coding speed and accuracy.
        </p>
    </div>

    <!-- Language Filter Buttons -->
    <div class="language-filters">
        <button class="lang-btn" data-lang="PHP">PHP</button>
        <button class="lang-btn" data-lang="HTML">HTML</button>
        <button class="lang-btn" data-lang="Java">Java</button>
        <button class="lang-btn active" data-lang="Python">Python</button>
        <button class="lang-btn" data-lang="Unix">Unix</button>
    </div>

    <!-- Challenge Info -->
    <div class="challenge-info">
        <div class="challenge-number">Challenge 1 of 1</div>
        <div class="timer-display">
            <span>⏱</span>
            <span id="timer">59s</span>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-bar">
        <div class="progress-fill" id="progressFill" style="width: 0%"></div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value" id="wpm">0</div>
            <div class="stat-label">WPM</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" id="accuracy">100%</div>
            <div class="stat-label">Accuracy</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" id="errors">0</div>
            <div class="stat-label">Errors</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" id="score">🏆 0</div>
            <div class="stat-label">Trophy Score</div>
        </div>
    </div>

    <!-- Typing Area -->
    <div class="challenge-card">
        <span class="language-badge" id="languageBadge"></span>
        <h3 class="challenge-title" id="challengeTitle"></h3>
        <div class="typing-area" id="typingArea">
            <div class="typing-text" id="typingText"></div>
        </div>

        <div class="input-prompt">
            <span>⌨️</span>
            <span>Type the code above:</span>
        </div>

        <div style="text-align: center;">
            <button class="btn btn-primary" id="startBtn">Start Typing Test</button>
            <button class="btn btn-secondary" id="restartBtn" style="display: none;">Restart</button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div class="result-modal" id="resultModal">
    <div class="result-content">
        <div class="result-icon">⌨️</div>
        <h2 class="result-title">Typing Test Complete!</h2>
        <p class="result-message" id="resultMessage"></p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="result-stat-value" id="finalWpm">0</div>
                <div class="result-stat-label">Words Per Minute</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalAccuracy">0%</div>
                <div class="result-stat-label">Accuracy</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalErrors">0</div>
                <div class="result-stat-label">Total Errors</div>
            </div>
            <div class="result-stat">
                <div class="result-stat-value" id="finalScore">0</div>
                <div class="result-stat-label">Score</div>
            </div>
        </div>

        <div class="result-actions">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Try Again</button>
            <button class="btn btn-secondary" onclick="window.history.back()">🏠 Dashboard</button>
        </div>
    </div>
</div>

<?php
$sql = "SELECT * FROM game_challenges
                  INNER JOIN games 
                  ON games.game_id = game_challenges.game_id
                  WHERE games.game_id = 4";
$result = mysqli_query($conn, $sql);
$codeSnippets = [];
while ($row = mysqli_fetch_assoc($result)) {
    $decoded = json_decode($row['challenges'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $lang => $snippets) {
            if (!isset($codeSnippets[$lang])) {
                $codeSnippets[$lang] = [];
            }
            foreach ($snippets as $snippetText) {
                $codeSnippets[$lang][] = [
                    'text' => $snippetText,
                    'challenge_id' => $row['id']
                ];
            }
        }
    }
}
?>
<script>
    /**
     * Typing Master Game Logic - With Trophy Score System
     */
    const codeSnippets = <?php echo json_encode($codeSnippets); ?>;
</script>
<script src="../js/typing-master.js"></script>
<?php
include_once("../includes/footer.php");
?>