<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$company_name = $company_name ?? '';

// ── Get User Basic Info ─────────────────────────────────────────────────
$user_query = "SELECT user_name, profile_pic, user_email, mobile, city FROM user_tbl WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// ── Get User Stats (from game_sessions) ─────────────────────────────────
$stats_query = "
    SELECT 
        COALESCE(SUM(score), 0) AS total_score,
        COUNT(*) AS games_played,
        COALESCE(SUM(CASE WHEN result = 'WIN' THEN 1 ELSE 0 END), 0) AS total_wins,
        COALESCE(ROUND(AVG(accuracy_percentage), 1), 0) AS avg_accuracy,
        COALESCE(MAX(score), 0) AS best_score,
        COALESCE(MAX(combo_max), 0) AS best_combo
    FROM game_sessions 
    WHERE user_id = ? AND completed = 1";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$win_rate = $stats['games_played'] > 0
    ? round(($stats['total_wins'] / $stats['games_played']) * 100)
    : 0;

// ── Get Recent Games ───────────────────────────────────────────────────
$recent_query = "
    SELECT 
        g.name AS game_name,
        g.icon AS game_icon,
        gs.language,
        gs.score,
        gs.result,
        gs.accuracy_percentage,
        gs.time_taken_seconds,
        gs.completed_at
    FROM game_sessions gs
    JOIN games g ON gs.game_id = g.game_id
    WHERE gs.user_id = ? AND gs.completed = 1
    ORDER BY gs.completed_at DESC
    LIMIT 10";
