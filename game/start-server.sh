#!/bin/bash
# CodeArena - Start Server Script

echo "🚀 Starting CodeArena Server..."
echo ""
echo "Make sure you run this script FROM INSIDE the codearena folder!"
echo ""

# Check if we're in the right directory
if [ ! -f "index.html" ]; then
    echo "❌ ERROR: index.html not found!"
    echo "Please run this script from the codearena directory:"
    echo "  cd codearena"
    echo "  ./start-server.sh"
    exit 1
fi

# Check if css/style.css exists
if [ ! -f "css/style.css" ]; then
    echo "❌ ERROR: css/style.css not found!"
    echo "File structure is incorrect."
    exit 1
fi

echo "✅ Files found. Starting PHP server..."
echo ""
echo "🌐 Open your browser and go to:"
echo "   http://localhost:8000"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

php -S localhost:8000
