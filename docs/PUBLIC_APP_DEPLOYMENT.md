# Public Web App Deployment Guide

## Overview

This guide explains how to deploy the **Public Web App** (Shivanum Naanum Join form) using the `deploy-public-app.sh` script.

## Deployment Structure

The project has two separate deployments:

### 1. Admin App + Backend API
- **Script:** `deploy.sh`
- **Path:** `/public_html/sn-progress`
- **Components:**
  - Frontend: Vue.js admin app for volunteers
  - Backend: PHP API (api.php)
  - Database: MySQL integration

### 2. Public Web App
- **Script:** `deploy-public-app.sh`
- **Path:** `/public_html/sn-join/`
- **Components:**
  - Frontend: Vue.js public website (Shivanum Naanum)
  - Features: Event info, centre finder, registration form
  - Uses same backend API at `/sn-progress/api/`

## Prerequisites

### 1. Install Dependencies

```bash
cd web-app
npm install
```

### 2. Configure FTP Credentials

The script uses the same `deploy.config.sh` file as the main deployment:

```bash
# If not already done
cp deploy.config.example.sh deploy.config.sh
# Edit deploy.config.sh with your FTP credentials
```

**Configuration Example:**
```bash
# Production Environment
PROD_FTP_HOST="your-ftp-host.com"
PROD_FTP_USER="your-ftp-username"
PROD_FTP_PASS="your-ftp-password"
PROD_DOMAIN="your-domain.com"

# Staging Environment (optional)
STAGING_FTP_HOST="staging-host.com"
STAGING_FTP_USER="staging-username"
STAGING_FTP_PASS="staging-password"
STAGING_DOMAIN="staging-domain.com"
```

### 3. Configure API URL

Before deploying, update the API URL in `web-app/index.html`:

**For Production:**
```html
<script>
  window.APP_CONFIG = {
    API_BASE_URL: 'https://yourdomain.com/sn-progress/api/api.php'
  };
</script>
```

**For Staging:**
```html
<script>
  window.APP_CONFIG = {
    API_BASE_URL: 'http://staging-domain.com/sn-progress/api/api.php'
  };
</script>
```

## Deployment Commands

### Deploy to Production

```bash
./deploy-public-app.sh production
```

### Deploy to Staging

```bash
./deploy-public-app.sh staging
```

### Force Deploy (Override Change Detection)

```bash
./deploy-public-app.sh production --force
```

## What the Script Does

### Step 1: Build the Web App
```bash
cd web-app
npm run build
```

- Compiles Vue.js components
- Minifies CSS and JavaScript
- Optimizes assets
- Generates production build in `dist/` folder

### Step 2: Prepare Files
- Copies all files from `dist/` folder
- Creates/copies `.htaccess` for SPA routing
- Generates file checksums for incremental deployment

### Step 3: Upload to FTP
- Target directory: `/public_html/sn-join/`
- Clears existing files for clean deployment
- Uploads all built files
- Creates proper directory structure

### Step 4: Verify Deployment
- Tests website availability
- Displays live URL
- Saves checksums for next deployment

## Deployment Output Example

```
🚀 Starting deployment of Public Web App to production environment...
FTP Host: your-ftp-host.com
Remote Directory: /public_html/sn-join

📦 Building Public Web App (web-app)...
✅ Web app build completed
✅ Created default .htaccess file for SPA routing
✅ Web app files prepared

🌐 Uploading files via FTP to /public_html/sn-join...
📊 Checking for changed files since last deployment...
Found 15 changed files

Using lftp for upload...
Uploading 15 files...

✅ Deployment completed successfully!
🌍 Your public web app should now be live at:
   http://yourdomain.com/sn-join/
📝 File checksums saved for next incremental deployment

🎉 Public Web App deployment process completed!
```

## Incremental Deployment

The script uses checksums to detect file changes:

- **First deployment:** Uploads all files
- **Subsequent deployments:** Only uploads changed files
- **Force mode (`--force`):** Uploads all files regardless of changes

**Benefits:**
- Faster deployments
- Reduced bandwidth usage
- Only updates what's necessary

**Checksum storage:**
```
~/.sn-progress-deploy/last-deploy-public-production.txt
~/.sn-progress-deploy/last-deploy-public-staging.txt
```

## Accessing the Deployed App

### Production
```
http://yourdomain.com/sn-join/
```

### Staging
```
http://staging-domain.com/sn-join/
```

## File Structure After Deployment

```
/public_html/
├── sn-progress/              # Admin app + Backend API (deploy.sh)
│   ├── index.html
│   ├── assets/
│   └── api/
│       └── api.php
│
└── sn-join/                  # Public web app (deploy-public-app.sh)
    ├── index.html
    ├── assets/
    │   ├── index-abc123.js
    │   ├── index-def456.css
    │   └── logo.svg
    ├── favicon.svg
    └── .htaccess
```

