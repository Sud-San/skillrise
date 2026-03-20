let currentChallengeIndex = 0;
let timerInterval;
let score = 0;
let streak = 0;
let correct = 0;
let timeRemaining = 300;
let startTime;
let sessionId = null;

window.addEventListener('load', () => {
    setTimeout(() => {
        const preloader = document.getElementById('preloader');
        if (preloader) preloader.classList.add('hidden');
        initGame();
    }, 500);
});

async function initGame() {
    startTime = Date.now();
    startTimer();
    loadChallenge();
    setupEventListeners();
    
    if (window.GameAPI) {
        sessionId = await GameAPI.startSession(3); // 3: Code Complete
    }
}

function setupEventListeners() {
    document.getElementById('submitBtn').addEventListener('click', submitAnswer);
    document.getElementById('skipBtn').addEventListener('click', skipChallenge);
    document.getElementById('answerInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || (e.ctrlKey && e.key === 'Enter')) {
            submitAnswer();
        }
    });
}

function startTimer() {
    timerInterval = setInterval(() => {
        timeRemaining--;
        updateTimerDisplay();
        if (timeRemaining <= 0) endGame();
    }, 1000);
}

function updateTimerDisplay() {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    document.getElementById('timer').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
}

function loadChallenge() {
    if (currentChallengeIndex >= challenges.length) {
        endGame();
        return;
    }

    const challenge = challenges[currentChallengeIndex];

    document.getElementById('challengeTitle').textContent = `Challenge ${currentChallengeIndex + 1} - ${challenge.language}`;
    document.getElementById('difficulty').textContent = challenge.difficulty;
    document.getElementById('difficulty').className = `game-difficulty difficulty-${challenge.difficulty.toLowerCase()}`;
    document.getElementById('instruction').textContent = challenge.instruction;

    const codeBlock = document.getElementById('codeBlock');
    const lines = challenge.code.split('\n');
    codeBlock.innerHTML = lines.map((line, index) => `
                <div class="code-line">
                    <span class="line-number">${index + 1}</span>
                    <span class="line-content">${highlightBlank(line)}</span>
                </div>
            `).join('');

    document.getElementById('answerInput').value = '';
    document.getElementById('answerInput').focus();
    closeFeedbackPopup();

    updateProgress();
}

function highlightBlank(line) {
    return line.replace(/___/g, '<span style="background: var(--neon-cyan); color: var(--bg-primary); padding: 0 0.5rem; border-radius: 0.25rem; font-weight: 700;">___</span>');
}

function submitAnswer() {
    const challenge = challenges[currentChallengeIndex];
    const userAnswer = document.getElementById('answerInput').value.trim().toLowerCase();
    const correctAnswer = challenge.answer.toLowerCase();
    const alternatives = challenge.alternatives || [];

    const isCorrect = userAnswer === correctAnswer || alternatives.some(alt => alt.toLowerCase() === userAnswer);

    if (isCorrect) {
        correct++;
        streak++;
        const multiplier = Math.min(Math.floor(streak / 3) + 1, 5);
        const points = 100 * multiplier;
        score += points;
        showFeedback(true, points, challenge);
    } else {
        streak = 0;
        showFeedback(false, 0, challenge);
    }

    updateScore();

    // Save attempt
    if (sessionId && window.GameAPI) {
        const pEarned = isCorrect ? 100 : 0;
        const timeTaken = 0; 
        GameAPI.saveAttempt(sessionId, challenge.challenge_id, isCorrect, pEarned, timeTaken);
    }

    setTimeout(() => {
        currentChallengeIndex++;
        loadChallenge();
    }, isCorrect ? 2000 : 3000);
}

function showFeedback(isCorrect, points, challenge) {
    const feedbackPopup = document.getElementById('feedbackPopup');
    const feedbackTitle = document.getElementById('feedbackTitle');
    const explanationText = document.getElementById('explanationText');

    if (isCorrect) {
        feedbackTitle.innerHTML = `✓ Correct! +${points} points`;
        feedbackTitle.style.color = '#00ff88';
        feedbackPopup.classList.remove('incorrect');
    } else {
        feedbackTitle.innerHTML = `✗ Incorrect`;
        feedbackTitle.style.color = '#ff006e';
        feedbackPopup.classList.add('incorrect');
    }

    let explanation = challenge.explanation;
    if (challenge.alternatives && challenge.alternatives.length > 0) {
        explanation += ` Acceptable answers: ${challenge.answer}, ${challenge.alternatives.join(', ')}`;
    }
    explanationText.textContent = explanation;
    feedbackPopup.classList.add('show');
}

function closeFeedbackPopup() {
    const feedbackPopup = document.getElementById('feedbackPopup');
    feedbackPopup.classList.remove('show');
}

function skipChallenge() {
    streak = 0;
    updateScore();
    currentChallengeIndex++;
    loadChallenge();
}

function updateScore() {
    document.getElementById('score').textContent = score;
    const multiplier = Math.min(Math.floor(streak / 3) + 1, 5);
    document.getElementById('streak').textContent = `${multiplier}x`;
}

function updateProgress() {
    const progress = (currentChallengeIndex / challenges.length) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('progress').textContent = `${currentChallengeIndex}/${challenges.length}`;
}

function endGame() {
    if (timerInterval) clearInterval(timerInterval);

    const totalTime = Math.floor((Date.now() - startTime) / 1000);
    const accuracy = challenges.length > 0 ? Math.round((correct / challenges.length) * 100) : 0;

    document.getElementById('resultIcon').textContent = score >= 1000 ? '🏆' : score >= 500 ? '⭐' : '💪';
    document.getElementById('resultTitle').textContent = score >= 1000 ? 'Excellent!' : score >= 500 ? 'Well Done!' : 'Keep Going!';
    document.getElementById('resultMessage').textContent = score >= 1000 ? 'Outstanding work!' : score >= 500 ? 'Good job!' : 'Keep practicing!';
    document.getElementById('finalScore').textContent = score;
    document.getElementById('accuracy').textContent = `${accuracy}%`;

    const minutes = Math.floor(totalTime / 60);
    const seconds = totalTime % 60;
    document.getElementById('timeTaken').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('completed').textContent = `${correct}/${challenges.length}`;

    if (sessionId && window.GameAPI) {
        const totalTime = Math.floor((Date.now() - startTime) / 1000);
        const result = accuracy >= 50 ? 'WIN' : 'LOSS';
        GameAPI.endSession(sessionId, score, totalTime, accuracy, result);
    }

    document.getElementById('resultModal').classList.add('show');
}