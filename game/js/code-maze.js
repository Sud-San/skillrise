/**
 * Code Maze Game Logic
 * Navigate through mazes using code commands
 */

// 0 = wall, 1 = path, 2 = start, 3 = goal

// Merge fetched levels from DB
if (typeof fetchedLevelsFromDB !== 'undefined') {
    fetchedLevelsFromDB.forEach(dbLevel => {
        levels.push(dbLevel);
    });
}

// Game State
let currentLevel = 0;
let playerPos = { row: 0, col: 0 };
let goalPos = { row: 0, col: 0 };
let moves = [];
let totalMoves = 0;
let visitedCells = new Set();
let scoreManager;
let levelScores = [];
let sessionId = null;
let startTime;

// Initialize
window.addEventListener('load', () => {
    setTimeout(() => {
        document.getElementById('preloader').classList.add('hidden');
        initGame();
    }, 500);
});

async function initGame() {
    startTime = Date.now();
    scoreManager = new ScoreManager();
    setupEventListeners();
    loadLevel(currentLevel);
    
    if (window.GameAPI) {
        sessionId = await GameAPI.startSession(5); // 5: Code Maze
    }
}

function setupEventListeners() {
    document.getElementById('moveUpBtn').addEventListener('click', () => move('UP'));
    document.getElementById('moveDownBtn').addEventListener('click', () => move('DOWN'));
    document.getElementById('moveLeftBtn').addEventListener('click', () => move('LEFT'));
    document.getElementById('moveRightBtn').addEventListener('click', () => move('RIGHT'));
    document.getElementById('resetBtn').addEventListener('click', () => resetLevel());
    document.getElementById('skipBtn').addEventListener('click', () => skipLevel());
    
    // Keyboard controls
    document.addEventListener('keydown', (e) => {
        switch(e.key) {
            case 'ArrowUp': case 'w': case 'W':
                e.preventDefault();
                move('UP');
                break;
            case 'ArrowDown': case 's': case 'S':
                e.preventDefault();
                move('DOWN');
                break;
            case 'ArrowLeft': case 'a': case 'A':
                e.preventDefault();
                move('LEFT');
                break;
            case 'ArrowRight': case 'd': case 'D':
                e.preventDefault();
                move('RIGHT');
                break;
            case 'r': case 'R':
                e.preventDefault();
                resetLevel();
                break;
        }
    });
}

function loadLevel(levelIndex) {
    if (levelIndex >= levels.length) {
        endGame();
        return;
    }
    
    const level = levels[levelIndex];
    moves = [];
    visitedCells.clear();
    
    // Update UI
    document.getElementById('levelTitle').textContent = `Level ${levelIndex + 1}: ${level.name}`;
    document.getElementById('levelDescription').textContent = level.description;
    document.getElementById('currentLevel').textContent = levelIndex + 1;
    document.getElementById('totalLevels').textContent = levels.length;
    document.getElementById('movesCount').textContent = '0';
    
    // Find start and goal positions
    for (let row = 0; row < level.grid.length; row++) {
        for (let col = 0; col < level.grid[row].length; col++) {
            if (level.grid[row][col] === 2) {
                playerPos = { row, col };
            }
            if (level.grid[row][col] === 3) {
                goalPos = { row, col };
            }
        }
    }
    
    visitedCells.add(`${playerPos.row},${playerPos.col}`);
    renderMaze();
    updateCodeDisplay();
    updateProgress();
}

function renderMaze() {
    const level = levels[currentLevel];
    const mazeGrid = document.getElementById('mazeGrid');
    mazeGrid.innerHTML = '';
    
    mazeGrid.style.gridTemplateColumns = `repeat(${level.gridSize.cols}, 50px)`;
    mazeGrid.style.gridTemplateRows = `repeat(${level.gridSize.rows}, 50px)`;
    
    for (let row = 0; row < level.grid.length; row++) {
        for (let col = 0; col < level.grid[row].length; col++) {
            const cell = document.createElement('div');
            cell.className = 'maze-cell';
            
            const cellValue = level.grid[row][col];
            
            if (cellValue === 0) {
                cell.classList.add('wall');
                cell.textContent = '🧱';
            } else if (cellValue === 1) {
                cell.classList.add('path');
                if (visitedCells.has(`${row},${col}`)) {
                    cell.classList.add('visited');
                }
            } else if (cellValue === 2) {
                cell.classList.add('start');
                cell.textContent = '🏁';
            } else if (cellValue === 3) {
                cell.classList.add('goal');
                cell.textContent = '🎯';
            }
            
            // Add player
            if (row === playerPos.row && col === playerPos.col) {
                cell.classList.add('player');
                cell.textContent = '🤖';
            }
            
            mazeGrid.appendChild(cell);
        }
    }
}

