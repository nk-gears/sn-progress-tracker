# Backend API Deployment Guide

## Overview
The backend API needs to be deployed alongside the frontend to handle authentication, data management, and the new user onboarding functionality.

## Deployment Structure

Your deployed directory should look like this:

```
/path/to/deployed/directory/
├── index.html              # Vue frontend entry point
├── assets/                 # Vue frontend assets
├── api.php                 # ✅ Backend API (copy from backend/api.php)
├── config.php              # ✅ Database config (copy from backend/config.php)
├── .htaccess               # ✅ Updated with API routing
└── [other frontend files]
```

## Required Files

### 1. Copy API Files
```bash
# Copy these files from backend/ to your deployment root:
cp backend/api.php ./
cp backend/config.php ./
```

### 2. Update .htaccess
The .htaccess file should include API routing (already updated in the project):

```apache
# API Routes - Route all /api/* requests to api.php  
RewriteCond %{REQUEST_URI} ^(.*/)?api/(.*)$
RewriteRule ^(.*/)?api/(.*)$ $1api.php [L,QSA]

# Vue SPA Routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^(.*/)?api/
RewriteRule ^(.*)$ index.html [L,QSA]
```

## Database Configuration

Update `config.php` with your production database settings:

```php
<?php
// Database configuration for production
define('DB_HOST', 'your-database-host');
define('DB_USER', 'your-database-user');  
define('DB_PASS', 'your-database-password');
define('DB_NAME', 'your-database-name');
?>
```

## API Endpoints

After deployment, these endpoints should be available:

### Frontend URLs
- `https://your-domain.com/sn-progress/` - Vue app
- `https://your-domain.com/sn-progress/login` - Login page
- `https://your-domain.com/sn-progress/dashboard` - Dashboard

### API URLs  
- `https://your-domain.com/sn-progress/api/auth` - Authentication
- `https://your-domain.com/sn-progress/api/participants` - Participants
- `https://your-domain.com/sn-progress/api/sessions` - Sessions  
- `https://your-domain.com/sn-progress/api/dashboard` - Analytics
- `https://your-domain.com/sn-progress/api/onboard` - **NEW: User Onboarding**

## Testing Deployment

### 1. Test Frontend
Visit: `https://your-domain.com/sn-progress/`
- Should load the Vue application
- Should show login page

### 2. Test API
```bash
# Test authentication endpoint
curl https://your-domain.com/sn-progress/api/auth \
  -H "Content-Type: application/json" \
  -d '{"mobile":"9283181228","password":"meditation123"}'

# Test onboarding endpoint  
curl https://your-domain.com/sn-progress/api/onboard \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "mobile": "9876543210", 
    "password": "testpass123",
    "branch_name": "Chennai Central"
  }'
```

### 3. Expected Responses
- **API Working**: Returns JSON responses
- **API Not Working**: Returns HTML 404 page or server error

## Troubleshooting

### 404 Errors on API calls
1. Ensure `api.php` and `config.php` are in the root deployment directory
2. Check `.htaccess` has the API routing rules
3. Verify mod_rewrite is enabled on the server
4. Check server error logs

### Database Connection Issues
1. Update database credentials in `config.php`
2. Ensure database server is accessible from web server
3. Check if required PHP extensions are installed (mysqli)
4. Test database connection separately

### PHP Requirements
- PHP 7.4 or higher
- mysqli extension
- mod_rewrite (for URL routing)

## Security Notes

- Ensure `config.php` contains production database credentials
- Database should not be publicly accessible
- API endpoints are designed to be public for the onboarding functionality
- Password hashing is handled automatically by the API

## File Permissions

Ensure proper file permissions after deployment:
```bash
chmod 644 api.php config.php .htaccess
chmod 755 ./  # directory permissions
```