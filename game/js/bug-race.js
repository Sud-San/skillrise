function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
    }
    shuffleArray(bugs);
    let currentBug = 0;
    let score = 0;
    let bugsFixed = 0;
    let combo = 0;
    let maxCombo = 0;
    let timeRemaining = 120;
    let timerInterval;
    let bugTimes = [];
    let isPlaying = false;
    let gameStartTime;
    let currentBugStartTime;
    let correctAnswers = 0;
    let totalAttempts = 0;
    let sessionId = null;

    window.addEventListener("load", () => {
        setTimeout(() => {
            document.getElementById("preloader").classList.add("hidden");
            setupGame();
        }, 500);
    });

    function setupGame() {
        console.log("✓ Game setup complete");
        document.getElementById("startBtn").addEventListener("click", startGame);
    }

    async function startGame() {
        console.log("🏁 Game started!");
        
        // Start API session (Bug Race ID: 7)
        sessionId = await GameAPI.startSession(7);
        console.log("Session ID:", sessionId);

        document.getElementById("startCard").style.display = "none";
        document.getElementById("challengeCard").style.display = "block";
        isPlaying = true;
        gameStartTime = Date.now();
        currentBugStartTime = Date.now();

        timerInterval = setInterval(() => {
            timeRemaining--;
            updateTimer();
            if (timeRemaining <= 0) endGame();
        }, 1000);

        loadBug();
    }

    function updateTimer() {
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        document.getElementById("raceTimer").textContent =
            `${minutes}:${seconds.toString().padStart(2, "0")}`;
    }

    function loadBug() {
        if (currentBug >= bugs.length) {
            currentBug = 0; // Loop back
        }

        currentBugStartTime = Date.now();
        const bug = bugs[currentBug];
        document.getElementById("codeBlock").textContent = bug.code;

        const optionsContainer = document.getElementById("answerOptions");
        optionsContainer.innerHTML = "";

        bug.options.forEach((option, index) => {
            const btn = document.createElement("button");
            btn.className = "option-btn";
            btn.textContent = option;
            btn.onclick = () => checkAnswer(index);
            optionsContainer.appendChild(btn);
        });

        document
            .getElementById("explanationBox")
            .classList.remove("show", "incorrect");
        console.log(`Bug #${currentBug + 1} loaded`);
    }

    function checkAnswer(selectedIndex) {
        const bug = bugs[currentBug];
        const isCorrect = selectedIndex === bug.correct;
        const bugSolveTime = (Date.now() - currentBugStartTime) / 1000;

        totalAttempts++;

        const buttons = document.querySelectorAll(".option-btn");
        buttons.forEach((btn, idx) => {
            btn.disabled = true;
            if (idx === bug.correct) btn.classList.add("correct");
            else if (idx === selectedIndex) btn.classList.add("incorrect");
        });

        if (isCorrect) {
            bugsFixed++;
            correctAnswers++;
            combo++;
            maxCombo = Math.max(maxCombo, combo);

            // Time bonus: faster = more points
            let timeBonus = 1;
            if (bugSolveTime < 5) timeBonus = 1.5;
            else if (bugSolveTime < 10) timeBonus = 1.2;

            score += Math.floor(100 * combo * timeBonus);
            bugTimes.push(bugSolveTime);
            showFeedback(true, bug.explanation);
            console.log(`✓ Bug fixed! Score: ${score}, Combo: ${combo}x`);
        } else {
            combo = 0;
            showFeedback(false, bug.explanation);
            console.log("✗ Wrong answer! Combo reset");
        }

        // Save attempt to API
        if (sessionId) {
            GameAPI.saveAttempt(sessionId, bug.challenge_id, isCorrect, score, bugSolveTime);
        }

        updateStats();

        setTimeout(() => {
            currentBug++;
            loadBug();
        }, 2000);
    }

    function showFeedback(isCorrect, explanation) {
        const box = document.getElementById("explanationBox");
        const title = document.getElementById("feedbackTitle");
        const text = document.getElementById("explanationText");

        title.textContent = isCorrect ? "✓ Fixed!" : "✗ Not quite";
        title.style.color = isCorrect ? "var(--color-success)" : "var(--color-error)";
        text.textContent = explanation;

        box.classList.toggle("incorrect", !isCorrect);
        box.classList.add("show");
    }

    function updateStats() {
        document.getElementById("bugsFixed").textContent = bugsFixed;
        document.getElementById("score").textContent = score;
        document.getElementById("combo").textContent = combo + "x";
    }

    async function endGame() {
        console.log("🏁 Game ended!");
        isPlaying = false;
        clearInterval(timerInterval);

        const totalTime = 120 - timeRemaining;
        const avgTime =
            bugsFixed > 0 ? bugTimes.reduce((a, b) => a + b, 0) / bugsFixed : 0;
        const accuracy =
            totalAttempts > 0 ? (correctAnswers / totalAttempts) * 100 : 0;

        // Update UI
        document.getElementById("finalScore").textContent = score;
        document.getElementById("finalBugs").textContent = bugsFixed;
        document.getElementById("finalCombo").textContent = maxCombo + "x";
        document.getElementById("avgTime").textContent = avgTime.toFixed(1) + "s";

        const message =
            score >= 1000 ?
                "🔥 Bug Destroyer!" :
                score >= 500 ?
                    "💪 Great debugging!" :
                    "🐛 Keep practicing!";
        document.getElementById("resultMessage").textContent = message;

        document.getElementById("resultModal").classList.add("show");

        // Save to database
        const saveStatus = document.getElementById("saveStatus");
        saveStatus.style.display = "block";
        saveStatus.textContent = "💾 Saving your score...";
        saveStatus.className = "save-status";

        if (sessionId) {
            const resultStatus = accuracy >= 80 ? 'WIN' : (accuracy >= 50 ? 'LOSS' : 'INCOMPLETE');
            const saveResult = await GameAPI.endSession(sessionId, score, totalTime, accuracy, resultStatus);

            if (saveResult && saveResult.status === "completed") {
                saveStatus.textContent = "✓ Score saved successfully!";
                saveStatus.classList.add("success");
                console.log("✓ Save successful!", saveResult);
            } else {
                saveStatus.textContent = "✗ Failed to save score.";
                saveStatus.classList.add("error");
            }
        } else {
            saveStatus.textContent = "✗ No session found.";
            saveStatus.classList.add("error");
        }
    }
    document.getElementById("hintBtn").addEventListener("click", () => {
        const hint =
            bugs[currentBug].hint || "Look closely at operators or conditions 👀";
        document.getElementById("hintText").textContent = hint;
        document.getElementById("hintModal").classList.add("show");
    });

    function closeHint() {
        document.getElementById("hintModal").classList.remove("show");
    }