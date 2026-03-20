function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}
shuffleArray(challenges);
let currentChallengeIndex = 0;
let score = 0;
let hintsUsed = 0;
let timeRemaining = 600; // 10 minutes
let timerInterval;
let sessionId = null;
let startTime;
let popupTimeout;

// Side popup functions
function showSidePopup(message) {
    const popup = document.getElementById('sidePopup');
    const messageEl = document.getElementById('popupMessage');
    messageEl.textContent = message;
    popup.classList.add('show');

    // Clear any existing timeout
    if (popupTimeout) {
        clearTimeout(popupTimeout);
    }

    // Auto-hide after 4 seconds
    popupTimeout = setTimeout(() => {
        closeSidePopup();
    }, 4000);
}

function closeSidePopup() {
    const popup = document.getElementById('sidePopup');
    popup.classList.remove('show');
    if (popupTimeout) {
        clearTimeout(popupTimeout);
    }
}

window.addEventListener('load', () => {
    setTimeout(() => {
        document.getElementById('preloader').classList.add('hidden');
        initGame();
    }, 500);
});

async function initGame() {
    startTime = Date.now();
    renderProgressTracker();
    loadChallenge();
    startTimer();

    if (window.GameAPI) {
        sessionId = await GameAPI.startSession(9); // 9: Escape the Server Room
    }

    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'Enter') {
            const submitBtn = document.querySelector('.submit-btn');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.click();
            } else {
                const nextBtn = document.getElementById('nextBtn'); // If there's one elsewhere
                if (nextBtn && !nextBtn.disabled) {
                    nextBtn.click();
                }
            }
        }
    });
}

function renderProgressTracker() {
    const tracker = document.getElementById('progressTracker');
    tracker.innerHTML = '';
    challenges.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.className = 'progress-dot';
        if (index < currentChallengeIndex) dot.classList.add('completed');
        if (index === currentChallengeIndex) dot.classList.add('active');
        tracker.appendChild(dot);
    });
}

function startTimer() {
    timerInterval = setInterval(() => {
        timeRemaining--;
        updateTimerDisplay();

        if (timeRemaining <= 0) {
            endGame(false, 'Time\'s up! The server room stays locked.');
        }
    }, 1000);
}

function updateTimerDisplay() {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    const display = document.getElementById('timerDisplay');
    display.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

    if (timeRemaining <= 60) {
        display.classList.add('warning');
    }
}

function loadChallenge() {
    const challenge = challenges[currentChallengeIndex];
    const container = document.getElementById('challengeContainer');

    document.getElementById('currentChallenge').textContent = currentChallengeIndex + 1;
    document.getElementById('progressBar').style.width = `${((currentChallengeIndex) / challenges.length) * 100}%`;

    let html = `
                    <div class="challenge-header">
                        <div class="challenge-number">${challenge.title}</div>
                        <button class="hint-btn top-right" onclick="showHint()">💡 Hint</button>
                    </div>

                `;

    if (challenge.code) {
        html += `<div class="code-preview">${escapeHtml(challenge.code)}</div>`;
    }

    if (challenge.options) {
        html += '<div class="options-grid">';
        challenge.options.forEach((option, index) => {
            html += `<button class="option-btn" onclick="checkOption(${index})">${option}</button>`;
        });
        html += '</div>';
    } else if (challenge.answer) {
        html += `
                        <div class="code-editor">
                            <textarea id="codeInput" placeholder="Write your solution here...">${challenge.code || ''}</textarea>
                        </div>
                        <button class="submit-btn" onclick="checkCode()">Submit Solution</button>
                    `;
    } else if (challenge.expectedCode) {
        html += `
                        <div class="code-editor">
                            <textarea id="codeInput" placeholder="function isEven(num) {
                                     // Your code here
                                  }"></textarea>
                        </div>
                        <button class="submit-btn" onclick="checkQuickScript()">Submit Solution</button>
                    `;
    }

    html += `
                   
                `;

    container.innerHTML = html;
}

function showHint() {
    const challenge = challenges[currentChallengeIndex];
    const hintBox = document.getElementById('hintBox');
    hintBox.style.display = 'block';
    hintBox.innerHTML = `<strong>💡 Hint:</strong> ${challenge.hint}`;
    hintsUsed++;
    score = Math.max(0, score - 50);
    document.getElementById('hintsUsed').textContent = hintsUsed;
    document.getElementById('score').textContent = score;
}

