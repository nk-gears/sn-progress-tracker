# PHP Backend Setup Guide - Missing Extensions

You're getting an error about missing PHP extensions. Here's how to install them:

## 🚀 Quick Solutions

### For macOS (Homebrew)
```bash
# Install PHP with required extensions
brew install php

# Or if PHP is already installed, reinstall
brew reinstall php

# Check if extensions are now available
php -m | grep -i pdo
```

### For macOS (MacPorts)
```bash
sudo port install php81 +mysql +pdo
```

### For Ubuntu/Debian Linux
```bash
# Update package list
sudo apt update

# Install PHP and required extensions
sudo apt install php php-mysql php-pdo php-json php-cli

# Restart if needed
sudo service apache2 restart  # if using Apache
```

### For CentOS/RHEL/Rocky Linux
```bash
# Enable required repositories
sudo dnf install epel-release
sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm

# Install PHP and extensions
sudo dnf install php php-pdo php-mysqlnd php-json

# Or using yum (older versions)
sudo yum install php php-pdo php-mysql php-json
```

### For Windows
1. **Download PHP** from [php.net/downloads](https://php.net/downloads)
2. **Choose Thread Safe version** with extensions
3. **Or use XAMPP/WAMP** which includes all extensions:
   - Download [XAMPP](https://www.apachefriends.org/)
   - Install and start Apache/MySQL services
   - PHP with all extensions will be available

## 🔍 Verify Installation

After installing, verify the extensions are available:

```bash
# Check PHP version and confirm it's working
php --version

# List all installed extensions
php -m

# Check specific extensions we need
php -m | grep -i pdo
php -m | grep -i mysql
php -m | grep -i json
```

Expected output should show:
```
PDO
pdo_mysql
json
```

## 🐛 Still Having Issues?

### Check PHP Configuration
```bash
# Find php.ini location
php --ini

# Check if extensions are commented out in php.ini
grep -i "extension=pdo" /path/to/php.ini
```

### Enable Extensions in php.ini
Edit your `php.ini` file and ensure these lines are uncommented:
```ini
extension=pdo
extension=pdo_mysql
extension=json
```

### Alternative: Use Docker
If you're still having PHP extension issues, use Docker:

```bash
# Create a simple Dockerfile in the backend directory
cat > Dockerfile << 'EOF'
FROM php:8.1-cli
RUN docker-php-ext-install pdo pdo_mysql
WORKDIR /var/www
COPY . .
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080"]
EOF

# Build and run
docker build -t meditation-api .
docker run -p 8080:8080 meditation-api
```

## 🚀 Start the Server

Once extensions are installed, try starting the server again:

```bash
cd backend
./start-server.sh
```

Or manually:
```bash
php -S localhost:8080
```

## 📱 Test the API

Once running, test with:
```bash
curl -X POST http://localhost:8080/auth.php \
  -H "Content-Type: application/json" \
  -d '{"mobile": "9283181228", "password": "meditation123"}'
```

## 💡 Quick Development Setup

For the fastest setup, I recommend:

1. **macOS**: Use Homebrew - `brew install php`
2. **Windows**: Use XAMPP - includes everything
3. **Linux**: Use your package manager as shown above
4. **Any OS**: Use Docker if you want isolated environment

Let me know which operating system you're using if you need more specific help!