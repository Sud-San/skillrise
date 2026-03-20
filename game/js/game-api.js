/**
 * GameAPI - Client-side utility for interacting with the CodeArena Game API
 */
const GameAPI = {
    baseUrl: '../api/',

    /**
     * Start a new game session
     */
    async startSession(gameId, language = 'mixed') {
        const formData = new FormData();
        formData.append('game_id', gameId);
        formData.append('language', language);

        try {
            const response = await fetch(`${this.baseUrl}start_session.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            return data.session_id;
        } catch (error) {
            console.error('Error starting session:', error);
            return null;
        }
    },

    /**
     * Record a challenge attempt
     */
    async saveAttempt(sessionId, challengeId, isCorrect, score, timeTaken) {
        const formData = new FormData();
        formData.append('session_id', sessionId);
        formData.append('challenge_id', challengeId);
        formData.append('is_correct', isCorrect ? 1 : 0);
        formData.append('score', score);
        formData.append('time_taken', timeTaken);

        try {
            const response = await fetch(`${this.baseUrl}save_attempt.php`, {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('Error saving attempt:', error);
            return null;
        }
    },

    /**
     * End the current game session
     */
    async endSession(sessionId, finalScore, timeTaken, accuracy, result) {
        const formData = new FormData();
        formData.append('session_id', sessionId);
        formData.append('final_score', finalScore);
        formData.append('time_taken', timeTaken);
        formData.append('accuracy', accuracy);
        formData.append('result', result); // 'WIN', 'LOSS', 'INCOMPLETE'

        try {
            const response = await fetch(`${this.baseUrl}end_session.php`, {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('Error ending session:', error);
            return null;
        }
    }
};

window.GameAPI = GameAPI;