## .htaccess Configuration

The script automatically creates an `.htaccess` file with:

### 1. Vue.js SPA Routing
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.html [L]
```

### 2. Cache Headers
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 3. Compression
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>
```

## Troubleshooting

### Build Fails

**Error:** `dist directory not found`

**Solution:**
```bash
cd web-app
rm -rf node_modules package-lock.json
npm install
npm run build
```

### FTP Connection Fails

**Error:** `FTP credentials not configured`

**Solution:**
- Check `deploy.config.sh` exists
- Verify FTP credentials are correct
- Test FTP connection manually:
  ```bash
  ftp your-ftp-host.com
  # Enter username and password
  ```

### Upload Fails

**Error:** `Failed to upload: [file]`

**Solution:**
- Check FTP user has write permissions
- Verify remote directory exists
- Check disk space on server
- Try with `--force` flag

### Website Shows 404

**Error:** Website returns 404 after deployment

**Solutions:**
1. Check `.htaccess` was uploaded
2. Verify mod_rewrite is enabled on server
3. Check file permissions (644 for files, 755 for directories)
4. Clear browser cache

### CORS Errors

**Error:** API calls fail with CORS errors

**Solution:**
Update `backend/config.php` to allow the new domain:
```php
$allowed_origins = [
    'http://localhost:8080',
    'https://yourdomain.com',
    'http://yourdomain.com/sn-join'  // Add this
];
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Deploy Public Web App

on:
  push:
    branches: [ main ]
    paths:
      - 'web-app/**'

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup Node.js
        uses: actions/setup-node@v2
        with:
          node-version: '18'

      - name: Install lftp
        run: sudo apt-get install -y lftp

      - name: Create deploy config
        run: |
          cat > deploy.config.sh << EOF
          PROD_FTP_HOST="${{ secrets.FTP_HOST }}"
          PROD_FTP_USER="${{ secrets.FTP_USER }}"
          PROD_FTP_PASS="${{ secrets.FTP_PASS }}"
          PROD_DOMAIN="${{ secrets.DOMAIN }}"
          EOF

      - name: Deploy to production
        run: ./deploy-public-app.sh production
```

## Rollback Procedure

If deployment causes issues:

### Option 1: Redeploy Previous Version
```bash
# Checkout previous version
git checkout <previous-commit-hash>

# Force deploy
./deploy-public-app.sh production --force

# Return to latest
git checkout main
```

### Option 2: Manual FTP Restore
1. Connect via FTP client (FileZilla, etc.)
2. Delete contents of `/public_html/sn-join/`
3. Upload backup files

## Best Practices

### 1. Test Before Deploying
```bash
# Local testing
cd web-app
npm run dev
# Test all features

# Build test
npm run build
npm run preview
# Test built version
```

### 2. Deploy to Staging First
```bash
# Test on staging
./deploy-public-app.sh staging

# Verify staging works
# Then deploy to production
./deploy-public-app.sh production
```

### 3. Keep Backups
Before major deployments:
```bash
# Backup via FTP
lftp -c "open ftp://$USER:$PASS@$HOST; mirror /public_html/sn-join ./backup-$(date +%Y%m%d)"
```

### 4. Monitor After Deployment
- Check website loads correctly
- Test registration form submission
- Verify API calls work
- Check browser console for errors
- Test on mobile devices

## Maintenance

### Update Dependencies
```bash
cd web-app
npm update
npm audit fix
```

### Clean Old Deployments
```bash
# Clear deployment cache
rm ~/.sn-progress-deploy/last-deploy-public-*.txt

# Next deployment will be full upload
./deploy-public-app.sh production
```

## Security Notes

1. **Never commit** `deploy.config.sh` to git
2. Use environment variables for CI/CD
3. Regularly update FTP password
4. Use SFTP/FTPS if available
5. Restrict FTP user to specific directories

## Support

### Common Commands Quick Reference

```bash
# Deploy to production
./deploy-public-app.sh production

# Deploy to staging
./deploy-public-app.sh staging

# Force full deployment
./deploy-public-app.sh production --force

# Check build output
cd web-app && npm run build && ls -lh dist/

# Test FTP connection
lftp ftp://user:pass@host

# View deployment history
cat ~/.sn-progress-deploy/last-deploy-public-production.txt
```

---

**Note:** This script deploys **only the public web app**. The admin app and backend API use the separate `deploy.sh` script.

For questions or issues, check the deployment logs or contact the development team.
