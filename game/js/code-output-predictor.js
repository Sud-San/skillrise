function shuffleArray(array) {
        if (!Array.isArray(array)) return;
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
    }
    
    // Shuffle challenges for each language
    Object.keys(challenges).forEach(lang => {
        shuffleArray(challenges[lang]);
    });
    document.getElementById("languageDropdown").addEventListener("change", function () {
        currentLanguage = this.value;
        currentChallengeIndex = 0;
        correctAnswers = 0;
        if (scoreManager) scoreManager.reset();
        if (progressTracker) progressTracker.reset();
        if (timer) {
            timer.reset();
            timer.start();
        }
        loadChallenge();
    });
    let currentLanguage = 'python';
    let currentChallengeIndex = 0;
    let gameSession;
    let timer;
    let scoreManager;
    let progressTracker;
    let correctAnswers = 0;

    document.addEventListener('DOMContentLoaded', function () {
        initGame();
        setupEventListeners();
    });

    function initGame() {
        gameSession = new CodeArena.GameSession(2, 'Code Output Predictor');
        scoreManager = new CodeArena.ScoreManager();
        progressTracker = new CodeArena.ProgressTracker(5);

        timer = new CodeArena.GameTimer(60, updateTimerDisplay, handleTimeUp);
        timer.start();

        loadChallenge();
    }

    function setupEventListeners() {

        document.getElementById('skipBtn').addEventListener('click', skipChallenge);
        document.getElementById('nextBtn').addEventListener('click', nextChallenge);

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

        document.getElementById('currentChallenge').textContent = currentChallengeIndex + 1;
        document.getElementById('totalChallenges').textContent = languageChallenges.length;

        const progress = ((currentChallengeIndex) / languageChallenges.length) * 100;
        document.getElementById('progressBar').style.width = `${progress}%`;

        // Render code
        document.getElementById('codeBlock').innerHTML = `<pre><code>${CodeArena.escapeHtml(challenge.code)}</code></pre>`;

        // Render options
        renderOptions(challenge);

        document.getElementById('explanationBox').classList.remove('show');
        document.getElementById('nextBtn').style.display = 'none';
        document.getElementById('skipBtn').style.display = 'inline-flex';
    }

    function renderOptions(challenge) {
        const optionsContainer = document.getElementById('answerOptions');
        const shuffledIndices = CodeArena.shuffleArray([...Array(challenge.options.length).keys()]);

        optionsContainer.innerHTML = shuffledIndices.map((originalIndex, shuffledIndex) => {
            const isCorrect = originalIndex === challenge.correctIndex;
            return `
            <button class="option-btn" 
                    data-correct="${isCorrect}" 
                    data-index="${originalIndex}"
                    onclick="checkAnswer(this, ${challenge.correctIndex})">
                ${CodeArena.escapeHtml(challenge.options[originalIndex])}
            </button>
        `;
        }).join('');
    }

    function checkAnswer(button, correctIndex) {
        const isCorrect = button.dataset.correct === 'true';
        const challenge = challenges[currentLanguage][currentChallengeIndex];

        document.querySelectorAll('.option-btn').forEach(btn => {
            btn.disabled = true;
            if (btn.dataset.correct === 'true') {
                btn.classList.add('correct');
            } else if (btn === button && !isCorrect) {
                btn.classList.add('incorrect');
            }
        });

        const points = isCorrect ? 150 : 0;
        const earnedPoints = scoreManager.addPoints(points, isCorrect);

        if (isCorrect) {
            correctAnswers++;
            CodeArena.UIFeedback.showSuccess(`+${earnedPoints} points! ${scoreManager.getStreak() > 1 ? `🔥 ${scoreManager.getStreak()} streak!` : ''}`);
        } else {
            CodeArena.UIFeedback.showError('Incorrect! The correct answer is: ' + challenge.options[correctIndex]);
        }

        document.getElementById('score').textContent = scoreManager.getScore();

        const explanationBox = document.getElementById('explanationBox');
        if (isCorrect) {
            explanationBox.innerHTML = `
            <h4 style="margin-bottom: 0.5rem; color: var(--accent-green);">✓ Correct!</h4>
            <p>${challenge.explanation}</p>
        `;
        } else {
            explanationBox.innerHTML = `
            <h4 style="margin-bottom: 0.5rem; color: var(--accent-red);">✗ Incorrect</h4>
            <p><strong>Correct output:</strong> ${challenge.options[correctIndex]}</p>
            <p style="margin-top: 0.5rem;">${challenge.explanation}</p>
        `;
        }
        explanationBox.classList.add('show');

        gameSession.recordAnswer(currentChallengeIndex, challenge.options[parseInt(button.dataset.index)], isCorrect, timer.getRemaining());
        progressTracker.incrementProgress(isCorrect);

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

        const timerEl = document.getElementById('timer');
        if (remaining <= 15) {
            timerEl.style.color = 'var(--accent-red)';
        } else if (remaining <= 30) {
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
        const timeTaken = 60 - timer.getRemaining();

        document.getElementById('finalScore').textContent = CodeArena.formatNumber(finalScore);
        document.getElementById('accuracy').textContent = `${Math.round(accuracy)}%`;
        document.getElementById('timeTaken').textContent = CodeArena.formatTime(timeTaken);
        document.getElementById('correctPredictions').textContent = correctAnswers;

        let message = '';
        let icon = '🏆';

        if (accuracy >= 80) {
            message = 'Excellent! You really understand code execution!';
            icon = '🏆';
        } else if (accuracy >= 60) {
            message = 'Good job! Keep practicing to improve.';
            icon = '🎯';
        } else if (accuracy >= 40) {
            message = 'Not bad! Review the explanations and try again.';
            icon = '📚';
        } else {
            message = 'Keep learning! Practice reading code carefully.';
            icon = '💪';
        }

        document.getElementById('resultIcon').textContent = icon;
        document.getElementById('resultMessage').textContent = message;
        document.getElementById('resultModal').classList.add('show');

        try {
            gameSession.setLanguage(currentLanguage);
            await gameSession.saveSession();
        } catch (error) {
            console.error('Error saving session:', error);
        }
    }