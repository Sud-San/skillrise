<?php
/**
 * leaderboard.php
 * Place at: game/leaderboard.php
 * Replaces the existing empty leaderboard.php
 */
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    $_SESSION['prelogin_redirect'] = $_SERVER['REQUEST_URI'];
    header("Location: ../login.php");
    exit;
}

$current_uid = (int) $_SESSION['user_id'];

// ── Query params ──────────────────────────────────────────────────────────────
$period = in_array($_GET['period'] ?? '', ['weekly', 'all_time']) ? $_GET['period'] : 'all_time';
$game_filter = (int) ($_GET['game_id'] ?? 0);

// ── Time filter ───────────────────────────────────────────────────────────────
$time_where = $period === 'weekly'
    ? "AND gs.started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    : "";

// ── Game filter ───────────────────────────────────────────────────────────────
$game_where = $game_filter > 0
    ? "AND gs.game_id = " . $game_filter
    : "";

// ── Main leaderboard query ────────────────────────────────────────────────────
$lb_sql = "
    SELECT
        u.user_id,
        u.user_name,
        u.profile_pic,
        SUM(gs.score)                                         AS total_score,
        COUNT(*)                                              AS games_played,
        ROUND(AVG(gs.accuracy_percentage), 1)                AS avg_accuracy,
        SUM(CASE WHEN gs.result = 'WIN'  THEN 1 ELSE 0 END)  AS wins,
        MAX(gs.score)                                         AS best_score
    FROM   game_sessions gs
    JOIN   user_tbl      u  ON gs.user_id = u.user_id
    WHERE  gs.completed = 1
           $time_where
           $game_where
    GROUP  BY gs.user_id
    ORDER  BY total_score DESC
    LIMIT  100
";
$lb_result = mysqli_query($conn, $lb_sql);
$entries = [];
$rank = 1;
$me = null;
while ($row = mysqli_fetch_assoc($lb_result)) {
    $row['rank'] = $rank++;
    $entries[] = $row;
    if ((int) $row['user_id'] === $current_uid)
        $me = $row;
}

// ── All active games for the filter dropdown ──────────────────────────────────
$gq = mysqli_query($conn, "SELECT game_id, name, icon, difficulty FROM games WHERE is_active = 1 ORDER BY name");
$all_games = [];
while ($g = mysqli_fetch_assoc($gq))
    $all_games[] = $g;

// ── Site name ─────────────────────────────────────────────────────────────────
$company_name = $company_name ?? 'Codezy';

