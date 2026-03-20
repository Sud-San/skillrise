/**
 * Debugging Master Game Logic
 * CodeArena
 */


// Game State
let currentLanguage = 'python';
let currentChallengeIndex = 0;

// Combine hardcoded challenges with DB challenges
if (typeof fetchedChallengesFromDB !== 'undefined') {
    fetchedChallengesFromDB.forEach(dbChallenge => {
        // Assume DB challenge has a 'language' field if it's for Debugging Master
        const lang = dbChallenge.language || 'python';
        if (challenges[lang]) {
            challenges[lang].push(dbChallenge);
        }
    });
}

let gameSession;
let timer;
let scoreManager;
let progressTracker;
let correctAnswers = 0;

// Initialize game
document.addEventListener('DOMContentLoaded', function() {
    initGame();
    setupEventListeners();
});

function initGame() {
    gameSession = new CodeArena.GameSession(1, 'Debugging Master');
    scoreManager = new CodeArena.ScoreManager();
    progressTracker = new CodeArena.ProgressTracker(5);
    
    // Start timer (2 minutes per challenge)
    timer = new CodeArena.GameTimer(120, updateTimerDisplay, handleTimeUp);
    timer.start();
    
    loadChallenge();
}

function setupEventListeners() {
    // Language selector
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentLanguage = this.dataset.lang;
            currentChallengeIndex = 0;
            correctAnswers = 0;
            scoreManager.reset();
            progressTracker.reset();
            loadChallenge();
        });
    });

    // Skip button
    document.getElementById('skipBtn').addEventListener('click', skipChallenge);

    // Next button
    document.getElementById('nextBtn').addEventListener('click', nextChallenge);

    // Ctrl+Enter for Next Challenge
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'Enter') {
            const nextBtn = document.getElementById('nextBtn');
            if (nextBtn && nextBtn.style.display !== 'none') {
                nextChallenge();
            }
        }
    });
}

function loadChallenge() {
    const languageChallenges = challenges[currentLanguage];
    if (currentChallengeIndex >= languageChallenges.length) {
        endGame();
        return;
    }

    const challenge = languageChallenges[currentChallengeIndex];
    
    // Update UI
    document.getElementById('currentChallenge').textContent = currentChallengeIndex + 1;
    document.getElementById('totalChallenges').textContent = languageChallenges.length;
    document.getElementById('challengeTitle').textContent = `Find the bug in this ${currentLanguage.toUpperCase()} code:`;
    
    // Update progress bar
    const progress = ((currentChallengeIndex) / languageChallenges.length) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
    
    // Render code
    renderCode(challenge.code);
    
    // Render options
    renderOptions(challenge.errors);
    
    // Hide explanation and next button
    document.getElementById('explanationBox').classList.remove('show');
    document.getElementById('nextBtn').style.display = 'none';
    document.getElementById('skipBtn').style.display = 'inline-flex';
}

function renderCode(code) {
    const codeEditor = document.getElementById('codeEditor');
    const lines = code.split('\n');
    
    codeEditor.innerHTML = lines.map((line, index) => `
        <div class="code-line" id="line-${index + 1}">
            <span class="line-number">${index + 1}</span>
            <span class="line-content">${CodeArena.escapeHtml(line) || '&nbsp;'}</span>
        </div>
    `).join('');
}

function renderOptions(errors) {
    const optionsContainer = document.getElementById('answerOptions');
    const shuffledErrors = CodeArena.shuffleArray(errors);
    
    optionsContainer.innerHTML = shuffledErrors.map((error, index) => `
        <button class="option-btn" data-correct="${error.correct}" data-explanation="${error.text}" onclick="checkAnswer(this)">
            ${error.text}
        </button>
    `).join('');
}

