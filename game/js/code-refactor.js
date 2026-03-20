/**
 * Code Refactor Challenge - Game Logic
 * Optimize messy code into clean, efficient code
 */

// const challenges = [
//     {
//         language: 'JavaScript',
//         difficulty: 'Easy',
//         messyCode: `function calc(a,b,c){
// var x=a+b;
// var y=x+c;
// return y;
// }`,
//         cleanCode: `function sum(a, b, c) {
//   return a + b + c;
// }`,
//         issues: [
//             { icon: '📝', title: 'Poor Naming', desc: 'Use descriptive function and variable names' },
//             { icon: '➖', title: 'Unnecessary Variables', desc: 'Remove intermediate variables when not needed' },
//             { icon: '🎨', title: 'Formatting', desc: 'Add proper spacing and indentation' }
//         ],
//         hint: 'Combine all operations into a single return statement. Name the function based on what it does.',
//         metrics: { originalLines: 4, targetLines: 3 }
//     },
//     {
//         language: 'Python',
//         difficulty: 'Easy',
//         messyCode: `def check(n):
//     if n%2==0:
//         return True
//     else:
//         return False`,
//         cleanCode: `def is_even(n):
//     return n % 2 == 0`,
//         issues: [
//             { icon: '📝', title: 'Poor Naming', desc: 'Function name should indicate it checks for even numbers' },
//             { icon: '🔄', title: 'Redundant Logic', desc: 'No need for if-else when returning boolean' },
//             { icon: '🎨', title: 'Spacing', desc: 'Add spaces around operators' }
//         ],
//         hint: 'The condition itself is already a boolean. Just return it directly!',
//         metrics: { originalLines: 5, targetLines: 2 }
//     },
//     {
//         language: 'JavaScript',
//         difficulty: 'Medium',
//         messyCode: `var arr=[1,2,3,4,5];
// var result=[];
// for(var i=0;i<arr.length;i++){
//   result.push(arr[i]*2);
// }
// console.log(result);`,
//         cleanCode: `const numbers = [1, 2, 3, 4, 5];
// const doubled = numbers.map(n => n * 2);
// console.log(doubled);`,
//         issues: [
//             { icon: '🔧', title: 'Modern Syntax', desc: 'Use const/let instead of var' },
//             { icon: '🚀', title: 'Array Methods', desc: 'Use .map() instead of manual loop' },
//             { icon: '📝', title: 'Naming', desc: 'Use descriptive variable names' },
//             { icon: '🎨', title: 'Spacing', desc: 'Add spaces after commas and around operators' }
//         ],
//         hint: 'Array.map() is perfect for transforming arrays. Arrow functions make it concise.',
//         metrics: { originalLines: 6, targetLines: 3 }
//     },
//     {
//         language: 'Python',
//         difficulty: 'Medium',
//         messyCode: `def find(lst,val):
//     for i in range(len(lst)):
//         if lst[i]==val:
//             return True
//     return False`,
//         cleanCode: `def contains(items, value):
//     return value in items`,
//         issues: [
//             { icon: '🔧', title: 'Built-in Operators', desc: 'Use "in" operator instead of manual loop' },
//             { icon: '📝', title: 'Naming', desc: 'Better variable names (items, value instead of lst, val)' },
//             { icon: '🎨', title: 'Spacing', desc: 'Add spaces around operators' }
//         ],
//         hint: 'Python has a built-in "in" operator for checking membership!',
//         metrics: { originalLines: 5, targetLines: 2 }
//     },
//     {
//         language: 'JavaScript',
//         difficulty: 'Medium',
//         messyCode: `function getUser(id){
//   var user=null;
//   if(id){
//     user=database.find(id);
//   }
//   if(user){
//     return user;
//   }else{
//     return null;
//   }
// }`,
//         cleanCode: `function getUser(id) {
//   return id ? database.find(id) : null;
// }`,
//         issues: [
//             { icon: '➖', title: 'Unnecessary Variable', desc: 'No need for user variable' },
//             { icon: '🔄', title: 'Nested Conditions', desc: 'Simplify with ternary operator' },
//             { icon: '🎨', title: 'Spacing', desc: 'Add proper formatting' }
//         ],
//         hint: 'Use the ternary operator (condition ? true : false) for simple if-else returns.',
//         metrics: { originalLines: 10, targetLines: 3 }
//     },
//     {
//         language: 'Python',
//         difficulty: 'Hard',
//         messyCode: `def process(data):
//     result=[]
//     for item in data:
//         if item>0:
//             if item%2==0:
//                 result.append(item*2)
//     return result`,
//         cleanCode: `def process(data):
//     return [item * 2 for item in data if item > 0 and item % 2 == 0]`,
//         issues: [
//             { icon: '🚀', title: 'List Comprehension', desc: 'Use list comprehension for filtering and mapping' },
//             { icon: '🔄', title: 'Nested If', desc: 'Combine conditions with "and"' },
//             { icon: '🎨', title: 'Spacing', desc: 'Add spaces around operators' }
//         ],
//         hint: 'List comprehensions can filter and transform in one line: [expr for item in list if condition]',
//         metrics: { originalLines: 7, targetLines: 2 }
//     },
//     {
//         language: 'JavaScript',
//         difficulty: 'Hard',
//         messyCode: `function getData(url,callback){
//   fetch(url).then(function(response){
//     return response.json();
//   }).then(function(data){
//     callback(null,data);
//   }).catch(function(error){
//     callback(error,null);
//   });
// }`,
//         cleanCode: `async function getData(url) {
//   try {
//     const response = await fetch(url);
//     return await response.json();
//   } catch (error) {
//     throw error;
//   }
// }`,
//         issues: [
//             { icon: '🚀', title: 'Async/Await', desc: 'Use modern async/await instead of callbacks' },
//             { icon: '🔧', title: 'Arrow Functions', desc: 'Use arrow functions for cleaner syntax' },
//             { icon: '📝', title: 'Error Handling', desc: 'Let errors propagate naturally with try/catch' }
//         ],
//         hint: 'async/await makes asynchronous code look synchronous and more readable.',
//         metrics: { originalLines: 9, targetLines: 7 }
//     },
//     {
//         language: 'Python',
//         difficulty: 'Hard',
//         messyCode: `def get_names(users):
//     names=[]
//     for user in users:
//         if user['active']==True:
//             names.append(user['name'])
//     return names`,
//         cleanCode: `def get_active_names(users):
//     return [user['name'] for user in users if user['active']]`,
//         issues: [
//             { icon: '🚀', title: 'List Comprehension', desc: 'Use list comprehension' },
//             { icon: '📝', title: 'Function Name', desc: 'Name should reflect it gets active users only' },
//             { icon: '🔧', title: 'Boolean Check', desc: 'No need to compare with True' }
//         ],
//         hint: 'List comprehension with filtering is perfect here. Boolean values are truthy by default.',
//         metrics: { originalLines: 6, targetLines: 2 }
//     },
//     {
//         language: 'JavaScript',
//         difficulty: 'Hard',
//         messyCode: `function total(arr){
//   var sum=0;
//   for(var i=0;i<arr.length;i++){
//     sum=sum+arr[i];
//   }
//   return sum;
// }`,
//         cleanCode: `function sum(numbers) {
//   return numbers.reduce((total, n) => total + n, 0);
// }`,
//         issues: [
//             { icon: '🚀', title: 'Array Methods', desc: 'Use .reduce() for accumulating values' },
//             { icon: '📝', title: 'Naming', desc: 'Better function and variable names' },
//             { icon: '🔧', title: 'Modern Syntax', desc: 'Use const and arrow functions' }
//         ],
//         hint: 'The reduce() method is designed for reducing an array to a single value.',
//         metrics: { originalLines: 6, targetLines: 3 }
//     },
//     {
//         language: 'Python',
//         difficulty: 'Hard',
//         messyCode: `def validate(user):
//     if user.get('email'):
//         if '@' in user['email']:
//             if user.get('age'):
//                 if user['age']>=18:
//                     return True
//     return False`,
//         cleanCode: `def is_valid_user(user):
//     return (user.get('email') and '@' in user['email'] and 
//             user.get('age', 0) >= 18)`,
//         issues: [
//             { icon: '🔄', title: 'Nested Conditions', desc: 'Flatten nested if statements' },
//             { icon: '🔧', title: 'Boolean Logic', desc: 'Use "and" to combine conditions' },
//             { icon: '📝', title: 'Naming', desc: 'Function name should indicate validation' }
//         ],
//         hint: 'Combine all conditions with "and" operators for a single, readable return statement.',
//         metrics: { originalLines: 8, targetLines: 3 }
//     }
// ];

