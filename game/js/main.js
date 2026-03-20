/**
 * CodeArena - Main JavaScript
 * Core functionality and utilities
 */

// ============================================
// Preloader Management
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Hide preloader after page loads
    const preloader = document.getElementById('preloader');
    if (preloader) {
        setTimeout(() => {
            preloader.classList.add('hidden');
        }, 500);
    }

    // Initialize animations
    initScrollAnimations();
    
    // Initialize navigation
    initNavigation();
});

// ============================================
// Navigation Management
// ============================================
function initNavigation() {
    // Highlight active navigation link
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage || (currentPage === '' && linkPage === 'index.html')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// ============================================
// Scroll Animations
// ============================================
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    animatedElements.forEach(el => observer.observe(el));
}

// ============================================
// Timer Utility
// ============================================
class GameTimer {
    constructor(duration, onTick, onComplete) {
        this.duration = duration;
        this.remaining = duration;
        this.onTick = onTick;
        this.onComplete = onComplete;
        this.interval = null;
        this.isPaused = false;
    }

    start() {
        this.interval = setInterval(() => {
            if (!this.isPaused) {
                this.remaining--;
                this.onTick(this.remaining);
                
                if (this.remaining <= 0) {
                    this.stop();
                    this.onComplete();
                }
            }
        }, 1000);
    }

    pause() {
        this.isPaused = true;
    }

    resume() {
        this.isPaused = false;
    }

    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }

    reset() {
        this.stop();
        this.remaining = this.duration;
    }

    getRemaining() {
        return this.remaining;
    }

    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
}

// ============================================
// Score Management
// ============================================
class ScoreManager {
    constructor() {
        this.currentScore = 0;
        this.multiplier = 1;
        this.streak = 0;
    }

    addPoints(points, isCorrect = true) {
        if (isCorrect) {
            const earnedPoints = Math.floor(points * this.multiplier);
            this.currentScore += earnedPoints;
            this.incrementStreak();
            this.updateMultiplier();
            return earnedPoints;
        } else {
            this.resetStreak();
            return 0;
        }
    }

    incrementStreak() {
        this.streak++;
    }

    resetStreak() {
        this.streak = 0;
        this.multiplier = 1;
    }

    updateMultiplier() {
        if (this.streak >= 10) {
            this.multiplier = 3;
        } else if (this.streak >= 5) {
            this.multiplier = 2;
        } else if (this.streak >= 3) {
            this.multiplier = 1.5;
        } else {
            this.multiplier = 1;
        }
    }

    getScore() {
        return this.currentScore;
    }

    getStreak() {
        return this.streak;
    }

    getMultiplier() {
        return this.multiplier;
    }

    reset() {
        this.currentScore = 0;
        this.multiplier = 1;
        this.streak = 0;
    }
}

// ============================================
// Progress Tracking
// ============================================
class ProgressTracker {
    constructor(totalChallenges) {
        this.totalChallenges = totalChallenges;
        this.completedChallenges = 0;
        this.correctAnswers = 0;
    }

    incrementProgress(isCorrect = true) {
        this.completedChallenges++;
        if (isCorrect) {
            this.correctAnswers++;
        }
    }

    getProgress() {
        return {
            completed: this.completedChallenges,
            total: this.totalChallenges,
            percentage: (this.completedChallenges / this.totalChallenges) * 100,
            accuracy: this.completedChallenges > 0 ? 
                (this.correctAnswers / this.completedChallenges) * 100 : 0
        };
    }

    isComplete() {
        return this.completedChallenges >= this.totalChallenges;
    }

    reset() {
        this.completedChallenges = 0;
        this.correctAnswers = 0;
    }
}

// ============================================
// UI Feedback & Animations
// ============================================
const UIFeedback = {
    showSuccess(message, duration = 3000) {
        this.showNotification(message, 'success', duration);
    },

    showError(message, duration = 3000) {
        this.showNotification(message, 'error', duration);
    },

    showInfo(message, duration = 3000) {
        this.showNotification(message, 'info', duration);
    },

    showNotification(message, type, duration) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? 'var(--accent-green)' : 
                         type === 'error' ? 'var(--accent-red)' : 
                         'var(--accent-blue)'};
            color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            font-weight: 600;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, duration);
    },

    highlightElement(element, color = 'var(--accent-blue)') {
        const originalBorder = element.style.border;
        element.style.border = `2px solid ${color}`;
        element.style.transition = 'border 0.3s ease';
        
        setTimeout(() => {
            element.style.border = originalBorder;
        }, 1000);
    },

    shakeElement(element) {
        element.style.animation = 'shake 0.5s ease';
        setTimeout(() => {
            element.style.animation = '';
        }, 500);
    },

    pulseElement(element) {
        element.style.animation = 'pulse 1s ease';
        setTimeout(() => {
            element.style.animation = '';
        }, 1000);
    }
};

// Add CSS animations to the document
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);

// ============================================
// Local Storage Utilities
// ============================================
const Storage = {
    save(key, value) {
        try {
            localStorage.setItem(`codearena_${key}`, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('Error saving to localStorage:', e);
            return false;
        }
    },

    load(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(`codearena_${key}`);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.error('Error loading from localStorage:', e);
            return defaultValue;
        }
    },

    remove(key) {
        try {
            localStorage.removeItem(`codearena_${key}`);
            return true;
        } catch (e) {
            console.error('Error removing from localStorage:', e);
            return false;
        }
    },

    clear() {
        try {
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith('codearena_')) {
                    localStorage.removeItem(key);
                }
            });
            return true;
        } catch (e) {
            console.error('Error clearing localStorage:', e);
            return false;
        }
    }
};