function checkOption(selectedIndex) {
    const challenge = challenges[currentChallengeIndex];
    const buttons = document.querySelectorAll('.option-btn');

    buttons.forEach((btn, index) => {
        btn.disabled = true;
        if (index === challenge.correctIndex) {
            btn.classList.add('correct');
        } else if (index === selectedIndex && selectedIndex !== challenge.correctIndex) {
            btn.classList.add('wrong');
        }
    });

    if (selectedIndex === challenge.correctIndex) {
        score += 200;
        document.getElementById('score').textContent = score;
        
        // Save attempt
        if (sessionId && window.GameAPI) {
            GameAPI.saveAttempt(sessionId, challenge.challenge_id, true, 200, 0);
        }
        
        setTimeout(nextChallenge, 1500);
    } else {
        score = Math.max(0, score - 50);
        document.getElementById('score').textContent = score;
        
        // Save attempt
        if (sessionId && window.GameAPI) {
            GameAPI.saveAttempt(sessionId, challenge.challenge_id, false, 0, 0);
        }
        
        setTimeout(() => {
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('wrong');
            });
        }, 1500);
    }
}

function checkCode() {
    const challenge = challenges[currentChallengeIndex];
    const userCode = document.getElementById('codeInput').value.trim();
    const expectedCode = challenge.answer.trim();

    const normalized1 = userCode.replace(/\s+/g, '');
    const normalized2 = expectedCode.replace(/\s+/g, '');

    if (normalized1 === normalized2) {
        score += 200;
        document.getElementById('score').textContent = score;
        
        // Save attempt
        if (sessionId && window.GameAPI) {
            GameAPI.saveAttempt(sessionId, challenge.challenge_id, true, 200, 0);
        }
        
        setTimeout(nextChallenge, 1000);
    } else {
        score = Math.max(0, score - 30);
        document.getElementById('score').textContent = score;
        
        // Save attempt
        if (sessionId && window.GameAPI) {
            GameAPI.saveAttempt(sessionId, challenge.challenge_id, false, 0, 0);
        }
        
        showSidePopup('Not quite right. Check your solution and try again!');
    }
}

function checkQuickScript() {
    const userCode = document.getElementById('codeInput').value.trim();
    const challenge = challenges[currentChallengeIndex];

    if (userCode.includes(challenge.expectedCode) ||
        userCode.includes('num%2===0') ||
        userCode.includes('num%2==0')) {
        score += 200;
        document.getElementById('score').textContent = score;
        
        // Save attempt
        if (sessionId && window.GameAPI) {
            GameAPI.saveAttempt(sessionId, challenge.challenge_id, true, 200, 0);
        }
        
        setTimeout(nextChallenge, 1000);
    } else {
        score = Math.max(0, score - 30);
        document.getElementById('score').textContent = score;
        
        // Save attempt
        if (sessionId && window.GameAPI) {
            GameAPI.saveAttempt(sessionId, challenge.challenge_id, false, 0, 0);
        }
        
        showSidePopup('Not quite right. Think about how to check if a number is divisible by 2.');
    }
}

function nextChallenge() {
    currentChallengeIndex++;
    renderProgressTracker();

    if (currentChallengeIndex >= challenges.length) {
        endGame(true, '🎉 Amazing! You escaped the server room!');
    } else {
        loadChallenge();
    }
}

function endGame(success, message) {
    clearInterval(timerInterval);

    const timeUsed = 600 - timeRemaining;
    const minutes = Math.floor(timeUsed / 60);
    const seconds = timeUsed % 60;

    if (success) {
        score += Math.floor(timeRemaining * 2); // Bonus for remaining time
    }

    document.getElementById('resultIcon').textContent = success ? '🎉' : '⏰';
    document.getElementById('resultTitle').textContent = success ? 'Escaped!' : 'Time\'s Up!';
    document.getElementById('resultMessage').textContent = message;
    document.getElementById('finalScore').textContent = score;
    document.getElementById('timeUsed').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('challengesSolved').textContent = `${currentChallengeIndex}/${challenges.length}`;
    document.getElementById('finalHints').textContent = hintsUsed;

    if (sessionId && window.GameAPI) {
        const accuracy = Math.round((currentChallengeIndex / challenges.length) * 100);
        const result = success ? 'WIN' : 'LOSS';
        GameAPI.endSession(sessionId, score, timeUsed, accuracy, result);
    }

    document.getElementById('resultModal').classList.add('show');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}  