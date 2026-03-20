<?php
session_start();
include_once("../connection.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $company_name; ?> - Master Coding Through Interactive Games</title>
    <meta name="description"
        content="Level up your coding skills with interactive games. Debug, predict outputs, complete code, and compete on leaderboards.">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">
    <link rel="stylesheet" href="css/games.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- Games Dashboard -->
    <section class="dashboard" id="games">
        <div class="section-header">
            <h2 class="section-title">Choose Your Challenge</h2>
            <p class="section-subtitle">
                <?php
                $games_query = "SELECT * FROM games WHERE is_active = 1";
                $result = mysqli_query($conn, $games_query);
                ?>
                <?php echo mysqli_num_rows($result); ?> unique games to test and improve your coding skills across PHP,
                Java, Python, HTML, and SQL
            </p>
        </div>
        <div class="difficulty-filters">
            <button class="btn btn-primary diff-btn active" data-difficulty="all">ALL</button>
            <button class="btn btn-primary diff-btn" data-difficulty="easy" href="easy.php">EASY</button>
            <button class="btn btn-primary diff-btn" data-difficulty="medium" href="medium.php">MEDIUM</button>
            <button class="btn btn-primary diff-btn" data-difficulty="hard" href="hard.php">HARD</button>
        </div>


        <div class="games-grid" id="gamesGrid">
            <!-- Games will be dynamically inserted here -->
        </div>
    </section>

    <script>
        // ================== GAMES DATA ==================
        <?php
        $games_query = "SELECT * FROM games WHERE is_active = 1";
        $result = mysqli_query($conn, $games_query);
        $games = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $games[] = $row;
        }
        ?>
        const games = [
            <?php foreach ($games as $index => $game) { ?> {
                    "slug": '<?php echo $game['slug']; ?>',
                    "icon": '<?php echo $game['icon']; ?>',
                    "category": '<?php echo strtolower($game['category']); ?>',
                    "title": '<?php echo $game['name']; ?>',
                    "description": '<?php echo $game['description']; ?>',
                    "difficulty": '<?php echo strtolower($game['difficulty']); ?>',
                    "duration": '<?php echo $game['base_duration_minutes']; ?>',
                    "players": '<?php echo "1500"; ?>'
                }
                                    <?php if ($index < count($games) - 1)
                                        echo ','; ?>
            <?php } ?>
        ];
    </script>
    <script src="js/games.js"></script>
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">

            <!-- Brand -->
            <div class="footer-brand">
                <div class="footer-logo">⚡ <?php echo $company_name; ?><span> Play</span></div>
                <p class="footer-desc">
                    <?php echo $company_name; ?> is an interactive coding platform where learning feels like a game.
                    Debug, compete, practice, and level up your programming skills.
                </p>
            </div>

            <!-- Links -->
            <div class="footer-links">
                <h4>Platform</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="games.php">Games</a></li>
                    <li><a href="leaderboard.php">Leaderboard</a></li>
                    <li><a href="profile.php">Profile</a></li>
                </ul>
            </div>

            <!-- Links -->
            <div class="footer-links">
                <h4>Explore</h4>
                <ul>
                    <li><a href="#games">Challenges</a></li>
                    <li><a href="#">How It Works</a></li>
                    <li><a href="#">Terms & Policy</a></li>
                    <li><a href="#">Support</a></li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            © <?php echo date("Y"); ?> <?php echo $company_name; ?>. All rights reserved.
        </div>
    </footer>
    <script>
        document.getElementById("game").classList.add("active");
    </script>
</body>

</html>