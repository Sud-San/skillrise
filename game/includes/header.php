<?php
/**
 * Global Game Header
 * Includes: session check, connection (relative), preloader, and navbar
 */

// Title and Preloader defaults
if (!isset($pageTitle))
    $pageTitle = "Game";
if (!isset($preloaderText))
    $preloaderText = "Loading Adventure...";
if (!isset($backUrl))
    $backUrl = "../index.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo $pageTitle; ?> -
        <?php echo $company_name; ?>
    </title>

    <!-- Base Styles -->
    <link rel="stylesheet" href="../css/style.css">

    <!-- Component-specific CSS if any -->
    <?php if (isset($extraCss))
        echo $extraCss; ?>

    <style>
        /* Ensure navbar doesn't break if style.css is missing some parts */
        .nav-logo span {
            color: var(--neon-cyan);
            font-weight: 800;
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="preloader-content">
            <div class="spinner"></div>
            <p class="preloader-text">
                Loading Adventure...
            </p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index.php" class="nav-logo">
                    <span class="logo-icon">⚡</span>
                    <span class="logo-text">
                        <?php
                        $parts = explode(' ', $company_name, 2);
                        if (count($parts) > 1) {
                            echo '<span class="logo-1">' . htmlspecialchars($parts[0]) . '</span> <span class="logo-2">' . htmlspecialchars($parts[1]) . '</span>';
                        } else {
                            echo htmlspecialchars($company_name);
                        }
                        ?>
                    </span>
                </a>
            </div>
            <div class="nav-center">
                <ul class="nav-menu">
                    <li><a href="../index.php" class="nav-link">🏠 Home</a></li>
                    <li><a href="../index.php#games" class="nav-link">🎮 Games</a></li>
                    <li><a href="../leaderboard.php" class="nav-link">🏆 Leaderboard</a></li>
                    <li><a href="../profile.php" class="nav-link">👤 Profile</a></li>
                </ul>
            </div>
            <button class="back-btn" onclick="window.location.href='<?php echo $backUrl; ?>'">
                <span>←</span>
                <span>Back</span>
            </button>
        </div>
    </nav>

    <!-- Main Game Container -->
    <div class="game-container">