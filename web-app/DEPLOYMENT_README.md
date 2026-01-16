# Web App Deployment Guide

## Quick Start

Deploy the public web app (Shivanum Naanum Join) to production:

```bash
# From project root directory
./deploy-public-app.sh production
```

## Prerequisites

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Configure API URL:**
   Edit `index.html` and update:
   ```html
   <script>
     window.APP_CONFIG = {
       API_BASE_URL: 'https://yourdomain.com/sn-progress/api/api.php'
     };
   </script>
   ```

3. **Configure FTP credentials:**
   ```bash
   # From project root (one level up)
   cd ..
   cp deploy.config.example.sh deploy.config.sh
   # Edit deploy.config.sh with your FTP details
   ```

## Deployment Commands

### Production Deployment
```bash
./deploy-public-app.sh production
```

### Staging Deployment
```bash
./deploy-public-app.sh staging
```

### Force Deploy (Ignore Change Detection)
```bash
./deploy-public-app.sh production --force
```

## What Gets Deployed

- **Target Server Path:** `/public_html/sn-join/`
- **Source:** Build output from `web-app/dist/`
- **Includes:**
  - Vue.js app (compiled)
  - Static assets (CSS, JS, images)
  - .htaccess for SPA routing

## Deployment URL

After deployment, your app will be available at:

**Production:**
```
https://yourdomain.com/sn-join/
```

**Staging:**
```
http://staging-domain.com/sn-join/
```

## Testing Before Deployment

### Local Development
```bash
npm run dev
```
Opens at: `http://localhost:5174`

### Test Production Build
```bash
npm run build
npm run preview
```

## Troubleshooting

### Build Fails
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

### API Not Working
Check `index.html` has correct API URL:
```html
API_BASE_URL: 'https://yourdomain.com/sn-progress/api/api.php'
```

### 404 Errors on Refresh
Ensure `.htaccess` was deployed with proper SPA routing rules.

## Important Notes

1. **This deploys ONLY the public website** (no backend)
2. **Backend API must be deployed separately** using `./deploy.sh`
3. **API URL must point to the backend API location**
4. **Test on staging before production**

## Need Help?

- Full documentation: `/PUBLIC_APP_DEPLOYMENT.md`
- Comparison with admin deployment: `/DEPLOYMENT_COMPARISON.md`
- GitHub issues: Create an issue for deployment problems

---

**Quick Links:**
- [Full Deployment Guide](../PUBLIC_APP_DEPLOYMENT.md)
- [Deployment Comparison](../DEPLOYMENT_COMPARISON.md)
- [Main Project README](../README.md)