// Merge fetched challenges from DB
if (typeof fetchedRefactorsFromDB !== 'undefined') {
    fetchedRefactorsFromDB.forEach(dbChallenge => {
        challenges.push(dbChallenge);
    });
}

// Game State
let currentChallengeIndex = 0;
let score = 0;
let successfulRefactors = 0;
let totalQuality = 0;
let scoreManager;
let sessionId = null;
let startTime;

// Initialize
window.addEventListener('load', () => {
    const terminalCommands = ['npm run start', 'webpack --mode production', 'docker-compose up -d'];
    const loadingStatuses = ['INITIALIZING', 'CONNECTING', 'COMPILING', 'LOADING ASSETS', 'OPTIMIZING', 'FINALIZING', 'READY'];
    let currentPercent = 0, targetPercent = 0, statusIndex = 0;

    const commandEl = document.getElementById('loadingCommand');
    if (commandEl) commandEl.textContent = terminalCommands[Math.floor(Math.random() * terminalCommands.length)];

    const percentInterval = setInterval(() => {
        if (currentPercent < 100) {
            targetPercent = Math.min(targetPercent + Math.random() * 15, 100);
            currentPercent += (targetPercent - currentPercent) * 0.1;
            const percent = Math.floor(currentPercent);
            const percentEl = document.getElementById('loadingPercentage');
            const barFill = document.getElementById('loadingBarFill');
            if (percentEl) percentEl.textContent = percent + '%';
            if (barFill) barFill.style.width = percent + '%';
        }
    }, 50);

    const statusInterval = setInterval(() => {
        if (statusIndex < loadingStatuses.length) {
            const statusEl = document.getElementById('loadingStatus');
            if (statusEl) statusEl.textContent = loadingStatuses[statusIndex++];
        }
    }, 400);

    setTimeout(() => {
        clearInterval(percentInterval);
        clearInterval(statusInterval);
        const preloader = document.getElementById('preloader');
        if (preloader) preloader.classList.add('hidden');
        initGame();
    }, 3000);
});

