# 🚀 Simple MySQLi Backend (No PDO Required!)

This is a **simplified version** of the backend that uses **MySQLi** instead of PDO. MySQLi is usually included with PHP by default, so you won't need to install additional extensions.

## ⚡ Quick Start

### 1. Check What You Have
```bash
# Check if PHP is installed
php --version

# Check if MySQLi extension exists (usually included)
php -m | grep mysqli
```

### 2. Start the Server
```bash
cd backend
./start-mysqli-server.sh
```

**That's it!** 🎉

## 📡 API Endpoints (MySQLi Version)

All endpoints work the same, just with `-mysqli` suffix:

| Original | MySQLi Version |
|----------|----------------|
| `auth.php` | `auth-mysqli.php` |
| `participants.php` | `participants-mysqli.php` |
| `sessions.php` | `sessions-mysqli.php` |
| `dashboard.php` | `dashboard-mysqli.php` |

## 🧪 Test the APIs

### 1. Test Authentication
```bash
curl -X POST http://localhost:8080/auth-mysqli.php \
  -H "Content-Type: application/json" \
  -d '{
    "mobile": "9283181228",
    "password": "meditation123"
  }'
```

### 2. Test Participant Creation
```bash
curl -X POST http://localhost:8080/participants-mysqli.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "age": 30,
    "gender": "Male",
    "branch_id": 1
  }'
```

### 3. Test Session Creation
```bash
curl -X POST http://localhost:8080/sessions-mysqli.php \
  -H "Content-Type: application/json" \
  -d '{
    "participant_id": 1,
    "branch_id": 1,
    "volunteer_id": 1,
    "session_date": "2024-08-10",
    "start_time": "07:00",
    "duration_minutes": 60
  }'
```

## 🔧 Configuration

### Database Settings
Update `config-mysqli.php` if needed:
```php
$host = '192.168.1.13';     // Your MySQL host
$dbname = 'medi-tracker';   // Your database name
$username = 'mediuser';     // Your MySQL user
$password = 'mediuser123!'; // Your MySQL password
```

### For Local Database
If you want to use localhost instead:
```php
$host = 'localhost';
$username = 'root';
$password = 'your_local_password';
```

## 🐛 If MySQLi is Still Missing

### macOS
```bash
# MySQLi is usually included, but if missing:
brew reinstall php

# Or install specific version:
brew install php@8.1
```

### Ubuntu/Debian
```bash
sudo apt update
sudo apt install php php-mysql
```

### Windows
1. Download PHP from [php.net](https://php.net/downloads)
2. Or use XAMPP (includes everything)
3. Ensure `extension=mysqli` is uncommented in `php.ini`

### Check php.ini
```bash
# Find php.ini location
php --ini

# Make sure this line is not commented:
extension=mysqli
```

## ✨ Advantages of MySQLi Version

- ✅ **No PDO required** - Uses MySQLi (usually pre-installed)
- ✅ **Same functionality** - All APIs work identically
- ✅ **Prepared statements** - Still secure against SQL injection
- ✅ **Error handling** - Proper error reporting
- ✅ **Multiple ranges support** - Fully compatible with frontend

## 🔄 Switch Back to PDO Later

If you get PDO working later, you can switch back:
1. Use the original files (`auth.php`, `sessions.php`, etc.)
2. Update your frontend API URLs to remove `-mysqli` suffix
3. All functionality will be identical

## 🎯 Frontend Integration

### Update Frontend API URLs
If using the MySQLi version, update your frontend API base URL:

```typescript
// In your frontend config
const API_BASE_URL = 'http://localhost:8080';

// API calls become:
// POST http://localhost:8080/auth-mysqli.php
// POST http://localhost:8080/sessions-mysqli.php
// etc.
```

### Or Create URL Mapping
In your frontend API service, you can map the URLs:
```typescript
const MYSQLI_ENDPOINTS = {
  'auth.php': 'auth-mysqli.php',
  'participants.php': 'participants-mysqli.php',
  'sessions.php': 'sessions-mysqli.php',
  'dashboard.php': 'dashboard-mysqli.php'
};
```

## 🚀 Production Notes

- **Performance**: MySQLi is just as fast as PDO
- **Security**: Uses prepared statements for safety
- **Compatibility**: Works with all PHP versions 5.0+
- **Features**: Supports all backend functionality

## 💡 Why This Works Better

MySQLi is:
- **Pre-installed** with most PHP distributions
- **Easier to setup** - no additional extensions needed
- **Widely supported** - available everywhere PHP runs
- **Full-featured** - supports everything we need

---

**You should be able to run this immediately!** 🎉

Just run `./start-mysqli-server.sh` and start testing your APIs!