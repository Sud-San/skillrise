function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
    }
    shuffleArray(memes);

    let currentMemeIndex = 0;
    let score = 0;
    let streak = 0;
    let bestStreak = 0;
    let correctCount = 0;
    let timePerMeme = 15;
    let timeRemaining = timePerMeme;
    let timerInterval;
    let sessionId = null;
    let startTime;

    window.addEventListener('load', () => {
        setTimeout(async () => {
            document.getElementById('preloader').classList.add('hidden');
            startTime = Date.now();
            if (window.GameAPI) {
                sessionId = await GameAPI.startSession(11); // 11: Code Meme Guess
            }
            loadMeme();
            startTimer();
        }, 500);
    });

    function loadMeme() {
        if (currentMemeIndex >= memes.length) {
            endGame();
            return;
        }

        const meme = memes[currentMemeIndex];
        const container = document.getElementById('memeContainer');

        container.innerHTML = `
                <div class="meme-emoji">${meme.emoji}</div>
                <div class="meme-text">${meme.text.replace(/\n/g, '<br>')}</div>
                <div class="meme-caption">${meme.caption}</div>
            `;

        const optionsContainer = document.getElementById('optionsContainer');
        optionsContainer.innerHTML = '';

        meme.options.forEach((option, index) => {
            const optionCard = document.createElement('div');
            optionCard.className = 'option-card';
            optionCard.textContent = option;
            optionCard.onclick = () => selectAnswer(index);
            optionsContainer.appendChild(optionCard);
        });

        document.getElementById('currentMeme').textContent = currentMemeIndex + 1;
        document.getElementById('progressBar').style.width = `${((currentMemeIndex + 1) / memes.length) * 100}%`;

        timeRemaining = timePerMeme;
    }

    function startTimer() {
        timerInterval = setInterval(() => {
            timeRemaining -= 0.1;
            const percentage = (timeRemaining / timePerMeme) * 100;
            const timerFill = document.getElementById('timerFill');
            timerFill.style.width = `${percentage}%`;

            if (percentage < 30) {
                timerFill.classList.add('warning');
            } else {
                timerFill.classList.remove('warning');
            }

            if (timeRemaining <= 0) {
                skipMeme();
            }
        }, 100);
    }

    function selectAnswer(selectedIndex) {
        clearInterval(timerInterval);

        const meme = memes[currentMemeIndex];
        const options = document.querySelectorAll('.option-card');

        options.forEach((option, index) => {
            option.onclick = null;
            if (index === meme.correct) {
                option.classList.add('correct');
            } else if (index === selectedIndex) {
                option.classList.add('wrong');
            }
        });

        const isCorrect = selectedIndex === meme.correct;

        if (isCorrect) {
            correctCount++;
            streak++;
            bestStreak = Math.max(bestStreak, streak);

            const timeBonus = Math.floor(timeRemaining * 10);
            const streakBonus = streak * 50;
            const pointsEarned = 200 + timeBonus + streakBonus;
            score += pointsEarned;

            document.getElementById('streak').textContent = `${streak}🔥`;

            // Show correct feedback popup
            showFeedbackPopup(true, `+${pointsEarned} points`, meme.explanation);
        } else {
            streak = 0;
            document.getElementById('streak').textContent = '0';

            // Show incorrect feedback popup
            showFeedbackPopup(false, '', meme.explanation);
        }

        document.getElementById('score').textContent = score;

        if (sessionId && window.GameAPI) {
            const timeTaken = timePerMeme - timeRemaining;
            const points = isCorrect ? 200 : 0;
            GameAPI.saveAttempt(sessionId, meme.challenge_id, isCorrect, points, timeTaken);
        }

        setTimeout(() => {
            closeFeedbackPopup();
            currentMemeIndex++;
            loadMeme();
            startTimer();
        }, 3000);
    }

    function showFeedbackPopup(isCorrect, points, explanation) {
        const popup = document.getElementById('feedbackPopup');
        const iconEl = document.getElementById('feedbackIcon');
        const textEl = document.getElementById('feedbackText');
        const pointsEl = document.getElementById('feedbackPoints');
        const explanationEl = document.getElementById('explanationText');

        iconEl.textContent = isCorrect ? '✓' : '✗';
        iconEl.classList.toggle('incorrect', !isCorrect);

        textEl.textContent = isCorrect ? 'Correct!' : 'Not quite right!';
        textEl.classList.toggle('incorrect', !isCorrect);

        if (isCorrect && points) {
            pointsEl.textContent = points;
            pointsEl.style.display = 'inline-block';
        } else {
            pointsEl.style.display = 'none';
        }

        explanationEl.textContent = explanation;

        popup.classList.toggle('incorrect', !isCorrect);
        popup.classList.add('show');
    }

    function closeFeedbackPopup() {
        const popup = document.getElementById('feedbackPopup');
        popup.classList.remove('show');
        popup.classList.remove('incorrect');
    }

    function skipMeme() {
        clearInterval(timerInterval);
        streak = 0;
        document.getElementById('streak').textContent = '0';

        currentMemeIndex++;
        loadMeme();
        startTimer();
    }

    function endGame() {
        clearInterval(timerInterval);

        const accuracy = Math.round((correctCount / memes.length) * 100);
        let message = '';

        if (accuracy >= 90) message = '🏆 Meme Legend! You know your dev humor!';
        else if (accuracy >= 70) message = '😎 Great job! You\'re a meme master!';
        else if (accuracy >= 50) message = '👍 Not bad! Keep practicing!';
        else message = '😅 Need more meme exposure!';

        document.getElementById('resultMessage').textContent = message;
        document.getElementById('finalScore').textContent = score;
        document.getElementById('correctAnswers').textContent = `${correctCount}/${memes.length}`;
        document.getElementById('bestStreak').textContent = bestStreak;
        document.getElementById('accuracy').textContent = `${accuracy}%`;

        if (sessionId && window.GameAPI) {
            const totalTime = Math.floor((Date.now() - startTime) / 1000);
            const result = accuracy >= 50 ? 'WIN' : 'LOSS';
            GameAPI.endSession(sessionId, score, totalTime, accuracy, result);
        }

        document.getElementById('resultModal').classList.add('show');
    }