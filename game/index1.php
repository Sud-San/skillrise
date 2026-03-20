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
    <meta name="description" content="Level up your coding skills with interactive games. Debug, predict outputs, complete code, and compete on leaderboards.">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">
    <style>
        #gamePreloader {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at top, #0a1a3a, #000814);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #00e5ff;
            font-family: 'Consolas', monospace;
            z-index: 9999;
        }

        .difficulty-filters {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
            width: 100%;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .diff-btn {
            padding: 8px 22px;
            font-size: 14px;
            text-align: center;
        }

        .diff-btn.active {
            box-shadow: 0 0 12px rgba(77, 171, 247, 0.9);
            transform: scale(1.06);
        }

        .logo {
            font-size: 38px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 25px;
        }

        .logo span {
            color: #4dabf7;
        }

        .terminal {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .cursor {
            animation: blink 1s infinite;
        }

        @keyframes blink {
            50% {
                opacity: 0;
            }
        }

        .progress-bar {
            width: 320px;
            height: 10px;
            background: #021027;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #00e5ff, #4dabf7);
            transition: width 0.3s ease;
        }

        .loading-percent {
            font-size: 14px;
            opacity: 0.8;
        }


        /* Center everything */
        .center-content {
            position: relative;
            z-index: 5;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Ring system container */
        .ring-system {
            position: absolute;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Base arc style */
        .arc {
            position: absolute;
            border-radius: 50%;
            background: conic-gradient(from 0deg,
                    rgba(95, 168, 255, 0.7) 0deg,
                    rgba(95, 168, 255, 0.7) 140deg,
                    transparent 140deg,
                    transparent 360deg);
            mask: radial-gradient(circle, transparent 70%, black 68%);
            animation: spin linear infinite;
            opacity: 0.9;
        }

        /* ================= FOOTER ================= */
        .footer {
            background: linear-gradient(180deg, #0a0e27, #05081a);
            border-top: 1px solid var(--border-color);
            padding: 3rem 1rem 2rem;
            margin-top: 4rem;
        }

        .footer-container {
            max-width: 1400px;
            margin: auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
        }

        .footer-brand {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .footer-logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
        }

        .footer-logo span {
            color: var(--neon-blue);
        }

        .footer-desc {
            color: var(--text-secondary);
            font-size: 0.95rem;
            max-width: 420px;
            line-height: 1.6;
        }

        .footer-links h4 {
            color: white;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.6rem;
        }

        .footer-links a {
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: var(--neon-cyan);
        }

        .footer-bottom {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .footer-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-desc {
                margin: auto;
            }
        }


        /* Sizes + speeds (layered) — increased by +50px */
        .arc1 {
            width: 365px;
            height: 365px;
            animation-duration: 8s;
        }

        .arc2 {
            width: 410px;
            height: 410px;
            animation-duration: 10s;
            animation-direction: reverse;
        }

        .arc3 {
            width: 455px;
            height: 455px;
            animation-duration: 12s;
        }

        .arc4 {
            width: 500px;
            height: 500px;
            animation-duration: 14s;
            animation-direction: reverse;
        }

        .arc5 {
            width: 550px;
            height: 550px;
            animation-duration: 16s;
        }

        .arc6 {
            width: 600px;
            height: 600px;
            animation-duration: 18s;
            animation-direction: reverse;
        }

        .arc7 {
            width: 655px;
            height: 655px;
            animation-duration: 20s;
        }

        .arc8 {
            width: 690px;
            height: 690px;
            animation-duration: 22s;
            animation-direction: reverse;
        }

        .arc9 {
            width: 735px;
            height: 735px;
            animation-duration: 24s;
        }

        .arc10 {
            width: 780px;
            height: 780px;
            animation-duration: 26s;
            animation-direction: reverse;
        }

        .arc11 {
            width: 825px;
            height: 825px;
            animation-duration: 28s;
        }

        .arc12 {
            width: 870px;
            height: 870px;
            animation-duration: 30s;
            animation-direction: reverse;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <!-- Preloader -->
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
    </div>


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
                Level up your programming skills with 7 interactive coding games designed for real learning.
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
            <p class="section-subtitle">
                7 unique games to test and improve your coding skills across PHP, Java, Python, HTML, and SQL
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
        // Preloader
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('preloader').classList.add('hidden');
            }, 500);
        });

        // Games Data

        // ================== GAMES DATA ==================
        const games = [{
                slug: 'typing-master',
                icon: '⌨️',
                category: 'Speed',
                title: 'Typing Master',
                description: 'Type real code snippets fast and improve accuracy.',
                difficulty: 'easy',
                duration: '5 min',
                players: '15,678'
            },
            {
                slug: 'code-memes-guess',
                icon: '😂',
                category: 'Fun',
                title: 'Code Memes Guess',
                description: 'Guess programming concepts using memes.',
                difficulty: 'easy',
                duration: '8 min',
                players: '18,723'
            },
            {
                slug: 'code-output-predictor',
                icon: '🔮',
                category: 'Logic',
                title: 'Code Output Predictor',
                description: 'Predict what the code will output.',
                difficulty: 'medium',
                duration: '10 min',
                players: '6,789'
            },
            {
                slug: 'Unix',
                icon: '💾',
                category: 'System',
                title: 'Unix Command Master',
                description: 'Practice Unix commands like ls, cd, grep.',
                difficulty: 'medium',
                duration: '15 min',
                players: '7,890'
            },
            {
                slug: 'escape-room',
                icon: '🚪',
                category: 'Puzzle',
                title: 'Escape Room',
                description: 'Solve puzzles to escape rooms.',
                difficulty: 'medium',
                duration: '25 min',
                players: '6,234'
            },
            {
                slug: '1v1code',
                icon: '⚔️',
                category: 'Competitive',
                title: '1v1 Code Duel',
                description: 'Battle coders in real time.',
                difficulty: 'hard',
                duration: '15 min',
                players: '9,456'
            },
            {
                slug: 'bug-race',
                icon: '🏁',
                category: 'Speed',
                title: 'Bug Race',
                description: 'Fix bugs before time runs out.',
                difficulty: 'hard',
                duration: '10 min',
                players: '5,321'
            },
            {
                slug: 'guess-error',
                icon: '🎯',
                category: 'Debugging',
                title: 'Guess The Error',
                description: 'Identify syntax, runtime & logic errors.',
                difficulty: 'hard',
                duration: '12 min',
                players: '7,654'
            }
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
                    <span>⏱️ ${game.duration}</span>
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
            anchor.addEventListener('click', function(e) {
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

        const TOTAL_TIME = 9000; // total preloader time (ms)
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

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">

            <!-- Brand -->
            <div class="footer-brand">
                <div class="footer-logo">⚡ <?php echo $company_name; ?><span>_Play</span></div>
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