async function initGame() {
    startTime = Date.now();
    scoreManager = new ScoreManager();
    setupEventListeners();
    loadChallenge();
    
    if (window.GameAPI) {
        sessionId = await GameAPI.startSession(8); // 8: Code Refactor Challenge
    }
}

function setupEventListeners() {
    document.getElementById('submitBtn').addEventListener('click', checkRefactor);
    document.getElementById('resetBtn').addEventListener('click', resetCode);
    document.getElementById('showHintBtn').addEventListener('click', showHint);
    document.getElementById('refactoredCode').addEventListener('input', updateMetrics);
    document.getElementById('refactoredCode').addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'Enter') {
            checkRefactor();
        }
    });
}

function loadChallenge() {
    if (currentChallengeIndex >= challenges.length) {
        endGame();
        return;
    }

    const challenge = challenges[currentChallengeIndex];
    
    // Update UI
    document.getElementById('currentChallenge').textContent = currentChallengeIndex + 1;
    document.getElementById('totalChallenges').textContent = challenges.length;
    document.getElementById('messyCode').textContent = challenge.messyCode;
    document.getElementById('refactoredCode').value = '';
    document.getElementById('hintBox').classList.remove('show');
    
    // Load issues
    const issuesList = document.getElementById('issuesList');
    issuesList.innerHTML = challenge.issues.map(issue => `
        <div class="issue-item">
            <div class="issue-icon">${issue.icon}</div>
            <div class="issue-content">
                <div class="issue-title">${issue.title}</div>
                <div class="issue-description">${issue.desc}</div>
            </div>
        </div>
    `).join('');
    
    document.getElementById('totalIssues').textContent = challenge.issues.length;
    document.getElementById('issuesFixed').textContent = '0';
    
    updateProgress();
    updateMetrics();
}