$stmt = $conn->prepare($recent_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_games = $stmt->get_result();

// ── Get Language-wise Stats ────────────────────────────────────────────
$lang_query = "
    SELECT 
        language,
        COUNT(*) AS games_played,
        SUM(score) AS total_score,
        ROUND(AVG(accuracy_percentage), 1) AS avg_accuracy,
        SUM(CASE WHEN result = 'WIN' THEN 1 ELSE 0 END) AS wins
    FROM game_sessions
    WHERE user_id = ? AND completed = 1 AND language IS NOT NULL
    GROUP BY language
    ORDER BY total_score DESC";
$stmt = $conn->prepare($lang_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$lang_stats = $stmt->get_result();

// ── Get Achievements (from user_achievements) ─────────────────────────
$achievements_query = "
    SELECT a.*, ua.unlocked_at 
    FROM achievements a
    LEFT JOIN user_achievements ua ON a.achievement_id = ua.achievement_id AND ua.user_id = ?
    WHERE a.is_active = 1
    ORDER BY ua.unlocked_at DESC, a.name";
$stmt = $conn->prepare($achievements_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$achievements = $stmt->get_result();

// Calculate level (simple formula: level = floor(total_score/1000) + 1)
$level = floor($stats['total_score'] / 1000) + 1;
$next_level_xp = $level * 1000;
$current_level_xp = $stats['total_score'];
$xp_progress = min(100, round(($current_level_xp % 1000) / 10)); // percentage to next level
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo $company_name; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Oxanium:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">

</head>

<body>


    <?php include 'header.php'; ?>
    <div class="page">

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['user_name'], 0, 2)) ?>
            </div>
            <div class="profile-info">
                <h1 class="profile-name"><?= htmlspecialchars($user['user_name']) ?></h1>
                <div class="profile-email"><?= htmlspecialchars($user['user_email']) ?></div>
                <div class="profile-badge">
                    <span>⭐ Level <?= $level ?></span>
                    <span>•</span>
                    <span><?= number_format($stats['total_score']) ?> Total Score</span>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Games Played</div>
                <div class="value"><?= number_format($stats['games_played']) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Win Rate</div>
                <div class="value"><?= $win_rate ?>%</div>
                <div class="sub"><?= $stats['total_wins'] ?> wins</div>
            </div>
            <div class="stat-card">
                <div class="label">Best Score</div>
                <div class="value"><?= number_format($stats['best_score']) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Avg Accuracy</div>
                <div class="value"><?= $stats['avg_accuracy'] ?>%</div>
            </div>
        </div>

        <!-- XP Progress -->
        <div class="xp-container">
            <div class="xp-info">
                <span class="xp-level">Level <?= $level ?></span>
                <span class="xp-numbers">
                    <?= number_format($current_level_xp) ?> / <?= number_format($next_level_xp) ?> XP
                </span>
            </div>
            <div class="xp-bar-bg">
                <div class="xp-bar-fill" style="width: <?= $xp_progress ?>%"></div>
            </div>
        </div>

        <!-- Recent Games -->
        <h2 class="section-title">📋 Recent Games</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Game</th>
                        <!-- <th>Language</th> -->
                        <th>Score</th>
                        <th>Accuracy</th>
                        <th>Time</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_games->num_rows > 0): ?>
                        <?php while ($game = $recent_games->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="game-icon"><?= htmlspecialchars($game['game_icon'] ?? '🎮') ?></span>
                                    <?= htmlspecialchars($game['game_name']) ?>
                                </td>
                                <!-- <td>
                                    <?php if ($game['language']): ?>
                                        <span class="language-tag"><?= htmlspecialchars($game['language']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--muted);">—</span>
                                    <?php endif; ?>
                                </td> -->
                                <td><strong>+<?= number_format($game['score']) ?></strong></td>
                                <td><?= $game['accuracy_percentage'] ?>%</td>
                                <td>
                                    <?php
                                    $minutes = floor($game['time_taken_seconds'] / 60);
                                    $seconds = $game['time_taken_seconds'] % 60;
                                    echo $minutes ? "{$minutes}m {$seconds}s" : "{$seconds}s";
                                    ?>
                                </td>
                                <td>
                                    <span class="<?= $game['result'] === 'WIN' ? 'result-win' : 'result-loss' ?>">
                                        <?= $game['result'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--muted); padding: 40px;">
                                No games played yet. <a href="games.php" style="color: var(--accent2);">Start playing!</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Language Stats -->
        <?php if ($lang_stats->num_rows > 0): ?>
            <h2 class="section-title">📊 Performance</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <!-- <th>Language</th> -->
                            <th>Games</th>
                            <th>Total Score</th>
                            <th>Avg Accuracy</th>
                            <th>Wins</th>
                            <th>Win Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($lang = $lang_stats->fetch_assoc()):
                            $lang_win_rate = $lang['games_played'] > 0
                                ? round(($lang['wins'] / $lang['games_played']) * 100)
                                : 0;
                            ?>
                            <tr>
                                <!-- <td><span class="language-tag"><?= htmlspecialchars($lang['language']) ?></span></td> -->
                                <td><?= $lang['games_played'] ?></td>
                                <td><?= number_format($lang['total_score']) ?></td>
                                <td><?= $lang['avg_accuracy'] ?>%</td>
                                <td><?= $lang['wins'] ?></td>
                                <td><?= $lang_win_rate ?>%</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Achievements -->
        <h2 class="section-title">🏅 Achievements</h2>
        <div class="achievements-grid">
            <?php
            $has_achievements = false;
            while ($achievement = $achievements->fetch_assoc()):
                $has_achievements = true;
                $is_unlocked = !is_null($achievement['unlocked_at']);
                ?>
                <div class="achievement-card <?= $is_unlocked ? 'unlocked' : 'locked' ?>">
                    <div class="achievement-icon"><?= htmlspecialchars($achievement['icon'] ?? '🏆') ?></div>
                    <div class="achievement-info">
                        <div class="achievement-name"><?= htmlspecialchars($achievement['name']) ?></div>
                        <div class="achievement-desc"><?= htmlspecialchars($achievement['description']) ?></div>
                        <div class="achievement-reward">+<?= $achievement['xp_reward'] ?> XP</div>
                        <?php if ($is_unlocked): ?>
                            <div class="achievement-date">
                                Unlocked: <?= date('M j, Y', strtotime($achievement['unlocked_at'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if (!$has_achievements): ?>
                <div style="grid-column: 1/-1; text-align: center; color: var(--muted); padding: 40px;">
                    No achievements yet. Keep playing to unlock them!
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.getElementById("profile").classList.add("active");
    </script>
</body>

</html>