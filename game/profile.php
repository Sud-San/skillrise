<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$company_name = $company_name ?? 'Codezy';

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
    <style>
        /* ── Copy all CSS from leaderboard.php for consistency ── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #07070f;
            --surface: #0f0f1e;
            --surface2: #161628;
            --border: rgba(255, 255, 255, .07);
            --gold: #ffd700;
            --silver: #c0c0c0;
            --bronze: #cd7f32;
            --accent: #7c5cfc;
            --accent2: #00d4ff;
            --success: #4ade80;
            --danger: #ff3c64;
            --warning: #fbbf24;
            --text: #e8e8f0;
            --muted: rgba(232, 232, 240, .45);
            --font-head: 'Oxanium', sans-serif;
            --font-body: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font-body);
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background-image:
                radial-gradient(ellipse 80% 50% at 10% -10%, rgba(124, 92, 252, .09) 0%, transparent 60%),
                radial-gradient(ellipse 50% 60% at 90% 110%, rgba(0, 212, 255, .06) 0%, transparent 55%),
                linear-gradient(rgba(124, 92, 252, .025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(124, 92, 252, .025) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 48px 48px, 48px 48px;
        }


        /* ── Logout button ─────────────────────────────────────── */
        .logout-btn {
            padding: 9px 20px;
            background: linear-gradient(135deg, var(--danger), #b91c1c);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: var(--font-body);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 4px 16px rgba(255, 60, 100, .3);
        }

        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(255, 60, 100, .45);
        }

        .nav-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* ── Page wrapper ─────────────────────────────────────── */
        .page {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px 60px;
        }

        /* ── Profile Header ───────────────────────────────────── */
        .profile-header {
            background: linear-gradient(135deg, rgba(124, 92, 252, .15), rgba(0, 212, 255, .1));
            border: 1px solid rgba(124, 92, 252, .3);
            border-radius: 24px;
            padding: 40px;
            margin: 40px 0;
            display: flex;
            gap: 40px;
            align-items: center;
            flex-wrap: wrap;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 700;
            color: #fff;
            border: 4px solid rgba(255, 255, 255, .2);
            box-shadow: 0 0 40px rgba(124, 92, 252, .4);
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-family: var(--font-head);
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fff 30%, var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .profile-email {
            color: var(--muted);
            font-size: 16px;
            margin-bottom: 20px;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(124, 92, 252, .2);
            border: 1px solid rgba(124, 92, 252, .4);
            border-radius: 99px;
            padding: 8px 16px;
            font-size: 14px;
            color: var(--accent2);
        }

        /* ── Stats Grid ───────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 32px 0;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
        }

        .stat-card .label {
            color: var(--muted);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 8px;
        }

        .stat-card .value {
            font-family: var(--font-head);
            font-size: 32px;
            font-weight: 700;
            color: var(--accent2);
        }

        .stat-card .sub {
            color: var(--muted);
            font-size: 12px;
            margin-top: 4px;
        }

        /* ── XP Bar ───────────────────────────────────────────── */
        .xp-container {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            margin: 20px 0;
        }

        .xp-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .xp-level {
            font-family: var(--font-head);
            font-weight: 700;
            color: var(--accent);
        }

        .xp-numbers {
            color: var(--muted);
            font-size: 14px;
        }

        .xp-bar-bg {
            width: 100%;
            height: 12px;
            background: var(--surface2);
            border-radius: 99px;
            overflow: hidden;
        }

        .xp-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--accent2));
            border-radius: 99px;
            transition: width .3s ease;
        }

        /* ── Tables ───────────────────────────────────────────── */
        .section-title {
            font-family: var(--font-head);
            font-size: 24px;
            font-weight: 700;
            margin: 40px 0 20px;
            color: #fff;
        }

        .table-container {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 16px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            background: var(--surface2);
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .game-icon {
            font-size: 20px;
            margin-right: 8px;
        }

        .language-tag {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(124, 92, 252, .2);
            border: 1px solid rgba(124, 92, 252, .4);
            border-radius: 6px;
            font-size: 12px;
            color: var(--accent2);
        }

        .result-win {
            color: var(--success);
            font-weight: 600;
        }

        .result-loss {
            color: var(--danger);
            font-weight: 600;
        }

        /* ── Achievements Grid ────────────────────────────────── */
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .achievement-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform .2s, border-color .2s;
        }

        .achievement-card.unlocked {
            background: linear-gradient(135deg, rgba(124, 92, 252, .1), rgba(0, 212, 255, .05));
            border-color: rgba(124, 92, 252, .3);
        }

        .achievement-card.locked {
            opacity: .5;
        }

        .achievement-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .achievement-info {
            flex: 1;
        }

        .achievement-name {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .achievement-desc {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 4px;
        }

        .achievement-reward {
            color: var(--accent2);
            font-size: 11px;
            font-weight: 600;
        }

        .achievement-date {
            color: var(--muted);
            font-size: 10px;
        }

        /* ── Responsive ───────────────────────────────────────── */
        @media (max-width: 768px) {
            .navbar {
                padding: 0 16px;
            }

            .nav-links {
                display: none;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
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
                        <th>Language</th>
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
                                <td>
                                    <?php if ($game['language']): ?>
                                        <span class="language-tag"><?= htmlspecialchars($game['language']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--muted);">—</span>
                                    <?php endif; ?>
                                </td>
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
            <h2 class="section-title">📊 Language Performance</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Language</th>
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
                                <td><span class="language-tag"><?= htmlspecialchars($lang['language']) ?></span></td>
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