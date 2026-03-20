<?php

// ── Access rules ─────────────────────────────────────────────────────────────
// Beginner enrollments  → Easy  games only  (difficulty = 'Easy')
// Intermediate/Advanced → All games

/**
 * Returns the highest course level the user has paid for.
 * Possible return values: 'none' | 'beginner' | 'intermediate' | 'advanced'
 */
function getUserEnrollmentLevel(mysqli $conn, int $user_id): string
{
    $sql = "
        SELECT DISTINCT c.course_level
        FROM   enrollments_tbl e
        JOIN   course_tbl      c ON e.course_id        = c.course_id
        JOIN   user_payment_tbl p ON e.user_payment_id = p.user_payment_id
        WHERE  e.user_id        = ?
          AND  p.payment_status = 1
          AND  c.course_status  = 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $levels = [];
    while ($row = $res->fetch_assoc()) {
        $levels[] = $row['course_level'];
    }

    if (in_array('advanced', $levels))
        return 'advanced';
    if (in_array('intermediate', $levels))
        return 'intermediate';
    if (in_array('beginner', $levels))
        return 'beginner';
    return 'none';
}

/**
 * Returns the difficulty of a game ('Easy' | 'Medium' | 'Hard' | 'Expert').
 */
function getGameDifficulty(mysqli $conn, int $game_id): string
{
    $stmt = $conn->prepare("SELECT difficulty FROM games WHERE game_id = ? AND is_active = 1");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['difficulty'] ?? 'Medium';
}

/**
 * Returns true if the user may play the given game.
 */
function canAccessGame(mysqli $conn, int $user_id, int $game_id): bool
{
    $level = getUserEnrollmentLevel($conn, $user_id);
    $difficulty = getGameDifficulty($conn, $game_id);

    if ($level === 'none')
        return false;
    if ($level === 'beginner')
        return ($difficulty === 'Easy');
    // intermediate or advanced → all games
    return true;
}

/**
 * Call this at the top of every game page.
 * If the user lacks access the function outputs a full-page locked overlay
 * and exits; otherwise it returns silently.
 */
function enforceGameAccess(mysqli $conn, int $user_id, int $game_id): void
{
    if (canAccessGame($conn, $user_id, $game_id))
        return;

    $level = getUserEnrollmentLevel($conn, $user_id);
    $difficulty = getGameDifficulty($conn, $game_id);

    // Determine what the user needs
    if ($level === 'none') {
        $msg = "You need to purchase a course to play games.";
        $badge = "No Enrollment";
        $action = "Browse Courses";
        $href = "../../courses.php";
    } else {
        // beginner trying an intermediate/hard game
        $msg = "This game requires an Intermediate or Advanced course enrollment.";
        $badge = "Beginner Plan";
        $action = "Upgrade Enrollment";
        $href = "../../courses.php";
    }

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Access Restricted</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #07070f;
    font-family: 'Space Grotesk', sans-serif;
    overflow: hidden;
  }

  /* Ambient grid */
  body::before {
    content: '';
    position: fixed; inset: 0;
    background-image:
      linear-gradient(rgba(255,60,100,.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,60,100,.03) 1px, transparent 1px);
    background-size: 40px 40px;
  }

  .card {
    position: relative;
    background: #0f0f1e;
    border: 1px solid rgba(255,60,100,.25);
    border-radius: 24px;
    padding: 56px 48px 48px;
    max-width: 520px;
    width: 90%;
    text-align: center;
    box-shadow: 0 0 80px rgba(255,60,100,.08), 0 24px 48px rgba(0,0,0,.6);
    animation: slideUp .5s cubic-bezier(.16,1,.3,1) both;
  }

  @keyframes slideUp {
    from { opacity:0; transform:translateY(32px); }
    to   { opacity:1; transform:translateY(0); }
  }

  .lock-ring {
    width: 100px; height: 100px;
    border-radius: 50%;
    background: rgba(255,60,100,.08);
    border: 2px solid rgba(255,60,100,.3);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 28px;
    font-size: 40px;
    animation: pulse 2.4s ease-in-out infinite;
  }

  @keyframes pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(255,60,100,.25); }
    50%      { box-shadow: 0 0 0 18px rgba(255,60,100,0); }
  }

  .badge {
    display: inline-block;
    background: rgba(255,60,100,.12);
    border: 1px solid rgba(255,60,100,.35);
    color: #ff3c64;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    padding: 4px 14px;
    border-radius: 99px;
    margin-bottom: 18px;
  }

  h1 {
    color: #fff;
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 14px;
    line-height: 1.2;
  }

  p {
    color: rgba(255,255,255,.55);
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 36px;
  }

  .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

  .btn-primary {
    padding: 13px 28px;
    background: linear-gradient(135deg,#ff3c64,#d42050);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: transform .15s, box-shadow .15s;
    box-shadow: 0 4px 20px rgba(255,60,100,.35);
  }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(255,60,100,.5); }

  .btn-ghost {
    padding: 13px 28px;
    background: transparent;
    color: rgba(255,255,255,.6);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 12px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: border-color .15s, color .15s;
  }
  .btn-ghost:hover { border-color: rgba(255,255,255,.4); color: #fff; }

  .divider {
    margin: 40px 0 24px;
    border: none;
    border-top: 1px solid rgba(255,255,255,.07);
  }

  .plan-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    text-align: left;
  }

  .plan-item {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 10px;
    padding: 12px 14px;
    display: flex; gap: 10px; align-items: center;
  }

  .plan-item.unlocked { border-color: rgba(100,220,100,.2); }
  .plan-item.locked   { opacity: .45; }

  .plan-item .icon { font-size: 18px; }
  .plan-item .label { font-size: 12px; color: rgba(255,255,255,.55); }
  .plan-item .name  { font-size: 13px; font-weight: 600; color: #fff; }
</style>
</head>
<body>
<div class="card">
  <div class="lock-ring">🔒</div>
  <span class="badge">{$badge}</span>
  <h1>Game Locked</h1>
  <p>{$msg}</p>

  <div class="actions">
    <a href="{$href}" class="btn-primary">🚀 {$action}</a>
    <a href="javascript:history.back()" class="btn-ghost">← Go Back</a>
  </div>

  <hr class="divider">

  <div class="plan-list">
    <div class="plan-item unlocked">
      <span class="icon">🟢</span>
      <div><div class="name">Beginner</div><div class="label">Easy games only</div></div>
    </div>
    <div class="plan-item locked">
      <span class="icon">🔴</span>
      <div><div class="name">Intermediate</div><div class="label">All games unlocked</div></div>
    </div>
    <div class="plan-item locked">
      <span class="icon">🔴</span>
      <div><div class="name">Advanced</div><div class="label">All games unlocked</div></div>
    </div>
    <div class="plan-item locked">
      <span class="icon">🔒</span>
      <div><div class="name">This game</div><div class="label">{$difficulty} difficulty</div></div>
    </div>
  </div>
</div>
</body>
</html>
HTML;
    exit;
}