function updateMetrics() {
    const userCode = document.getElementById('refactoredCode').value;
    const lines = userCode.trim().split('\n').filter(line => line.trim()).length;
    
    document.getElementById('linesCount').textContent = lines || 0;
    
    // Update comparison
    const challenge = challenges[currentChallengeIndex];
    const comparison = document.getElementById('linesComparison');
    const diff = challenge.metrics.originalLines - lines;
    
    if (lines === 0) {
        comparison.innerHTML = '';
    } else if (diff > 0) {
        comparison.innerHTML = `<span class="indicator-arrow indicator-good">↓</span> ${diff} lines reduced`;
        comparison.className = 'comparison-indicator indicator-good';
    } else if (diff < 0) {
        comparison.innerHTML = `<span class="indicator-arrow indicator-bad">↑</span> ${Math.abs(diff)} lines added`;
        comparison.className = 'comparison-indicator indicator-bad';
    } else {
        comparison.innerHTML = `<span>→</span> Same line count`;
        comparison.className = 'comparison-indicator';
    }
}

function normalizeCode(code) {
    return code
        .trim()
        .replace(/\s+/g, ' ')
        .replace(/\s*([{}()\[\],;])\s*/g, '$1')
        .toLowerCase();
}

function calculateQuality(userCode, challenge) {
    let quality = 0;
    const normalized = normalizeCode(userCode);
    const targetNormalized = normalizeCode(challenge.cleanCode);
    
    // Check similarity
    if (normalized === targetNormalized) {
        quality = 100;
    } else {
        // Partial credit for improvements
        const userLines = userCode.trim().split('\n').filter(l => l.trim()).length;
        const targetLines = challenge.metrics.targetLines;
        const originalLines = challenge.metrics.originalLines;
        
        // Line reduction quality (0-50)
        const lineReduction = Math.max(0, originalLines - userLines);
        const maxReduction = originalLines - targetLines;
        quality += Math.min(50, (lineReduction / maxReduction) * 50);
        
        // Code similarity (0-50)
        const similarity = calculateSimilarity(normalized, targetNormalized);
        quality += similarity * 50;
    }
    
    return Math.round(quality);
}

function calculateSimilarity(str1, str2) {
    const longer = str1.length > str2.length ? str1 : str2;
    const shorter = str1.length > str2.length ? str2 : str1;
    
    if (longer.length === 0) return 1.0;
    
    const editDistance = levenshteinDistance(longer, shorter);
    return (longer.length - editDistance) / longer.length;
}

function levenshteinDistance(str1, str2) {
    const matrix = [];
    
    for (let i = 0; i <= str2.length; i++) {
        matrix[i] = [i];
    }
    
    for (let j = 0; j <= str1.length; j++) {
        matrix[0][j] = j;
    }
    
    for (let i = 1; i <= str2.length; i++) {
        for (let j = 1; j <= str1.length; j++) {
            if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j] + 1
                );
            }
        }
    }
    
    return matrix[str2.length][str1.length];
}

