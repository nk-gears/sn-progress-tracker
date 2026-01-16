# FTP Deployment Guide

This guide explains how to deploy your Vue.js frontend and PHP backend to a remote FTP server using the provided deployment script.

## Setup

1. **Copy the configuration file:**
   ```bash
   cp deploy.config.example.sh deploy.config.sh
   ```

2. **Edit the configuration file:**
   ```bash
   nano deploy.config.sh
   ```
   
   Fill in your FTP credentials:
   - `PROD_FTP_HOST`: Your FTP server hostname
   - `PROD_FTP_USER`: Your FTP username
   - `PROD_FTP_PASS`: Your FTP password
   - `PROD_FTP_REMOTE_DIR`: Remote directory path (usually `/public_html`)

3. **Make the script executable:**
   ```bash
   chmod +x deploy.sh
   ```

## Deployment

### Deploy to Production
```bash
./deploy.sh production
```

### Deploy to Staging (optional)
```bash
./deploy.sh staging
```

## What the Script Does

1. **Validates configuration** - Checks if FTP credentials are set
2. **Builds Vue.js frontend** - Runs `npm run build` in the frontend directory
3. **Prepares PHP backend** - Copies PHP files (excludes development files)
4. **Uploads via FTP** - Uses `lftp` (preferred) or `ftp` to upload files
5. **Tests deployment** - Optional HTTP check to verify the site is live

## File Structure After Deployment

```
Remote Server Root/
├── index.html          # Vue.js app entry point
├── assets/             # CSS, JS, and other assets
├── api/                # PHP backend files
│   ├── auth.php
│   ├── config.php
│   ├── dashboard.php
│   ├── participants.php
│   └── sessions.php
└── ...                 # Other Vue.js build files
```

## Requirements

- Node.js and npm (for building Vue.js)
- FTP client (`lftp` recommended, or system `ftp`)
- Bash shell

### Installing lftp (recommended)

**macOS:**
```bash
brew install lftp
```

**Ubuntu/Debian:**
```bash
sudo apt-get install lftp
```

**CentOS/RHEL:**
```bash
sudo yum install lftp
```

## Common Hosting Provider Paths

- **cPanel/WHM**: `/public_html`
- **Plesk**: `/httpdocs`
- **DirectAdmin**: `/public_html`
- **Subdirectory**: `/public_html/your-app`

## Troubleshooting

### FTP Connection Issues
- Verify hostname, username, and password
- Check if FTP is enabled on your hosting account
- Some hosts require SFTP instead of FTP

### Build Failures
- Ensure all frontend dependencies are installed: `cd frontend && npm install`
- Check for TypeScript errors: `npm run type-check`

### Permission Issues
- Make the script executable: `chmod +x deploy.sh`
- Ensure FTP user has write permissions to the target directory

### File Not Found Errors
- Verify `frontend/dist` exists after build
- Check that `backend` directory contains PHP files

## Security Notes

- Never commit `deploy.config.sh` to version control (it contains passwords)
- Use strong FTP passwords
- Consider using SFTP if your host supports it
- Regularly update your FTP credentials

## Customization

You can modify `deploy.sh` to:
- Add database migration scripts
- Include environment-specific configuration files
- Add pre/post-deployment hooks
- Implement rollback functionality