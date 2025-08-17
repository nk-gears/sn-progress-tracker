#!/bin/bash

# Meditation Tracker PHP Backend Server Startup Script
# This script starts the PHP development server and sets up the database

echo "🧘‍♂️ Starting Meditation Tracker PHP Backend Server"
echo "=================================================="

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
PORT=8080
HOST=localhost
DOCUMENT_ROOT="$(pwd)"

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check PHP installation
echo -e "${YELLOW}Checking PHP installation...${NC}"
if ! command_exists php; then
    echo -e "${RED}❌ PHP is not installed or not in PATH${NC}"
    echo "Please install PHP 7.4+ with the following extensions:"
    echo "- php-mysql (or php-mysqli)"
    echo "- php-pdo"
    echo "- php-json"
    echo ""
    echo "Installation commands:"
    echo "macOS (Homebrew): brew install php"
    echo "Ubuntu/Debian: sudo apt install php php-mysql php-json"
    echo "Windows: Download from php.net"
    exit 1
fi

PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
echo -e "${GREEN}✅ PHP $PHP_VERSION detected${NC}"

# Check required PHP extensions
echo -e "${YELLOW}Checking PHP extensions...${NC}"
REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "json")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -q "^$ext$"; then
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -ne 0 ]; then
    echo -e "${RED}❌ Missing PHP extensions: ${MISSING_EXTENSIONS[*]}${NC}"
    echo "Please install the missing extensions and restart."
    exit 1
fi

echo -e "${GREEN}✅ All required PHP extensions found${NC}"

# Check MySQL connection
echo -e "${YELLOW}Checking database connection...${NC}"
php -r "
try {
    \$pdo = new PDO('mysql:host=192.168.1.13;dbname=medi-tracker;charset=utf8mb4', 'mediuser', 'mediuser123!');
    echo '✅ Database connection successful' . PHP_EOL;
} catch (PDOException \$e) {
    echo '❌ Database connection failed: ' . \$e->getMessage() . PHP_EOL;
    echo 'Please check:' . PHP_EOL;
    echo '1. MySQL server is running on 192.168.1.13' . PHP_EOL;
    echo '2. Database \"medi-tracker\" exists' . PHP_EOL;
    echo '3. User \"mediuser\" has proper permissions' . PHP_EOL;
    echo '4. Network connectivity to database server' . PHP_EOL;
    exit(1);
}
"

if [ $? -ne 0 ]; then
    echo -e "${RED}Database connection check failed. Please fix the issues above.${NC}"
    exit 1
fi

# Check if port is available
echo -e "${YELLOW}Checking if port $PORT is available...${NC}"
if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null ; then
    echo -e "${RED}❌ Port $PORT is already in use${NC}"
    echo "Please stop the service using port $PORT or choose a different port:"
    echo "sudo lsof -i :$PORT"
    echo "kill -9 <PID>"
    exit 1
fi

echo -e "${GREEN}✅ Port $PORT is available${NC}"

# Create .htaccess for URL rewriting (if needed)
if [ ! -f ".htaccess" ]; then
    echo -e "${YELLOW}Creating .htaccess for clean URLs...${NC}"
    cat > .htaccess << 'EOF'
# Enable URL Rewriting
RewriteEngine On

# Handle CORS preflight requests
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ $1 [R=200,L]

# API routing (if you want clean URLs without .php)
# RewriteCond %{REQUEST_FILENAME} !-f
# RewriteCond %{REQUEST_FILENAME} !-d
# RewriteRule ^api/([^/]+)/?$ $1.php [L,QSA]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
EOF
    echo -e "${GREEN}✅ Created .htaccess${NC}"
fi

# Start the PHP development server
echo ""
echo -e "${GREEN}🚀 Starting PHP development server...${NC}"
echo -e "${GREEN}   Server URL: http://$HOST:$PORT${NC}"
echo -e "${GREEN}   Document Root: $DOCUMENT_ROOT${NC}"
echo ""
echo -e "${YELLOW}Available API endpoints:${NC}"
echo "   POST http://$HOST:$PORT/auth.php"
echo "   GET  http://$HOST:$PORT/participants.php"
echo "   POST http://$HOST:$PORT/participants.php"
echo "   GET  http://$HOST:$PORT/sessions.php"
echo "   POST http://$HOST:$PORT/sessions.php"
echo "   PUT  http://$HOST:$PORT/sessions.php"
echo "   DEL  http://$HOST:$PORT/sessions.php"
echo "   GET  http://$HOST:$PORT/dashboard.php"
echo ""
echo -e "${YELLOW}Swagger Documentation:${NC}"
echo "   Open: ../api-docs/index.html in your browser"
echo "   Or visit: https://editor.swagger.io (paste ../api-docs/swagger.yaml)"
echo ""
echo -e "${YELLOW}Demo credentials:${NC}"
echo "   Mobile: 9283181228"
echo "   Password: meditation123"
echo ""
echo -e "${GREEN}Press Ctrl+C to stop the server${NC}"
echo ""

# Start the server
php -S $HOST:$PORT -t $DOCUMENT_ROOT

echo -e "${YELLOW}Server stopped.${NC}"