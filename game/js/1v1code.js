function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}
shuffleArray(challenges);
let gameMode = 'vsAI';
let currentChallenge = 0;
let player1Stats = {
    correct: 0,
    wrong: 0,
    time: 0
};
let player2Stats = {
    correct: 0,
    wrong: 0,
    time: 0
};
let startTime;
let gameInterval;
let sessionId = null;
let gameActive = false;
const totalChallenges = Math.min(10, challenges.length);



window.addEventListener('load', () => {
    setTimeout(() => {
        document.getElementById('preloader').classList.add('hidden');
    }, 500);
});

async function startGame(mode) {
    gameMode = mode;
    
    // Start API session (1v1 Code ID: 10)
    sessionId = await GameAPI.startSession(10);
    console.log("Session ID:", sessionId);

    document.getElementById('modeSelection').style.display = 'none';
    document.getElementById('gameArea').style.display = 'block';

    if (mode === 'practice') {
        document.querySelector('.player2').style.display = 'none';
        document.getElementById('player2Name').textContent = 'Practice';
    }

    startCountdown();
}

function startCountdown() {
    const display = document.getElementById('countdownDisplay');
    let count = 3;

    const countInterval = setInterval(() => {
        if (count > 0) {
            display.innerHTML = `<div class="countdown">${count}</div>`;
            count--;
        } else {
            display.innerHTML = '<div class="countdown">GO!</div>';
            setTimeout(() => {
                display.style.display = 'none';
                document.getElementById('duelArena').style.display = 'grid';
                startDuel();
            }, 1000);
            clearInterval(countInterval);
        }
    }, 1000);
}

function startDuel() {
    // shuffleArray(challenges);

    startTime = Date.now();
    loadChallenge();

    document.getElementById('p1Input').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || (e.ctrlKey && e.key === 'Enter')) {
            checkAnswer('player1');
        }
    });

    gameInterval = setInterval(updateTime, 100);

    gameActive = true;
    if (gameMode === 'vsAI') {
        simulateAI();
    }
}

function loadChallenge() {
    if (currentChallenge >= totalChallenges || currentChallenge >= challenges.length) {
        endGame();
        return;
    }

    const challenge = challenges[currentChallenge];

    document.getElementById('p1Question').textContent = challenge.q;
    document.getElementById('p2Question').textContent = challenge.q;
    document.getElementById('p1Input').value = '';
    document.getElementById('p1Input').focus();
}

function checkAnswer(player) {
    const challenge = challenges[currentChallenge];
    const input = document.getElementById('p1Input');
    const userAnswer = input.value.trim().toLowerCase();

    if (userAnswer === challenge.a.toLowerCase()) {
        const solveTime = (Date.now() - startTime) / 1000;
        player1Stats.correct++;
        document.getElementById('p1Correct').textContent = player1Stats.correct;
        
        // Save correct attempt
        if (sessionId) {
            GameAPI.saveAttempt(sessionId, challenge.challenge_id, true, 100, solveTime);
        }

        currentChallenge++;
        updateProgress('player1');
        loadChallenge();
    } else if (userAnswer !== '') {
        player1Stats.wrong++;
        document.getElementById('p1Wrong').textContent = player1Stats.wrong;
        
        // Save wrong attempt
        if (sessionId) {
            GameAPI.saveAttempt(sessionId, challenge.challenge_id, false, 0, 0);
        }

        input.value = '';
        input.style.borderColor = 'var(--color-error)';
        setTimeout(() => {
            input.style.borderColor = '';
        }, 300);
    }
}

function simulateAI() {
    const aiSpeed = 2000 + Math.random() * 3000; // 2-5 seconds per answer
    const aiAccuracy = 0.85; // 85% accuracy

    setTimeout(function aiAnswer() {
        if (!gameActive) return;

        const correct = Math.random() < aiAccuracy;

        if (correct) {
            player2Stats.correct++;
            document.getElementById('p2Correct').textContent = player2Stats.correct;
            updateProgress('player2');
        } else {
            player2Stats.wrong++;
            document.getElementById('p2Wrong').textContent = player2Stats.wrong;
        }

        const nextAISpeed = 2000 + Math.random() * 3000;
        setTimeout(aiAnswer, nextAISpeed);
    }, aiSpeed);
}

function updateProgress(player) {
    const progress = player === 'player1' ? player1Stats.correct : player2Stats.correct;
    const percentage = (progress / totalChallenges) * 100;

    const progressBar = document.getElementById(player === 'player1' ? 'player1Progress' : 'player2Progress');
    progressBar.style.width = `${percentage}%`;
    progressBar.textContent = `${Math.round(percentage)}%`;

    if (progress >= totalChallenges) {
        endGame(player);
    }
}

function updateTime() {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    player1Stats.time = elapsed;
    document.getElementById('p1Time').textContent = `${elapsed}s`;

    if (gameMode === 'vsAI') {
        player2Stats.time = elapsed;
        document.getElementById('p2Time').textContent = `${elapsed}s`;
    }
}

function endGame(winner) {
    if (!gameActive) return;
    gameActive = false;
    
    clearInterval(gameInterval);

    const p1Side = document.querySelector('.player1');
    const p2Side = document.querySelector('.player2');

    if (winner === 'player1') {
        p1Side.classList.add('winner');
        document.getElementById('resultIcon').style.color = 'var(--color-primary)';
        document.getElementById('resultIcon').textContent = '🏆';
        document.getElementById('resultTitle').textContent = 'Victory!';
        document.getElementById('resultTitle').style.color = 'var(--color-primary)';
        document.getElementById('resultMessage').textContent = 'You won the duel! Amazing coding skills!';
    } else if (winner === 'player2') {
        p2Side.classList.add('winner');
        document.getElementById('resultIcon').textContent = '😅';
        document.getElementById('resultTitle').textContent = 'Defeated!';
        // Force the color red or something obvious
        document.getElementById('resultTitle').style.color = '#ef4444'; 
        document.getElementById('resultMessage').textContent = 'The AI won this time. Practice and try again!';
    } else {
        document.getElementById('resultIcon').textContent = '✅';
        document.getElementById('resultTitle').textContent = 'Complete!';
        document.getElementById('resultMessage').textContent = 'Great practice session!';
    }

    const accuracy = Math.round((player1Stats.correct / (player1Stats.correct + player1Stats.wrong)) * 100) || 0;
    const score = player1Stats.correct * 100 - player1Stats.wrong * 20;

    document.getElementById('finalCorrect').textContent = player1Stats.correct;
    document.getElementById('finalTime').textContent = `${player1Stats.time}s`;
    document.getElementById('finalAccuracy').textContent = `${accuracy}%`;
    document.getElementById('finalScore').textContent = score;

    // Save session via API
    if (sessionId && window.GameAPI) {
        let result = 'LOSS';
        if (gameMode === 'practice') {
            result = accuracy >= 50 ? 'WIN' : 'LOSS';
        } else {
            result = (winner === 'player1') ? 'WIN' : 'LOSS';
        }
        const duration = Math.floor((Date.now() - startTime) / 1000);
        GameAPI.endSession(sessionId, score, duration, accuracy, result);
    }

    setTimeout(() => {
        document.getElementById('resultModal').classList.add('show');
    }, 2000);
}