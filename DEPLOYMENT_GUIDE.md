# 🚀 Deployment Guide with Backup Strategy

## Pre-Deployment Checklist

- [ ] Verify `deploy.config.sh` has correct FTP credentials
- [ ] Ensure `.htaccess` file exists for Vue.js SPA routing
- [ ] Check that `config.php` is NOT included in deployment (it's excluded)
- [ ] Verify database migrations (if any) are up-to-date
- [ ] Test locally: `npm run dev` in frontend directory
- [ ] Build locally: `npm run build` in frontend directory

## Safe Deployment Process

### Step 1: Create Remote Backup (BEFORE deployment)

```bash
# Backup staging environment (recommended first)
./backup-remote.sh staging

# Or backup production (only if you have PROD credentials configured)
./backup-remote.sh production
```

**What this does:**
- Downloads all files from remote FTP server
- Creates timestamped backup in `/Users/Nirmal/Documents/backups-hv/`
- Creates `latest-staging` or `latest-production` symlinks for easy access
- Generates `BACKUP_INFO.txt` with backup details

**Backup directory structure:**
```
/Users/Nirmal/Documents/backups-hv/
├── staging_20251023_064930/
│   ├── index.html
│   ├── assets/
│   ├── api/
│   ├── BACKUP_INFO.txt
│   └── ...
└── latest-staging -> staging_20251023_064930/
```

### Step 2: Deploy Changes

```bash
# Deploy to staging (test environment)
./deploy.sh staging

# Or deploy to production (production environment)
./deploy.sh production

# Force deploy (upload all files regardless of changes)
./deploy.sh staging --force
```

**What the deploy script does:**
- Builds Vue.js frontend (`npm run build`)
- Prepares PHP backend files
- Compares with previous deployment (incremental upload)
- Uploads only changed files (unless `--force` is used)
- Saves checksums for next deployment
- Tests if domain is accessible (optional)

### Step 3: Verify Deployment

After deployment completes:

```bash
# Check if staging site is live
curl -I https://happy-village.org

# Or open in browser: https://happy-village.org
```

## Deploy Script Features

### Incremental Deployment ✨
- Tracks file checksums
- Only uploads changed files on subsequent deployments
- First deployment uploads everything
- Use `--force` flag to upload all files again

### Frontend & Backend Strategy
- **Frontend**: Full replacement (clears old assets, prevents stale files)
- **Backend**: Incremental (only changed PHP files uploaded)
- **Protection**: Excludes `config.php` to preserve database credentials

### Upload Methods
- **Primary**: `lftp` (smart and efficient)
- **Fallback**: `curl` (if lftp not available)

## Environment Configuration

### Current Configuration (from `deploy.config.sh`)

**Staging (Configured):**
- FTP Host: `82.180.152.203`
- FTP User: `u388678206.happy-village.org`
- Remote Dir: `/sn-progress`
- Domain: `happy-village.org`

**Production (Needs Configuration):**
- Not yet configured
- To configure: Edit `deploy.config.sh` and add PROD_* variables

## Rollback Procedure

If deployment causes issues:

### Option 1: Use Latest Backup (Fastest)
```bash
# Manual restoration via FTP client:
# 1. Open FTP client (FileZilla, Transmit, etc.)
# 2. Connect to FTP server
# 3. Navigate to /sn-progress directory
# 4. Delete current files
# 5. Upload files from /Users/Nirmal/Documents/backups-hv/latest-staging/
```

### Option 2: Use Backup Script for Restoration
```bash
# First backup creates a restore point
# Use your FTP client to manually restore from:
/Users/Nirmal/Documents/backups-hv/staging_<timestamp>/
```

### Option 3: Git Rollback
```bash
# Revert code changes in git
git revert <commit-hash>
# Then re-deploy
./deploy.sh staging --force
```

## Troubleshooting

### Issue: "deploy.config.sh not found"
**Solution:** Make sure you have FTP credentials configured in `deploy.config.sh`

### Issue: "Frontend build failed"
**Solution:**
```bash
cd frontend
npm install
npm run build
cd ..
```

### Issue: "lftp not found"
**Solution:** Script will automatically fall back to curl (slightly slower but works)

### Issue: "Upload failed - Permission denied"
**Solution:**
- Verify FTP credentials in `deploy.config.sh`
- Check FTP host allows uploads
- Ensure `/sn-progress` directory exists on server

### Issue: "Backup is empty"
**Solution:**
- Verify FTP connection works
- Check if remote server has files at `/sn-progress`
- Try manually connecting with FTP client to test

## File Exclusions

### Files NOT deployed (intentionally excluded)
- `.env` files
- `config.php` (preserves remote database credentials)
- `*.log` files
- `*.tmp` files
- `.git` directory
- Docker files (`docker-compose.yml`, `start-*.sh`)

## Safety Features

✅ **Checksums saved** - Tracks all deployed files
✅ **Backup before deploy** - Creates restoration point
✅ **Incremental upload** - Faster, less error-prone
✅ **Config protection** - Excludes sensitive files
✅ **Build verification** - Ensures build succeeds before upload
✅ **Domain testing** - Checks if site is live after deploy

## Performance Notes

- **First deployment**: Uploads all files (~100+ files typically)
- **Subsequent deployments**: Much faster (only changed files)
- **Frontend changes**: Usually 5-20 files changed
- **Backend changes**: Usually 1-5 files changed
- **Expected time**: 2-5 minutes total

## Next Steps

1. ✅ Review backup script created
2. ⏭️ Create a remote backup: `./backup-remote.sh staging`
3. ⏭️ Deploy changes: `./deploy.sh staging`
4. ⏭️ Test the live site
5. ⏭️ Keep backups for safety

---

**Created**: Oct 23, 2025
**Updated**: Deploy Guide v1.0
