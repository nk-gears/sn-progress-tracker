#!/bin/bash

# Simple MySQLi Backend Server (No PDO Required!)
# This uses MySQLi extension which is usually available by default

echo "🧘‍♂️ Starting Meditation Tracker Backend (MySQLi Version)"
echo "======================================================"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

PORT=8081
HOST=localhost

# Check PHP
echo -e "${YELLOW}Checking PHP installation...${NC}"
if ! command -v php >/dev/null 2>&1; then
    echo -e "${RED}❌ PHP is not installed${NC}"
    echo "Please install PHP first:"
    echo "macOS: brew install php"
    echo "Ubuntu: sudo apt install php"
    exit 1
fi

PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
echo -e "${GREEN}✅ PHP $PHP_VERSION found${NC}"

# Check MySQLi extension (usually available by default)
echo -e "${YELLOW}Checking MySQLi extension...${NC}"
if ! php -m | grep -q "^mysqli$"; then
    echo -e "${RED}❌ MySQLi extension not found${NC}"
    echo "Install MySQLi extension:"
    echo "macOS: Usually included with PHP"
    echo "Ubuntu: sudo apt install php-mysql"
    echo "Windows: Enable in php.ini"
    exit 1
fi

echo -e "${GREEN}✅ MySQLi extension found${NC}"

# Test database connection
echo -e "${YELLOW}Testing database connection...${NC}"
php -r "
\$mysqli = new mysqli('192.168.1.13', 'mediuser', 'mediuser123!', 'medi-tracker');
if (\$mysqli->connect_error) {
    echo '❌ Database connection failed: ' . \$mysqli->connect_error . PHP_EOL;
    echo 'Please check:' . PHP_EOL;
    echo '1. MySQL server is running on 192.168.1.13' . PHP_EOL;
    echo '2. Database medi-tracker exists' . PHP_EOL;
    echo '3. User mediuser has proper permissions' . PHP_EOL;
    exit(1);
} else {
    echo '✅ Database connection successful' . PHP_EOL;
    \$mysqli->close();
}
"

if [ $? -ne 0 ]; then
    echo -e "${RED}Database connection failed. Please fix the issues above.${NC}"
    exit 1
fi

# Check port availability
if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null ; then
    echo -e "${RED}❌ Port $PORT is already in use${NC}"
    echo "Kill the process: sudo lsof -i :$PORT"
    exit 1
fi

echo -e "${GREEN}✅ Port $PORT is available${NC}"

# Start server
echo ""
echo -e "${GREEN}🚀 Starting MySQLi PHP Server...${NC}"
echo -e "${GREEN}   Server: http://$HOST:$PORT${NC}"
echo ""
echo -e "${YELLOW}MySQLi API Endpoints:${NC}"
echo "   POST http://$HOST:$PORT/auth-mysqli.php"
echo "   GET  http://$HOST:$PORT/participants-mysqli.php"  
echo "   POST http://$HOST:$PORT/participants-mysqli.php"
echo "   GET  http://$HOST:$PORT/sessions-mysqli.php"
echo "   POST http://$HOST:$PORT/sessions-mysqli.php"
echo "   PUT  http://$HOST:$PORT/sessions-mysqli.php" 
echo "   DEL  http://$HOST:$PORT/sessions-mysqli.php"
echo "   GET  http://$HOST:$PORT/dashboard-mysqli.php"
echo ""
echo -e "${YELLOW}Test Authentication:${NC}"
echo "curl -X POST http://$HOST:$PORT/auth-mysqli.php \\"
echo "  -H 'Content-Type: application/json' \\"
echo "  -d '{\"mobile\":\"9283181228\",\"password\":\"meditation123\"}'"
echo ""
echo -e "${GREEN}Press Ctrl+C to stop${NC}"
echo ""

php -S $HOST:$PORT

echo -e "${YELLOW}Server stopped.${NC}"