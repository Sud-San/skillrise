<?php
session_start();
include_once("../connection.php");
if (!isset($_SESSION['user_id'])) {
    $_SESSION['prelogin_redirect'] = $_SERVER['REQUEST_URI'];
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
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <!-- Preloader -->
    <?php if ($_SESSION['game_preloader'] == 0) { ?>
        <div id="gamePreloader">
            <div class="ring-system">
                <div class="arc arc1"></div>
                <div class="arc arc2"></div>
                <div class="arc arc3"></div>
                <div class="arc arc4"></div>
                <div class="arc arc5"></div>
                <div class="arc arc6"></div>
                <div class="arc arc7"></div>
                <div class="arc arc8"></div>
                <div class="arc arc9"></div>
                <div class="arc arc10"></div>
                <div class="arc arc11"></div>
                <div class="arc arc12"></div>
            </div>

            <div class="center-content">
                <div class="logo">SKILLRISE <span>PLAY</span></div>

                <div class="terminal">
                    <span id="loadingText"></span>
                    <span class="cursor">|</span>
                </div>

                <div class="progress-bar">
                    <div class="progress"></div>
                </div>

                <div class="loading-percent" id="percent">0%</div>
            </div>

        </div>
    <?php } ?>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <span class="nav-logo-icon">⚡</span>
                <span><?php echo $company_name; ?></span>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link active">🏠 Home</a></li>
                <li><a href="games.php" class="nav-link">🎮 Games</a></li>
                <li><a href="leaderboard.php" class="nav-link">🏆 Leaderboard</a></li>
                <li><a href="profile.php" class="nav-link">👤 Profile</a></li>
            </ul>
            <a href="../index.php"><button class="nav-cta">Start Learning</button></a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content fade-in">
            <div class="hero-badge">
                <span>⚡</span>
                <span>Interactive Coding Education Platform</span>
            </div>
            <h1 class="hero-title">Master Code<br>Through Play</h1>
            <p class="hero-subtitle">
                Debug, predict outputs, complete challenges, and race against time.
                Level up your programming skills with interactive coding games designed for real learning.
            </p>
            <div class="hero-actions">
                <a href="#games" class="btn btn-primary">🎮 Start Playing</a>
                <a href="leaderboard.php" class="btn btn-secondary">🏆 View Leaderboard</a>
            </div>
        </div>
    </section>

    <!-- Games Dashboard -->
    <section class="dashboard" id="games">
        <div class="section-header">
            <h2 class="section-title">Choose Your Challenge</h2>
            <!-- <p class="section-subtitle">
                7 unique games to test and improve your coding skills across PHP, Java, Python, HTML, and SQL
            </p> -->
        </div>
        <!-- <div class="difficulty-filters">
            <button class="btn btn-primary diff-btn active" data-difficulty="all">ALL</button>
            <button class="btn btn-primary diff-btn" data-difficulty="easy" href="easy.php">EASY</button>
            <button class="btn btn-primary diff-btn" data-difficulty="medium" href="medium.php">MEDIUM</button>
            <button class="btn btn-primary diff-btn" data-difficulty="hard" href="hard.php">HARD</button>
        </div> -->
        <div class="games-grid" id="gamesGrid">
            <!-- Games will be dynamically inserted here -->
        </div>
    </section>

    <script>
        // Preloader
        // window.addEventListener('load', () => {
        //     setTimeout(() => {
        //         document.getElementById('gamePreloader').classList.add('hidden');
        //     }, 500);
        // });

        // Games Data

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


        // Render Games
        const gamesGrid = document.getElementById("gamesGrid");

        function renderGames(level = "all") {
            gamesGrid.innerHTML = "";

            const filtered = level === "all" ?
                games :
                games.filter(g => g.difficulty === level);

            filtered.forEach(game => {
                const card = document.createElement("div");
                card.className = "game-card";
                card.onclick = () => window.location.href = `games/${game.slug}.php`;

                card.innerHTML = `
            <div class="game-header">
                <div class="game-icon">${game.icon}</div>
                <div class="game-info">
                    <div class="game-category">${game.category}</div>
                    <div class="game-title">${game.title}</div>
                </div>
                <div class="game-difficulty difficulty-${game.difficulty}">
                    ${game.difficulty.toUpperCase()}
                </div>
            </div>
            <div class="game-body">
                <p class="game-description">${game.description}</p>
                <div class="game-stats">
                    <span>⏱️ ${game.duration} minutes</span>
                    <span>👥 ${game.players}</span>
                </div>
            </div>
        `;
                gamesGrid.appendChild(card);
            });
        }
        // Initial load
        renderGames();
        document.querySelectorAll('.diff-btn').forEach(btn => {
            btn.addEventListener('click', () => {

                // remove active from all
                document.querySelectorAll('.diff-btn')
                    .forEach(b => b.classList.remove('active'));

                // add active to clicked
                btn.classList.add('active');

                // get difficulty
                const difficulty = btn.dataset.difficulty;

                // render filtered games
                renderGames(difficulty);
            });
        });


        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>

    <?php if ($_SESSION['game_preloader'] == 0) {
        $_SESSION['game_preloader'] = 1;
        ?>
        <script>
            const texts = [
                "Initializing game engine...",
                "Loading challenges...",
                "Injecting bugs...",
                "Compiling fun...",
                "Ready to play!"
            ];

            let textIndex = 0;
            let charIndex = 0;
            let percent = 0;

            const loadingText = document.getElementById("loadingText");
            const progress = document.querySelector(".progress");
            const percentText = document.getElementById("percent");

            const TOTAL_TIME = 5000; // total preloader time (ms)
            const STEP_TIME = TOTAL_TIME / 100; // progress speed

            function typeText() {
                if (charIndex < texts[textIndex].length) {
                    loadingText.textContent += texts[textIndex].charAt(charIndex);
                    charIndex++;
                    setTimeout(typeText, 50); // ⏳ slower typing
                } else {
                    setTimeout(() => {
                        loadingText.textContent = "";
                        charIndex = 0;
                        textIndex++;

                        if (textIndex < texts.length) {
                            typeText();
                        }
                    }, 800); // ⏸ hold text before next
                }
            }

            typeText();

            const interval = setInterval(() => {
                percent++;
                progress.style.width = percent + "%";
                percentText.textContent = percent + "%";

                if (percent >= 100) {
                    clearInterval(interval);
                    setTimeout(() => {
                        document.getElementById("gamePreloader").style.opacity = "0";
                        setTimeout(() => {
                            document.getElementById("gamePreloader").style.display = "none";
                        }, 700);
                    }, 800);
                }
            }, STEP_TIME);
        </script>
        <script>
            document.querySelectorAll('.arc').forEach(arc => {
                const speed = Math.floor(Math.random() * 20) + 10; // 10–30s
                arc.style.animationDuration = speed + 's';
                arc.style.animationDirection = Math.random() > 0.5 ? 'normal' : 'reverse';
            });
        </script>
    <?php } ?>

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

</body>

</html>