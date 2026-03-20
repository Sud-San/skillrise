function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}
shuffleArray(errors);
let currentErrorIndex = 0;
let score = 0;
let correctCount = 0;
let timeRemaining = 15;
let timerInterval;
let totalTime = 0;
let answered = false;
let sessionId = null;
let startTime;

window.addEventListener('load', () => {
    setTimeout(async () => {
        document.getElementById('preloader').classList.add('hidden');
        startTime = Date.now();
        if (window.GameAPI) {
            sessionId = await GameAPI.startSession(12); // 12: Guess the Error Message
        }
        loadError();
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'Enter') {
                const nextBtn = document.getElementById('nextBtn');
                if (nextBtn && !nextBtn.disabled) {
                    nextError();
                }
            }
        });
    }, 500);
});

function loadError() {
    if (currentErrorIndex >= errors.length) {
        endGame();
        return;
    }

    answered = false;
    timeRemaining = 15;
    const error = errors[currentErrorIndex];

    document.getElementById('difficultyBadge').className = `difficulty-badge difficulty-${error.difficulty}`;
    document.getElementById('difficultyBadge').textContent = error.difficulty.charAt(0).toUpperCase() + error.difficulty.slice(1);

    document.getElementById('errorDisplay').innerHTML = `
                <div class="error-type">${error.type}</div>
                <div class="error-message">${error.message}</div>
                <div class="error-location">${error.location}</div>
            `;

    const causesGrid = document.getElementById('causesGrid');
    causesGrid.innerHTML = '';

    error.causes.forEach((cause, index) => {
        const causeDiv = document.createElement('div');
        causeDiv.className = 'cause-option';
        causeDiv.innerHTML = `
                    <div class="cause-title">${cause.title}</div>
                    <div class="cause-desc">${cause.desc}</div>
                `;
        causeDiv.onclick = () => selectCause(index);
        causesGrid.appendChild(causeDiv);
    });

    document.getElementById('nextBtn').disabled = true;
    document.getElementById('currentError').textContent = currentErrorIndex + 1;
    document.getElementById('progressBar').style.width = `${((currentErrorIndex + 1) / errors.length) * 100}%`;
}

function startTimer() {
    timerInterval = setInterval(() => {
        if (!answered) {
            timeRemaining--;
            document.getElementById('timerCircle').textContent = timeRemaining;

            if (timeRemaining <= 5) {
                document.getElementById('timerCircle').classList.add('warning');
            } else {
                document.getElementById('timerCircle').classList.remove('warning');
            }

            if (timeRemaining <= 0) {
                selectCause(-1);
            }
        }
    }, 1000);
}

function selectCause(selectedIndex) {
    if (answered) return;
    answered = true;
    clearInterval(timerInterval);

    const error = errors[currentErrorIndex];
    const causes = document.querySelectorAll('.cause-option');
    const timeTaken = 15 - timeRemaining;
    totalTime += timeTaken;

    causes.forEach((cause, index) => {
        cause.onclick = null;
        if (error.causes[index].correct) {
            cause.classList.add('correct');
        } else if (index === selectedIndex) {
            cause.classList.add('wrong');
        }
    });

    const correctIndex = error.causes.findIndex(c => c.correct);
    const isCorrect = selectedIndex === correctIndex;

    let pointsEarned = 0;
    if (isCorrect) {
        correctCount++;
        const timeBonus = Math.max(0, 15 - timeTaken) * 10;
        const difficultyBonus = error.difficulty === 'easy' ? 50 : error.difficulty === 'medium' ? 100 : 150;
        pointsEarned = 100 + timeBonus + difficultyBonus;
        score += pointsEarned;
    }

    document.getElementById('score').textContent = score;
    document.getElementById('correctCount').textContent = correctCount;

    if (sessionId && window.GameAPI) {
        GameAPI.saveAttempt(sessionId, error.challenge_id, isCorrect, pointsEarned, timeTaken);
    }

    showSidePopup(isCorrect, pointsEarned, error);

    document.getElementById('nextBtn').disabled = false;
}

function showSidePopup(isCorrect, pointsEarned, error) {
    const popup = document.getElementById('sidePopup');
    const popupIcon = document.getElementById('popupIcon');
    const popupTitle = document.getElementById('popupTitle');
    const popupContent = document.getElementById('popupContent');

    popup.className = 'side-popup';

    if (isCorrect) {
        popup.classList.add('correct');
        popupIcon.textContent = '✓';
        popupTitle.textContent = 'Correct!';
        popupContent.innerHTML = `<div class="popup-points">+${pointsEarned} points</div>`;
    } else {
        popup.classList.add('wrong');
        popupIcon.textContent = '✕';
        popupTitle.textContent = 'Not quite right!';
        popupContent.innerHTML = `
                    <div class="popup-explanation-text">${error.explanation}</div>
                    <div class="popup-code-example">${error.example}</div>
                `;
    }

    setTimeout(() => {
        popup.classList.add('show');
    }, 100);
}

function closeSidePopup() {
    const popup = document.getElementById('sidePopup');
    popup.classList.remove('show');
}

function nextError() {
    closeSidePopup();
    currentErrorIndex++;
    loadError();
    startTimer();
}

function endGame() {
    clearInterval(timerInterval);

    const accuracy = Math.round((correctCount / errors.length) * 100);
    const avgTime = Math.round(totalTime / errors.length);

    let message = '';
    if (accuracy >= 90) message = '🏆 Master Debugger! You know your errors!';
    else if (accuracy >= 70) message = '😎 Great job! Solid debugging skills!';
    else if (accuracy >= 50) message = '👍 Good effort! Keep practicing!';
    else message = '💪 Keep learning! Debugging takes practice!';

    document.getElementById('resultMessage').textContent = message;
    document.getElementById('finalScore').textContent = score;
    document.getElementById('finalCorrect').textContent = `${correctCount}/${errors.length}`;
    document.getElementById('finalAccuracy').textContent = `${accuracy}%`;
    document.getElementById('avgTime').textContent = `${avgTime}s`;

    if (sessionId && window.GameAPI) {
        const totalDuration = Math.round((Date.now() - startTime) / 1000);
        const result = accuracy >= 50 ? 'WIN' : 'LOSS';
        GameAPI.endSession(sessionId, score, totalDuration, accuracy, result);
    }

    document.getElementById('resultModal').classList.add('show');
}