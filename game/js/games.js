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