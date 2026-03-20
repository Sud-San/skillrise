@echo off
REM CodeArena - Start Server Script for Windows

echo.
echo Starting CodeArena Server...
echo.

REM Check if we're in the right directory
if not exist "index.html" (
    echo ERROR: index.html not found!
    echo Please run this script from the codearena directory:
    echo   cd codearena
    echo   start-server.bat
    pause
    exit /b 1
)

REM Check if css/style.css exists
if not exist "css\style.css" (
    echo ERROR: css\style.css not found!
    echo File structure is incorrect.
    pause
    exit /b 1
)

echo Files found. Starting PHP server...
echo.
echo Open your browser and go to:
echo    http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.

php -S localhost:8000
pause