// ============================================
// Form Validation
// ============================================
const Validator = {
    isEmpty(value) {
        return !value || value.trim() === '';
    },

    isEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    isMinLength(value, minLength) {
        return value.length >= minLength;
    },

    isMaxLength(value, maxLength) {
        return value.length <= maxLength;
    },

    isAlphanumeric(value) {
        const re = /^[a-zA-Z0-9]+$/;
        return re.test(value);
    },

    validateForm(formData, rules) {
        const errors = {};
        
        for (const field in rules) {
            const value = formData[field];
            const fieldRules = rules[field];
            
            if (fieldRules.required && this.isEmpty(value)) {
                errors[field] = `${field} is required`;
                continue;
            }
            
            if (fieldRules.email && !this.isEmail(value)) {
                errors[field] = 'Invalid email address';
            }
            
            if (fieldRules.minLength && !this.isMinLength(value, fieldRules.minLength)) {
                errors[field] = `Minimum length is ${fieldRules.minLength}`;
            }
            
            if (fieldRules.maxLength && !this.isMaxLength(value, fieldRules.maxLength)) {
                errors[field] = `Maximum length is ${fieldRules.maxLength}`;
            }
            
            if (fieldRules.alphanumeric && !this.isAlphanumeric(value)) {
                errors[field] = 'Only letters and numbers allowed';
            }
        }
        
        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    }
};

// ============================================
// API Communication
// ============================================
const API = {
    async request(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(endpoint, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'API request failed');
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    async get(endpoint) {
        return this.request(endpoint, 'GET');
    },

    async post(endpoint, data) {
        return this.request(endpoint, 'POST', data);
    },

    async put(endpoint, data) {
        return this.request(endpoint, 'PUT', data);
    },

    async delete(endpoint) {
        return this.request(endpoint, 'DELETE');
    }
};

// ============================================
// Game Session Manager
// ============================================
class GameSession {
    constructor(gameId, gameName) {
        this.gameId = gameId;
        this.gameName = gameName;
        this.startTime = Date.now();
        this.scoreManager = new ScoreManager();
        this.sessionId = null;
        this.sessionData = {
            challenges: [],
            language: null,
            gameId: gameId
        };
        
        // Auto-start session if GameAPI is available
        if (window.GameAPI) {
            this.startApiSession();
        }
    }

    async startApiSession() {
        try {
            this.sessionId = await GameAPI.startSession(this.gameId, this.sessionData.language || 'mixed');
            console.log(`[GameSession] Started API session: ${this.sessionId}`);
        } catch (error) {
            console.error('[GameSession] Error starting API session:', error);
        }
    }

    recordAnswer(challengeId, answer, isCorrect, timeTaken) {
        this.sessionData.challenges.push({
            challengeId,
            answer,
            isCorrect,
            timeTaken,
            timestamp: Date.now()
        });

        // Save attempt to API if session is active
        if (this.sessionId && window.GameAPI) {
            const score = isCorrect ? 100 : 0; // Default score logic
            GameAPI.saveAttempt(this.sessionId, challengeId, isCorrect, score, timeTaken);
        }
    }

    setLanguage(language) {
        this.sessionData.language = language;
        // If session already started, we can't easily change it in start_session.php 
        // but we can track it locally.
    }

    getSessionDuration() {
        return Math.floor((Date.now() - this.startTime) / 1000);
    }

    async saveSession() {
        const score = this.scoreManager.getScore();
        const duration = this.getSessionDuration();
        const accuracy = this.calculateAccuracy();

        const sessionData = {
            gameId: this.gameId,
            gameName: this.gameName,
            score: score,
            duration: duration,
            language: this.sessionData.language,
            challenges: this.sessionData.challenges,
            completedAt: new Date().toISOString()
        };

        try {
            // Save to new API if session is active
            if (this.sessionId && window.GameAPI) {
                const result = accuracy >= 50 ? 'WIN' : 'LOSS';
                await GameAPI.endSession(this.sessionId, score, duration, accuracy, result);
                console.log('[GameSession] API session ended successfully');
            } else {
                // Fallback to old API if needed or if no session
                await API.post('php/save-session.php', sessionData);
            }
            
            // Also save locally for offline access
            Storage.save(`session_${this.gameId}_${Date.now()}`, sessionData);
            
            return sessionData;
        } catch (error) {
            console.error('Error saving session:', error);
            Storage.save(`session_${this.gameId}_${Date.now()}`, sessionData);
            throw error;
        }
    }

    calculateAccuracy() {
        if (this.sessionData.challenges.length === 0) return 0;
        const correct = this.sessionData.challenges.filter(c => c.isCorrect).length;
        return Math.round((correct / this.sessionData.challenges.length) * 100);
    }
}

// ============================================
// Utility Functions
// ============================================
function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function shuffleArray(array) {
    const shuffled = [...array];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// ============================================
// Export utilities for use in other scripts
// ============================================
window.CodeArena = {
    GameTimer,
    ScoreManager,
    ProgressTracker,
    UIFeedback,
    Storage,
    Validator,
    API,
    GameSession,
    formatTime,
    formatNumber,
    shuffleArray,
    debounce,
    escapeHtml
};
