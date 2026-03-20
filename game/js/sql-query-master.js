const hardcodedChallenges = [
    {
        "schema": {
            "users": [
                "id INT",
                "name VARCHAR(100)",
                "email VARCHAR(100)",
                "age INT",
                "city VARCHAR(50)"
            ]
        },
        "difficulty": "Easy",
        "explanation": "SELECT * retrieves all columns from a table.",
        "instruction": "Select all columns from the users table",
        "acceptedAnswers": [
            "SELECT * FROM users",
            "SELECT * FROM users;",
            "select * from users",
            "select * from users;"
        ]
    }
];

// Combine challenges and ensure they follow the new format
const challenges = [...hardcodedChallenges, ...fetchedChallenges].map(c => {
    // Normalize fetching from DB if needed
    return {
        difficulty: c.difficulty || 'Medium',
        instruction: c.instruction || '',
        schema: c.schema || {},
        acceptedAnswers: c.acceptedAnswers || c.answers || [],
        explanation: c.explanation || ''
    };
});

let currentChallenge = 0;
let score = 0;
let correctCount = 0;
let timeRemaining = 300;
let timerInterval;
let startTime;
let sessionId = null;

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}
// Only shuffle if there are multiple
if (challenges.length > 1) shuffleArray(challenges);

window.addEventListener('load', () => {
    setTimeout(() => {
        document.getElementById('preloader').classList.add('hidden');
        initGame();
    }, 500);
});

async function initGame() {
    startTime = Date.now();
    
    // Start API session (SQL Query Master ID: 6)
    sessionId = await GameAPI.startSession(6);
    console.log("Session ID:", sessionId);

    document.getElementById('totalChallenges').textContent = challenges.length;
    startTimer();
    loadChallenge();
    setupEventListeners();
}

function setupEventListeners() {
    document.getElementById('submitBtn').addEventListener('click', submitQuery);
    document.getElementById('skipBtn').addEventListener('click', skipChallenge);

    document.getElementById('queryEditor').addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'Enter') {
            submitQuery();
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
    if (currentChallenge >= challenges.length) {
        endGame();
        return;
    }

    const challenge = challenges[currentChallenge];

    document.getElementById('currentChallenge').textContent = currentChallenge + 1;
    document.getElementById('challengeTitle').textContent = `Challenge ${currentChallenge + 1}`;
    document.getElementById('difficulty').textContent = challenge.difficulty;
    document.getElementById('difficulty').className = `game-difficulty difficulty-${challenge.difficulty.toLowerCase()}`;
    document.getElementById('instruction').textContent = challenge.instruction;

    const schemaContent = document.getElementById('schemaContent');
    schemaContent.innerHTML = '';

    for (const [tableName, columns] of Object.entries(challenge.schema)) {
        const tableDiv = document.createElement('div');
        tableDiv.className = 'table-schema';

        // Support both array of strings and array of objects if needed, 
        // but here we expect array of strings based on user input
        const columnHtml = Array.isArray(columns)
            ? columns.map(col => `- ${col}`).join('<br>')
            : '';

        tableDiv.innerHTML = `
                    <div class="table-name">${tableName}</div>
                    <div class="column-list">${columnHtml}</div>
                `;
        schemaContent.appendChild(tableDiv);
    }

    document.getElementById('queryEditor').value = '';
    document.getElementById('queryEditor').focus();
    document.getElementById('explanationBox').classList.remove('show', 'incorrect');

    updateProgress();
}

function normalizeQuery(query) {
    return query.toLowerCase()
        .replace(/\s+/g, ' ')
        .replace(/;$/, '')
        .trim();
}

function submitQuery() {
    if (currentChallenge >= challenges.length) return;

    const challenge = challenges[currentChallenge];
    const userQuery = document.getElementById('queryEditor').value.trim();

    if (!userQuery) return;

    const normalizedUserQuery = normalizeQuery(userQuery);
    const isCorrect = challenge.acceptedAnswers.some(answer =>
        normalizeQuery(answer) === normalizedUserQuery
    );

    if (isCorrect) {
        correctCount++;
        const points = challenge.difficulty === 'Easy' ? 100 : challenge.difficulty === 'Medium' ? 150 : 200;
        score += points;
        showFeedback(true, challenge.explanation);
    } else {
        showFeedback(false, `Expected: ${challenge.acceptedAnswers[0]}. ${challenge.explanation}`);
    }

    // Save attempt to API
    if (sessionId) {
        const timeTaken = (Date.now() - startTime) / 1000; // Total time since start of session for this attempt
        GameAPI.saveAttempt(sessionId, challenge.challenge_id, isCorrect, score, timeTaken);
    }

    updateScore();

    setTimeout(() => {
        currentChallenge++;
        loadChallenge();
    }, isCorrect ? 2500 : 4000);
}

function showFeedback(isCorrect, explanation) {
    const box = document.getElementById('explanationBox');
    const title = document.getElementById('feedbackTitle');
    const text = document.getElementById('explanationText');

    title.textContent = isCorrect ? '✓ Correct!' : '✗ Incorrect';
    title.style.color = isCorrect ? 'var(--color-success)' : 'var(--color-error)';
    text.textContent = explanation;

    box.classList.toggle('incorrect', !isCorrect);
    box.classList.add('show');
}

function skipChallenge() {
    currentChallenge++;
    loadChallenge();
    updateScore();
}

function updateScore() {
    document.getElementById('score').textContent = score;
}

function updateProgress() {
    const progress = (currentChallenge / challenges.length) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
}

function endGame() {
    clearInterval(timerInterval);

    const totalTime = Math.floor((Date.now() - startTime) / 1000);
    const accuracy = challenges.length > 0 ? Math.round((correctCount / challenges.length) * 100) : 0;

    document.getElementById('finalScore').textContent = score;
    document.getElementById('accuracy').textContent = `${accuracy}%`;

    const minutes = Math.floor(totalTime / 60);
    const seconds = totalTime % 60;
    document.getElementById('timeTaken').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('queriesWritten').textContent = `${correctCount}/${challenges.length}`;

    const message = score >= 1000 ? '🔥 SQL Master!' :
        score >= 600 ? '💪 Great querying!' :
            '📚 Keep learning!';
    document.getElementById('resultMessage').textContent = message;

    if (sessionId && window.GameAPI) {
        const result = accuracy >= 50 ? 'WIN' : 'LOSS';
        GameAPI.endSession(sessionId, score, totalTime, accuracy, result);
    }

    document.getElementById('resultModal').classList.add('show');
}