// Game State
let currentLanguage = 'Python';
let currentText = '';
let currentIndex = 0;
let errors = 0;
let startTime = null;
let timerInterval = null;
let timeRemaining = 60;
let isTyping = false;
let totalTyped = 0;
let score = 0;
let trophyScore = 0;
let sessionId = null;
let currentChallengeId = null;
let realStartTime;

// Initialize
window.addEventListener('load', () => {
    setTimeout(() => {
        const preloader = document.getElementById('preloader');
        if (preloader) preloader.classList.add('hidden');
        setupGame();
        loadTrophyScore();
    }, 500);
});

function setupGame() {
    const startBtn = document.getElementById('startBtn');
    const restartBtn = document.getElementById('restartBtn');

    if (startBtn) startBtn.addEventListener('click', startGame);
    if (restartBtn) restartBtn.addEventListener('click', () => location.reload());

    // Language filter buttons
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!isTyping) {
                document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentLanguage = btn.dataset.lang;
                updateLanguageDisplay();
            }
        });
    });

    updateLanguageDisplay();
}

function updateLanguageDisplay() {
    const snippets = codeSnippets[currentLanguage];
    const snippetObj = snippets[Math.floor(Math.random() * snippets.length)];
    currentText = snippetObj.text;
    currentChallengeId = snippetObj.challenge_id;
    document.getElementById('languageBadge').textContent = currentLanguage;
    document.getElementById('challengeTitle').textContent = 'Type this ' + currentLanguage + ' code:';
    document.getElementById('typingText').textContent = currentText;
}

function loadTrophyScore() {
    const saved = localStorage.getItem('typingMasterTrophy');
    trophyScore = saved ? parseInt(saved) : 0;
    updateTrophyDisplay();
}

function updateTrophyDisplay() {
    document.getElementById('score').innerHTML = `🏆 ${trophyScore}`;
}

function saveTrophyScore() {
    localStorage.setItem('typingMasterTrophy', trophyScore.toString());
}

async function startGame() {
    currentIndex = 0;
    errors = 0;
    totalTyped = 0;
    score = 0;
    timeRemaining = 60;
    startTime = Date.now();
    realStartTime = Date.now();
    isTyping = true;
    
    if (window.GameAPI) {
        sessionId = await GameAPI.startSession(4); // 4: Typing Master
    }

    const startBtn = document.getElementById('startBtn');
    const restartBtn = document.getElementById('restartBtn');
    if (startBtn) startBtn.style.display = 'none';
    if (restartBtn) restartBtn.style.display = 'inline-block';

    displayText();

    timerInterval = setInterval(() => {
        timeRemaining--;
        document.getElementById('timer').textContent = `${timeRemaining}s`;
        updateProgress();

        if (timeRemaining <= 0) {
            endGame();
        }
    }, 1000);

    document.addEventListener('keypress', handleKeyPress);
    document.addEventListener('keydown', handleKeyDown);
}

function displayText() {
    const typingText = document.getElementById('typingText');
    if (!typingText) return;

    typingText.innerHTML = '';

    for (let i = 0; i < currentText.length; i++) {
        const char = currentText[i];
        const span = document.createElement('span');

        if (char === '\n') {
            span.innerHTML = '\n';
        } else {
            span.textContent = char;
        }

        if (i < currentIndex) {
            span.className = 'typed-char';
        } else if (i === currentIndex) {
            span.className = 'current-char';
        }

        typingText.appendChild(span);
    }

    updateStats();
}

function handleKeyPress(e) {
    if (!isTyping) return;

    const expectedChar = currentText[currentIndex];
    const typedChar = e.key;

    totalTyped++;

    if (typedChar === 'Enter' && expectedChar === '\n') {
        currentIndex++;
        if (currentIndex >= currentText.length) {
            // Save attempt
            if (sessionId && window.GameAPI) {
                GameAPI.saveAttempt(sessionId, currentChallengeId, true, 500, (Date.now() - startTime) / 1000);
            }
            endGame(true);
            return;
        }
        displayText();
        e.preventDefault();
        return;
    }

    if (typedChar === expectedChar) {
        currentIndex++;
        score += 10;

        if (currentIndex >= currentText.length) {
            // Save attempt
            if (sessionId && window.GameAPI) {
                GameAPI.saveAttempt(sessionId, currentChallengeId, true, 500, (Date.now() - startTime) / 1000);
            }
            endGame(true);
            return;
        }
    } else {
        if (expectedChar !== '\n') {
            errors++;
            score = Math.max(0, score - 5);
        }
    }

    displayText();
}

function handleKeyDown(e) {
    if (!isTyping) return;

    if (e.key === 'Enter') {
        const expectedChar = currentText[currentIndex];
        if (expectedChar === '\n') {
            e.preventDefault();
            totalTyped++;
            currentIndex++;
            score += 10;

            if (currentIndex >= currentText.length) {
                // Save attempt
                if (sessionId && window.GameAPI) {
                    GameAPI.saveAttempt(sessionId, currentChallengeId, true, 500, (Date.now() - startTime) / 1000);
                }
                endGame(true);
                return;
            }

            displayText();
        }
    }

    if (e.key === 'Backspace' && currentIndex > 0) {
        e.preventDefault();
        currentIndex--;
        totalTyped++;
        displayText();
    }
}

function updateStats() {
    const timeElapsed = (Date.now() - startTime) / 1000 / 60;
    const wordsTyped = currentIndex / 5;
    const wpm = Math.round(wordsTyped / (timeElapsed || 0.01));
    const accuracy = totalTyped > 0 ? Math.round(((totalTyped - errors) / totalTyped) * 100) : 100;

    document.getElementById('wpm').textContent = wpm || 0;
    document.getElementById('accuracy').textContent = `${accuracy}%`;
    document.getElementById('errors').textContent = errors;
    updateTrophyDisplay();
}

function updateProgress() {
    const progress = ((60 - timeRemaining) / 60) * 100;
    document.getElementById('progressFill').style.width = `${progress}%`;
}

function endGame(completed = false) {
    isTyping = false;
    if (timerInterval) clearInterval(timerInterval);
    document.removeEventListener('keypress', handleKeyPress);
    document.removeEventListener('keydown', handleKeyDown);

    const timeElapsed = (Date.now() - startTime) / 1000 / 60;
    const wordsTyped = currentIndex / 5;
    const finalWpm = Math.round(wordsTyped / (timeElapsed || 0.01));
    const finalAccuracy = totalTyped > 0 ? Math.round(((totalTyped - errors) / totalTyped) * 100) : 100;

    // Calculate bonus for completion
    if (completed) {
        score += 500;
    }

    // Add to trophy score
    trophyScore += score;
    saveTrophyScore();
    updateTrophyDisplay();

    const message = completed ? '🎉 Perfect! You completed the entire text!' :
        finalWpm >= 60 ? '🔥 Excellent typing speed!' :
            finalWpm >= 40 ? '👍 Good typing speed!' :
                '💪 Keep practicing to improve!';

    document.getElementById('resultMessage').textContent = message;
    document.getElementById('finalWpm').textContent = finalWpm;
    document.getElementById('finalAccuracy').textContent = `${finalAccuracy}%`;
    document.getElementById('finalErrors').textContent = errors;
    document.getElementById('finalScore').textContent = score;

    if (sessionId && window.GameAPI) {
        const duration = Math.floor((Date.now() - realStartTime) / 1000);
        const result = finalAccuracy >= 50 ? 'WIN' : 'LOSS';
        GameAPI.endSession(sessionId, score, duration, finalAccuracy, result);
    }

    document.getElementById('resultModal').classList.add('show');
}