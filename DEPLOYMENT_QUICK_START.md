# Quick Start: GitHub Actions Deployment

## 🚀 One-Time Setup (5 minutes)

### 1. Add GitHub Secrets

Go to your GitHub repository:
- **Settings** → **Secrets and variables** → **Actions**
- Click **New repository secret**

Add these secrets:

```
FTP_HOST = your-ftp-server.com
FTP_USER = your-ftp-username
FTP_PASSWORD = your-ftp-password
VITE_API_BASE_URL = https://happy-village.org/sn-progress
```

### 2. Verify Workflow File

Check that `.github/workflows/deploy-web-app.yml` exists in your repository.

## ✅ How to Deploy

### Simple 3-Step Deployment

```bash
# 1. Update version
echo "1.2.0" > version.txt

# 2. Commit and push
git add version.txt
git commit -m "Release v1.2.0"
git push origin main

# 3. Watch deployment in GitHub Actions
# → Go to Actions tab → See "Deploy Web App on Version Change" running
```

### What Happens Automatically

1. ✅ GitHub detects version.txt change
2. ✅ Builds the web app (production mode)
3. ✅ Uploads to FTP server
4. ✅ Shows status in GitHub Actions

### Monitor Deployment

- Go to **Actions** tab
- Click on the workflow run
- Watch the progress in real-time
- See logs for each step

## 📋 Workflow Steps Explained

```
Detect Version → Build Web App → Deploy to FTP → Send Notification
   (read version)   (npm build)    (FTP upload)     (summary)
```

## 🎯 Common Scenarios

### Deploy New Features
```bash
# Make your changes
git add web-app/src/...
git commit -m "Add new features"

# Bump version and push
echo "1.5.0" > version.txt
git add version.txt
git commit -m "Release v1.5.0"
git push
```

### Manual Deployment
1. Go to **Actions** → **Deploy Web App on Version Change**
2. Click **Run workflow**
3. Select environment
4. Click **Run workflow**

### Rollback to Previous Version
1. Go to **Actions** → Find the previous successful deployment
2. Download the artifact from that run
3. Or just deploy a previous version number

## ⚙️ What Gets Deployed

- ✅ Built Vue.js application (optimized)
- ✅ Static assets (CSS, JS, images)
- ✅ `.htaccess` file (SPA routing)
- ✅ All public files

## 🔍 Troubleshooting

### Deployment Failed?

1. **Check Actions tab** for error details
2. **Verify FTP credentials** in GitHub Secrets
3. **Ensure version.txt is committed** before pushing

### Build Issues?

- Check Node.js version is 18
- Verify `web-app/package.json` is valid
- Run `npm run build` locally to test

## 📊 Deployment URL

After deployment, your app is live at:
```
https://happy-village.org/sn-join/
```

## 💡 Pro Tips

1. **Use semantic versioning**: 1.0.0 → 1.1.0 → 2.0.0
2. **Test locally**: Run `npm run build && npm run preview` before deploying
3. **Keep version updated**: Always update version.txt with new releases
4. **Monitor logs**: Check GitHub Actions for deployment status

## 📝 Version Format

Use `X.Y.Z` format:
- `1.0.0` - First release
- `1.0.1` - Bug fix
- `1.1.0` - New features
- `2.0.0` - Major update

## ❓ Questions?

See full documentation in:
- `docs/GITHUB_ACTIONS_DEPLOYMENT.md` - Complete guide
- `.github/workflows/deploy-web-app.yml` - Workflow definition

---

**That's it!** Your automated deployment is ready. Just update version.txt and push to deploy! 🎉