function checkRefactor() {
    const userCode = document.getElementById('refactoredCode').value.trim();
    
    if (!userCode) {
        UIFeedback.showError('Please write some refactored code!');
        return;
    }
    
    const challenge = challenges[currentChallengeIndex];
    const quality = calculateQuality(userCode, challenge);
    
    document.getElementById('qualityScore').textContent = quality + '%';
    
    let issuesFixed = 0;
    if (quality >= 90) issuesFixed = challenge.issues.length;
    else if (quality >= 70) issuesFixed = Math.floor(challenge.issues.length * 0.7);
    else if (quality >= 50) issuesFixed = Math.floor(challenge.issues.length * 0.5);
    else if (quality >= 30) issuesFixed = 1;
    
    document.getElementById('issuesFixed').textContent = issuesFixed;
    
    // Calculate score
    let points = 0;
    if (quality >= 90) {
        points = 500;
        UIFeedback.showSuccess(`🎯 Perfect Refactor! +${points} points`);
        UIFeedback.confetti();
        successfulRefactors++;
    } else if (quality >= 70) {
        points = 350;
        UIFeedback.showSuccess(`⭐ Great Refactor! +${points} points`);
        successfulRefactors++;
    } else if (quality >= 50) {
        points = 200;
        UIFeedback.showInfo(`✓ Good Effort! +${points} points`);
    } else {
        points = 50;
        UIFeedback.showWarning(`💪 Keep Trying! +${points} points`);
    }
    
    scoreManager.addPoints(points, true);
    totalQuality += quality;
    
    // Save attempt
    if (sessionId && window.GameAPI) {
        GameAPI.saveAttempt(sessionId, challenge.challenge_id, quality >= 50, points, 0);
    }
    
    setTimeout(() => {
        currentChallengeIndex++;
        loadChallenge();
    }, 2500);
}

function resetCode() {
    document.getElementById('refactoredCode').value = '';
    updateMetrics();
    UIFeedback.showInfo('Code reset!');
}

function showHint() {
    const challenge = challenges[currentChallengeIndex];
    document.getElementById('hintText').textContent = challenge.hint;
    document.getElementById('hintBox').classList.add('show');
}

function updateProgress() {
    const progress = Math.round((currentChallengeIndex / challenges.length) * 100);
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('score').textContent = scoreManager.getScore();
    document.getElementById('refactorCount').textContent = successfulRefactors;
}

function endGame() {
    const avgQuality = currentChallengeIndex > 0 ? Math.round(totalQuality / currentChallengeIndex) : 0;
    const completion = Math.round((currentChallengeIndex / challenges.length) * 100);
    
    document.getElementById('resultIcon').textContent = avgQuality >= 90 ? '🏆' : avgQuality >= 70 ? '⭐' : '💪';
    document.getElementById('resultTitle').textContent = avgQuality >= 90 ? 'Refactoring Master!' : 
                                                         avgQuality >= 70 ? 'Great Work!' : 'Keep Practicing!';
    document.getElementById('resultMessage').textContent = avgQuality >= 90 ? 
        'You\'re a code optimization expert!' :
        avgQuality >= 70 ?
        'Excellent refactoring skills!' :
        'Keep learning and improving!';
    
    document.getElementById('finalScore').textContent = scoreManager.getScore();
    document.getElementById('totalRefactors').textContent = successfulRefactors;
    document.getElementById('avgQuality').textContent = avgQuality + '%';
    document.getElementById('completionRate').textContent = completion + '%';
    
    if (sessionId && window.GameAPI) {
        const totalTime = Math.floor((Date.now() - startTime) / 1000);
        const result = avgQuality >= 50 ? 'WIN' : 'LOSS';
        GameAPI.endSession(sessionId, scoreManager.getScore(), totalTime, avgQuality, result);
    }

    document.getElementById('resultModal').classList.add('show');
    
    if (avgQuality >= 90) {
        UIFeedback.confetti();
    }
}