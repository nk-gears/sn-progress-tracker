# Auto-Update Feature - Quick Start

## What Changed?

When you deploy a new version, users **automatically see a notification** asking them to refresh to get the latest features.

```
┌─────────────────────────────────────────────┐
│ ✨ New version available!                   │
│ A new version of the app has been released. │
│                                             │
│  [Later]     [Refresh]                      │
└─────────────────────────────────────────────┘
```

## How It Works (Simple Version)

1. **You deploy** → Update `version.txt` and push
2. **Server gets updated** → version.json file is created with new version
3. **Browser checks** → Every 5 minutes, app checks if new version is available
4. **User is notified** → Blue banner appears at top with "New version available"
5. **User refreshes** → Clicks "Refresh" button, gets latest version

## For Users

Users will see a notification at the top of the page when a new version is available. They can:

- **Click "Refresh"** → Load the new version immediately
- **Click "Later"** → Dismiss and check again in 5 minutes
- **Nothing** → Old version expires from cache after 1 hour anyway

## For Developers

### Deploying a New Version

```bash
# 1. Make your changes
git add web-app/src/...
git commit -m "Add new features"

# 2. Update version
echo "1.3.0" > version.txt
git add version.txt
git commit -m "Release v1.3.0"

# 3. Push (this triggers everything automatically)
git push origin main
```

That's it! The rest happens automatically:
- ✅ GitHub Actions builds with v1.3.0
- ✅ Creates `version.json` with v1.3.0
- ✅ Creates `.htaccess` with cache headers
- ✅ Deploys to production
- ✅ Users are automatically notified within 5 minutes

### What Gets Cached?

| File Type | Cache Time | Why |
|-----------|-----------|-----|
| `version.json` | Never cached | Always check for new version |
| `index.html` | 1 hour | Moderate refresh rate |
| JS/CSS files | 1 year | Safe because filenames change with content |
| Images | 1 year | Stable content |

## Browser Cache Explained

The app is smart about caching:

```
User opens app at 9:00 AM
├─ Browser loads from cache (fast!)
├─ App checks version.json from server (fresh!)
├─ Detects new version? Yes → Show notification
└─ User clicks Refresh → Hard reload, get latest

User waits 1+ hour without closing
├─ HTML cache expires automatically
├─ Next visit auto-loads fresh version
└─ OR: Notification still visible to click
```

## Troubleshooting

### "I deployed but users don't see notification"

**Answer**: They will within 5 minutes. The app checks:
- Immediately when they open the page
- Every 5 minutes after that

If they don't see it after 5 minutes:
1. Ask them to refresh the page manually (`Ctrl+Shift+R` or `Cmd+Shift+R`)
2. Check that `version.json` was deployed correctly

### "User is still seeing old version"

**This is normal!**
- HTML is cached for 1 hour
- App shows notification to refresh
- After 1 hour, old HTML expires anyway

Just tell user to click "Refresh" button or wait.

### "How do I test this locally?"

```bash
# Build and preview
cd web-app
npm run build
npm run preview

# Open in browser and check version.json exists
# http://localhost:4173/version.json
```

## Customization

### Change check interval

**File**: `src/App.vue`

```typescript
// Current: Every 5 minutes
const { showUpdateNotification, refreshPage } = useVersionCheck(5 * 60 * 1000)

// Change to 2 minutes
const { showUpdateNotification, refreshPage } = useVersionCheck(2 * 60 * 1000)
```

### Change notification text

**Files**:
- `web-app/public/locales/lang-en.json` - English
- `web-app/public/locales/lang-ta.json` - Tamil
- `web-app/public/locales/lang-ml.json` - Malayalam

```json
"updateNotification": {
  "title": "✨ New version available!",
  "message": "A new version of the app has been released.",
  "refresh": "Refresh",
  "later": "Later"
}
```

### Change HTML cache duration

**File**: `.github/workflows/deploy-web-app.yml`

```apache
# Current: 1 hour (3600 seconds)
Header set Cache-Control "public, max-age=3600, must-revalidate"

# Change to 30 minutes (1800 seconds)
Header set Cache-Control "public, max-age=1800, must-revalidate"
```

## Technical Details

If you want to understand how it works in detail, see:
- `docs/CACHE_BUSTING_STRATEGY.md` - Complete technical documentation
- `src/composables/useVersionCheck.ts` - Version checking logic
- `src/components/UpdateNotification.vue` - Notification UI
- `.github/workflows/deploy-web-app.yml` - Deployment & cache headers

## Summary

✅ **Automatic version detection**
✅ **User-friendly notification**
✅ **Optimal caching performance**
✅ **Fallback cache expiry (1 hour)**
✅ **Multi-language support**

Now when you deploy, users will automatically know about it! 🎉
