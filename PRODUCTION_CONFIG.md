# Production Configuration Guide

## Environment Variables

### ✅ .env.production (Already Configured)
```env
VITE_API_MODE=real
VITE_API_BASE_URL=/sn-progress/api
VITE_ENABLE_API_SWITCHER=false
VITE_ENABLE_MOCK_DELAY=false
```

### Key Changes Made:
- `VITE_API_MODE=real` - Uses real API instead of mock
- `VITE_API_BASE_URL=/sn-progress/api` - Correct API path for subdirectory deployment
- `VITE_ENABLE_API_SWITCHER=false` - Hides development API switcher
- `VITE_ENABLE_MOCK_DELAY=false` - Disables mock API delays

## Vite Configuration

### ✅ vite.config.ts (Already Configured)
```typescript
export default defineConfig({
  base: '/sn-progress/',           // ✅ Correct for subdirectory
  plugins: [
    vue(),
    VitePWA({
      manifest: {
        start_url: '/sn-progress/', // ✅ Fixed PWA start URL
        // ... other PWA config
      }
    })
  ]
})
```

## Build Process

### Production Build Command
```bash
# This automatically uses .env.production
npm run build
```

### Or use the deployment script:
```bash
./prepare-deployment.sh
```

## API Routing Configuration

### Frontend API Calls
With `VITE_API_BASE_URL=/sn-progress/api`, the frontend will make calls to:
- `/sn-progress/api/auth`
- `/sn-progress/api/participants`
- `/sn-progress/api/sessions`
- etc.

### Backend .htaccess Routing
The .htaccess file routes these to the actual PHP file:
```apache
# /sn-progress/api/auth -> /sn-progress/api.php (with auth endpoint)
RewriteCond %{REQUEST_URI} ^(.*/)?api/(.*)$
RewriteRule ^(.*/)?api/(.*)$ $1api.php [L,QSA]
```

## Deployment Structure

After building, your deployment should have:

```
/path/to/sn-progress/
├── index.html              # Vue app entry
├── assets/                 # Built JS/CSS assets  
├── api.php                 # Backend API
├── config.php              # Database config
├── .htaccess               # Routing rules
├── manifest.json           # PWA manifest
└── sn-logo.png            # App logo
```

## URLs After Deployment

### Frontend URLs
- `https://happy-village.org/sn-progress/` - Main app
- `https://happy-village.org/sn-progress/login` - Login page
- `https://happy-village.org/sn-progress/dashboard` - Dashboard

### API URLs (Backend)
- `https://happy-village.org/sn-progress/api/auth` - Authentication
- `https://happy-village.org/sn-progress/api/participants` - Participants
- `https://happy-village.org/sn-progress/api/sessions` - Sessions
- `https://happy-village.org/sn-progress/api/dashboard` - Analytics
- `https://happy-village.org/sn-progress/api/onboard` - User onboarding

## Testing Production Build Locally

You can test the production build locally:

```bash
# Build for production
npm run build

# Serve the built files (from frontend directory)
npx serve dist -s
```

Then test API calls to ensure they work with the production configuration.

## Environment File Priority

Vite uses environment files in this order:
1. `.env.production.local` (highest priority, git-ignored)
2. `.env.production` ✅ (your configured file)
3. `.env.local` (git-ignored)
4. `.env`

## Verification Checklist

Before deployment:
- [ ] Production build completes without errors
- [ ] API base URL points to correct path (`/sn-progress/api`)
- [ ] PWA start URL matches deployment path (`/sn-progress/`)
- [ ] API switcher is disabled in production
- [ ] Database config updated with production credentials