// ── Medal helper ─────────────────────────────────────────────────────────────
function medal(int $rank): string
{
    if ($rank === 1)
        return '🥇';
    if ($rank === 2)
        return '🥈';
    if ($rank === 3)
        return '🥉';
    return "#$rank";
}
function rankClass(int $rank): string
{
    if ($rank === 1)
        return 'gold';
    if ($rank === 2)
        return 'silver';
    if ($rank === 3)
        return 'bronze';
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($company_name) ?> – Leaderboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Oxanium:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/leaderboard.css">
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">

</head>

<body>

    <!-- ── Navbar ───────────────────────────────────────────────────────────── -->
    <?php include 'header.php'; ?>

    <!-- ── Page content ─────────────────────────────────────────────────────── -->
    <div class="page">

        <!-- Hero -->
        <div class="lb-hero">
            <span class="icon">🏆</span>
            <h1>Leaderboard</h1>
            <p>Top coders ranked by score. Compete, rise, and claim your spot.</p>
        </div>

        <!-- My rank card (only if user has played) -->
        <?php if ($me): ?>
            <div class="my-rank-card">
                <div class="rank-num">#<?= $me['rank'] ?></div>
                <div class="info">
                    <strong>Your Current Rank</strong>
                    <span><?= $period === 'weekly' ? 'This Week' : 'All Time' ?><?= $game_filter ? ' · ' . htmlspecialchars($all_games[array_search($game_filter, array_column($all_games, 'game_id'))]['name'] ?? '') : '' ?></span>
                </div>
                <div>
                    <div class="score-big"><?= number_format($me['total_score']) ?></div>
                    <div class="score-label">pts · <?= $me['games_played'] ?> games</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Controls -->
        <div class="controls">
            <!-- Period tabs -->
            <div class="period-tabs">
                <a href="?period=all_time&game_id=<?= $game_filter ?>"
                    class="<?= $period === 'all_time' ? 'active' : '' ?>">⏳ All Time</a>
                <a href="?period=weekly&game_id=<?= $game_filter ?>"
                    class="<?= $period === 'weekly' ? 'active' : '' ?>">📅 This Week</a>
            </div>

            <!-- Game filter -->
            <form method="GET" id="gameForm" style="display:flex;gap:8px;align-items:center;">
                <input type="hidden" name="period" value="<?= htmlspecialchars($period) ?>">
                <select name="game_id" class="game-select" onchange="document.getElementById('gameForm').submit()">
                    <option value="0" <?= !$game_filter ? 'selected' : '' ?>>🎮 All Games</option>
                    <?php foreach ($all_games as $g): ?>
                        <option value="<?= $g['game_id'] ?>" <?= $game_filter === (int) $g['game_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['icon'] . ' ' . $g['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <div class="total-pill"><strong><?= count($entries) ?></strong> players ranked</div>
        </div>

        <?php if (count($entries) > 0): ?>

            <!-- ── Podium (top 3) ───────────────────────────────────────────────── -->
            <?php $top3 = array_slice($entries, 0, 3); ?>
            <?php if (count($top3) === 3): ?>
                <div class="podium">
                    <?php foreach ($top3 as $p):
                        $cls = rankClass($p['rank']);
                        $initials = mb_strtoupper(mb_substr($p['user_name'], 0, 1));
                        ?>
                        <div class="podium-slot <?= $cls ?>">
                            <div class="avatar-wrap">
                                <div class="avatar"><?= $initials ?></div>
                                <span class="medal-badge"><?= medal($p['rank']) ?></span>
                            </div>
                            <div class="p-name"><?= htmlspecialchars($p['user_name']) ?></div>
                            <div class="p-score"><?= number_format($p['total_score']) ?></div>
                            <div class="podium-block"><?= $p['rank'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ── Full table ───────────────────────────────────────────────────── -->
            <div class="lb-table-wrap">
                <table class="lb-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Player</th>
                            <th>Score</th>
                            <th class="center">Accuracy</th>
                            <th class="center">Games</th>
                            <th class="center">Wins</th>
                            <th>Best</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $e):
                            $isMe = (int) $e['user_id'] === $current_uid;
                            $rClass = rankClass($e['rank']);
                            $initials = mb_strtoupper(mb_substr($e['user_name'], 0, 1));
                            $winRate = $e['games_played'] > 0
                                ? round($e['wins'] / $e['games_played'] * 100) : 0;
                            ?>
                            <tr class="<?= $isMe ? 'me' : '' ?>">
                                <td class="rank-col <?= $rClass ?>"><?= medal($e['rank']) ?></td>
                                <td>
                                    <div class="user-cell">
                                        <div class="av"><?= $initials ?></div>
                                        <span class="uname"><?= htmlspecialchars($e['user_name']) ?></span>
                                        <?php if ($isMe): ?><span class="you">YOU</span><?php endif; ?>
                                    </div>
                                </td>
                                <td><span class="score-val"><?= number_format($e['total_score']) ?></span></td>
                                <td class="center">
                                    <div class="acc-val"><?= $e['avg_accuracy'] ?>%</div>
                                    <div class="acc-bar">
                                        <div class="acc-bar-fill" style="width:<?= min(100, $e['avg_accuracy']) ?>%"></div>
                                    </div>
                                </td>
                                <td class="center games-played"><?= $e['games_played'] ?></td>
                                <td class="center">
                                    <span class="win-rate"
                                        style="color:<?= $winRate >= 60 ? '#4ade80' : ($winRate >= 40 ? '#fbbf24' : 'var(--danger)') ?>">
                                        <?= $winRate ?>%
                                    </span>
                                </td>
                                <td><span class="best-val"><?= number_format($e['best_score']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <!-- Empty state -->
            <div class="lb-table-wrap">
                <div class="empty-state">
                    <span class="icon">🎮</span>
                    <h3>No scores yet</h3>
                    <p>
                        <?= $period === 'weekly' ? 'No games played this week.' : 'No games played yet.' ?>
                        Be the first to set a score!
                    </p>
                </div>
            </div>
        <?php endif; ?>

    </div><!-- /page -->

    <div class="lb-footer">
        © <?= date('Y') ?> <?= htmlspecialchars($company_name) ?> &nbsp;·&nbsp; Scores update in real-time after each
        game.
    </div>
    <script>
        document.getElementById("leaderboard").classList.add("active");
    </script>
</body>

</html>