function move(direction) {
    const level = levels[currentLevel];
    let newRow = playerPos.row;
    let newCol = playerPos.col;
    
    switch(direction) {
        case 'UP': newRow--; break;
        case 'DOWN': newRow++; break;
        case 'LEFT': newCol--; break;
        case 'RIGHT': newCol++; break;
    }
    
    // Check bounds
    if (newRow < 0 || newRow >= level.grid.length || 
        newCol < 0 || newCol >= level.grid[0].length) {
        UIFeedback.showError('Cannot move outside the maze!');
        return;
    }
    
    // Check if wall
    if (level.grid[newRow][newCol] === 0) {
        UIFeedback.showError('Cannot move into a wall!');
        return;
    }
    
    // Valid move
    playerPos = { row: newRow, col: newCol };
    moves.push(direction);
    totalMoves++;
    visitedCells.add(`${newRow},${newCol}`);
    
    document.getElementById('movesCount').textContent = moves.length;
    
    renderMaze();
    updateCodeDisplay();
    
    // Check if reached goal
    if (newRow === goalPos.row && newCol === goalPos.col) {
        levelComplete();
    }
}

function updateCodeDisplay() {
    const codeLines = document.getElementById('codeLines');
    codeLines.innerHTML = '';
    
    if (moves.length === 0) {
        codeLines.innerHTML = '<div style="color: var(--text-muted); font-style: italic;">// No moves yet</div>';
        return;
    }
    
    moves.forEach((move, index) => {
        const line = document.createElement('div');
        line.className = 'code-line-item';
        line.textContent = `${index + 1}. MOVE_${move}()`;
        codeLines.appendChild(line);
    });
}

function levelComplete() {
    const level = levels[currentLevel];
    const moveCount = moves.length;
    const optimal = level.optimalMoves;
    
    // Calculate score
    let points = 200;
    if (moveCount === optimal) {
        points = 500; // Perfect!
    } else if (moveCount <= optimal + 2) {
        points = 350; // Great
    } else if (moveCount <= optimal + 5) {
        points = 250; // Good
    }
    
    scoreManager.addPoints(points, true);
    levelScores.push({ level: currentLevel, moves: moveCount, optimal: optimal, points: points });
    
    // Show feedback
    const efficiency = Math.round((optimal / moveCount) * 100);
    let message = '';
    
    if (moveCount === optimal) {
        message = `🎯 Perfect! Optimal solution! +${points} points`;
        UIFeedback.confetti();
    } else if (moveCount <= optimal + 2) {
        message = `⭐ Great job! ${efficiency}% efficient! +${points} points`;
    } else {
        message = `✓ Level complete! +${points} points`;
    }
    
    // Save attempt
    if (sessionId && window.GameAPI) {
        GameAPI.saveAttempt(sessionId, level.challenge_id, true, points, 0);
    }
    
    UIFeedback.showSuccess(message);
    
    // Move to next level
    setTimeout(() => {
        currentLevel++;
        if (currentLevel < levels.length) {
            loadLevel(currentLevel);
        } else {
            endGame();
        }
    }, 2000);
}

function resetLevel() {
    loadLevel(currentLevel);
    UIFeedback.showInfo('Level reset!');
}

function skipLevel() {
    currentLevel++;
    if (currentLevel < levels.length) {
        loadLevel(currentLevel);
        UIFeedback.showInfo('Level skipped');
    } else {
        endGame();
    }
}

function updateProgress() {
    const progress = Math.round((currentLevel / levels.length) * 100);
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('score').textContent = scoreManager.getScore();
}

function endGame() {
    const totalOptimalMoves = levels.reduce((sum, level) => sum + level.optimalMoves, 0);
    const efficiency = totalMoves > 0 ? Math.round((totalOptimalMoves / totalMoves) * 100) : 0;
    
    const results = {
        score: scoreManager.getScore(),
        totalMoves: totalMoves,
        levelsCompleted: levelScores.length,
        totalLevels: levels.length,
        efficiency: efficiency,
        message: levelScores.length === levels.length && efficiency >= 90 ? 
                'Master Navigator! Perfect efficiency!' :
                levelScores.length === levels.length ? 
                'All levels completed! Well done!' :
                'Good effort! Try to complete more levels!'
    };
    
    // Show result modal
    document.getElementById('resultIcon').textContent = results.levelsCompleted === levels.length && efficiency >= 90 ? '🏆' : 
                                                        results.levelsCompleted === levels.length ? '⭐' : '💪';
    document.getElementById('resultTitle').textContent = results.levelsCompleted === levels.length ? 
                                                         'Maze Mastered!' : 'Game Over';
    document.getElementById('resultMessage').textContent = results.message;
    document.getElementById('finalScore').textContent = results.score;
    document.getElementById('totalMoves').textContent = results.totalMoves;
    document.getElementById('levelsCompleted').textContent = `${results.levelsCompleted}/${results.totalLevels}`;
    document.getElementById('efficiency').textContent = `${results.efficiency}%`;
    
    if (sessionId && window.GameAPI) {
        const totalTime = Math.floor((Date.now() - startTime) / 1000);
        const result = results.levelsCompleted === levels.length ? 'WIN' : 'LOSS';
        GameAPI.endSession(sessionId, results.score, totalTime, results.efficiency, result);
    }

    document.getElementById('resultModal').classList.add('show');
    
    if (results.levelsCompleted === levels.length && efficiency >= 90) {
        UIFeedback.confetti();
    }
}