function checkAnswer(button) {
    const isCorrect = button.dataset.correct === 'true';
    const explanation = button.dataset.explanation;
    const challenge = challenges[currentLanguage][currentChallengeIndex];
    
    // Disable all buttons
    document.querySelectorAll('.option-btn').forEach(btn => {
        btn.disabled = true;
        if (btn.dataset.correct === 'true') {
            btn.classList.add('correct');
        } else if (btn === button && !isCorrect) {
            btn.classList.add('incorrect');
        }
    });
    
    // Update score
    const points = isCorrect ? 100 : 0;
    const earnedPoints = scoreManager.addPoints(points, isCorrect);
    if (isCorrect) {
        correctAnswers++;
        CodeArena.UIFeedback.showSuccess(`+${earnedPoints} points! ${scoreManager.getStreak() > 1 ? `🔥 ${scoreManager.getStreak()} streak!` : ''}`);
    } else {
        CodeArena.UIFeedback.showError('Incorrect! Try again next time.');
    }
    
    // Update score display
    document.getElementById('score').textContent = scoreManager.getScore();
    
    // Show explanation
    const explanationBox = document.getElementById('explanationBox');
    const explanationText = document.getElementById('explanationText');
    explanationText.textContent = challenge.explanation;
    
    if (isCorrect) {
        explanationBox.innerHTML = `
            <h4 style="margin-bottom: 0.5rem; color: var(--accent-green);">✓ Correct!</h4>
            <p>${challenge.explanation}</p>
        `;
    } else {
        explanationBox.innerHTML = `
            <h4 style="margin-bottom: 0.5rem; color: var(--accent-red);">✗ Incorrect</h4>
            <p><strong>Correct answer:</strong> ${explanation}</p>
            <p style="margin-top: 0.5rem;">${challenge.explanation}</p>
        `;
    }
    explanationBox.classList.add('show');
    
    // Record answer in session
    gameSession.recordAnswer(currentChallengeIndex, explanation, isCorrect, timer.getRemaining());
    progressTracker.incrementProgress(isCorrect);
    
    // Show next button
    document.getElementById('skipBtn').style.display = 'none';
    document.getElementById('nextBtn').style.display = 'inline-flex';
}

function skipChallenge() {
    scoreManager.resetStreak();
    gameSession.recordAnswer(currentChallengeIndex, 'skipped', false, timer.getRemaining());
    progressTracker.incrementProgress(false);
    nextChallenge();
}

function nextChallenge() {
    currentChallengeIndex++;
    loadChallenge();
}

function updateTimerDisplay(remaining) {
    const formatted = timer.formatTime(remaining);
    document.getElementById('timer').textContent = formatted;
    
    // Add warning color when time is running out
    const timerEl = document.getElementById('timer');
    if (remaining <= 30) {
        timerEl.style.color = 'var(--accent-red)';
    } else if (remaining <= 60) {
        timerEl.style.color = 'var(--accent-orange)';
    } else {
        timerEl.style.color = 'var(--accent-blue)';
    }
}

function handleTimeUp() {
    CodeArena.UIFeedback.showError('Time\'s up!');
    endGame();
}

async function endGame() {
    timer.stop();
    
    const progress = progressTracker.getProgress();
    const finalScore = scoreManager.getScore();
    const accuracy = progress.accuracy;
    const timeTaken = 120 - timer.getRemaining();
    
    // Update result modal
    document.getElementById('finalScore').textContent = CodeArena.formatNumber(finalScore);
    document.getElementById('accuracy').textContent = `${Math.round(accuracy)}%`;
    document.getElementById('timeTaken').textContent = CodeArena.formatTime(timeTaken);
    document.getElementById('bugsFound').textContent = correctAnswers;
    
    // Set message based on performance
    let message = '';
    let icon = '🏆';
    
    if (accuracy >= 80) {
        message = 'Outstanding! You\'re a debugging master!';
        icon = '🏆';
    } else if (accuracy >= 60) {
        message = 'Great job! Keep practicing to improve.';
        icon = '🎯';
    } else if (accuracy >= 40) {
        message = 'Good effort! Review the explanations and try again.';
        icon = '📚';
    } else {
        message = 'Keep learning! Practice makes perfect.';
        icon = '💪';
    }
    
    document.getElementById('resultIcon').textContent = icon;
    document.getElementById('resultMessage').textContent = message;
    
    // Show result modal
    document.getElementById('resultModal').classList.add('show');
    
    // Save game session
    try {
        gameSession.setLanguage(currentLanguage);
        await gameSession.saveSession();
    } catch (error) {
        console.error('Error saving session:', error);
    }
}
