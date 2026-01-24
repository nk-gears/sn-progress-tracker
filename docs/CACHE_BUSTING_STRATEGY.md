# Cache Busting & Auto-Update Strategy

This document explains how the application handles caching and automatically notifies users of new versions.

## Problem Statement

When deploying new versions, browsers cache static assets and may not fetch updated files, resulting in users seeing outdated content even after deployment.

## Solution Overview

The application uses a **multi-layered cache-busting strategy**:

1. **Server-side cache headers** (.htaccess) - Controls browser caching behavior
2. **Version.json file** - Always-fresh version indicator
3. **Client-side version checking** - Detects new versions and notifies users
4. **Vite hash-based assets** - Production assets include hash in filename

---

## How It Works

### 1. Server-Side Cache Control (.htaccess)

The deployment workflow creates `.htaccess` with specific cache headers:

#### HTML Files
```apache
# 1 hour cache for index.html
Header set Cache-Control "public, max-age=3600, must-revalidate"
```
- Users get fresh HTML every hour
- They won't be stuck with old code for extended periods

#### version.json
```apache
# Never cache version.json - always fetch fresh
Header set Cache-Control "public, max-age=0, must-revalidate, no-cache, no-store"
Header set Pragma "no-cache"
```
- Always fetches fresh version info from server
- No cache bypass possible

#### Static Assets (JS/CSS)
```apache
# Long cache for hashed assets (Vite includes hash in filename)
Header set Cache-Control "public, max-age=31536000, immutable"
```
- Safe because Vite appends content hash to filenames
- `app.abc123.js` vs `app.def456.js` = different files
- Browser caches indefinitely since filename changes with content

#### Images
```apache
# 1 year cache for images
ExpiresByType image/png "access plus 1 year"
ExpiresByType image/svg+xml "access plus 1 year"
```
- Images rarely change
- Long cache is safe

### 2. Version Detection System

#### version.json File

During deployment, GitHub Actions creates `version.json`:

```json
{
  "version": "1.2.0",
  "timestamp": 1643024400000,
  "deployed": "2026-01-24T12:00:00Z"
}
```

This file is created fresh with each deployment and **never cached** by the browser.

#### Client-Side Monitoring

**File**: `src/composables/useVersionCheck.ts`

The composable:

1. **On App Load**
   - Fetches `version.json` from server
   - Compares with stored version in localStorage
   - If different, sets `newVersionAvailable = true`

2. **Periodic Checking**
   - Checks every 5 minutes (configurable)
   - Fetches version.json with cache-busting `?t=timestamp` parameter
   - Notifies user if new version is detected

3. **Storage**
   - Stores current version in localStorage
   - localStorage key: `appVersion`
   - Survives browser sessions/page reloads

#### Update Notification Component

**File**: `src/components/UpdateNotification.vue`

When new version is detected:

```
┌─────────────────────────────────────────────┐
│ ✨ New version available!                   │
│ A new version of the app has been released. │
│                                             │
│  [Later]     [Refresh]                      │
└─────────────────────────────────────────────┘
```

**User Actions:**
- **Later**: Dismiss notification, check again in 5 minutes
- **Refresh**: Hard-refresh page with `window.location.href`

---

## Cache Hierarchy

```
╔════════════════════════════════════════════════════════════╗
║                    CACHE STRATEGY                          ║
╠════════════════════════════════════════════════════════════╣
║ File Type          │ Cache Duration      │ Reason         ║
╠════════════════════════════════════════════════════════════╣
║ version.json       │ Never (0 seconds)   │ Always fresh   ║
║ index.html         │ 1 hour              │ Moderate sync  ║
║ CSS/JS (hashed)    │ 1 year (indefinite) │ Safe via hash   ║
║ Images             │ 1 year              │ Stable content ║
║ Fonts              │ 1 year              │ Stable content ║
╚════════════════════════════════════════════════════════════╝
```

---

## User Experience Timeline

### Scenario: User has v1.0.0, New v1.1.0 is deployed

```
Time 0:00 - User opens app
├─ Browser loads index.html (cached from 1h ago, still valid)
├─ App loads from cache
├─ useVersionCheck runs
├─ Fetches version.json from server
├─ Detects: stored v1.0.0 ≠ server v1.1.0
└─ Shows notification: "New version available"

Time 0:05 - Periodic check runs
├─ Fetches version.json again (still v1.1.0)
├─ Notification remains visible
└─ User can click "Refresh"

User clicks "Refresh"
├─ Hard refresh: window.location.href
├─ Clears browser cache
├─ Downloads fresh index.html
├─ Vite assets loaded (new hash = new files)
├─ localStorage updated: appVersion = v1.1.0
└─ User sees latest version
```

### Scenario: User browses for 1+ hours without closing

```
Time 1:05 - HTML cache expires
├─ Periodic version check runs
├─ Fetches version.json (detects new version)
├─ Shows notification
└─ OR: Next page load automatically gets fresh HTML
```

---

## GitHub Actions Deployment

### What Happens During Deployment

```yaml
# GitHub Actions Workflow

1. Detect Version
   └─ Read version.txt

2. Build Web App
   └─ NODE_ENV=production npm run build

3. Create version.json
   └─ version.json with current version & timestamp

4. Create .htaccess
   └─ Set cache headers as described above

5. Deploy to FTP
   └─ Upload all files to /public_html/sn-join/
```

### File Deployment Example

```
Before Deployment (Server has v1.0.0):
├─ app.abc123.js (v1.0.0)
├─ app.abc123.css (v1.0.0)
├─ index.html (v1.0.0)
└─ version.json: {"version": "1.0.0"}

After Deployment (v1.1.0):
├─ app.def456.js (v1.1.0) ← Different hash = new file
├─ app.def456.css (v1.1.0) ← Different hash = new file
├─ index.html (v1.1.0)
└─ version.json: {"version": "1.1.0"} ← Updated
```

---

## Configuration

### Change Check Interval

In `src/App.vue`:

```typescript
// Check for updates every 5 minutes
const { showUpdateNotification, refreshPage } = useVersionCheck(5 * 60 * 1000)

// Change to 2 minutes
const { showUpdateNotification, refreshPage } = useVersionCheck(2 * 60 * 1000)
```

### Change HTML Cache Duration

In `.github/workflows/deploy-web-app.yml`:

```apache
# Current: 1 hour
<FilesMatch "\.html$">
    Header set Cache-Control "public, max-age=3600, must-revalidate"
</FilesMatch>

# Change to 30 minutes
<FilesMatch "\.html$">
    Header set Cache-Control "public, max-age=1800, must-revalidate"
</FilesMatch>
```

---

## How to Deploy with Cache Busting

### Standard Deployment

```bash
# 1. Update version
echo "1.2.0" > version.txt

# 2. Commit and push
git add version.txt
git commit -m "Release v1.2.0"
git push origin main

# GitHub Actions automatically:
# ✅ Builds with v1.2.0
# ✅ Creates version.json with v1.2.0
# ✅ Creates .htaccess with cache headers
# ✅ Deploys everything
# ✅ Users are notified of new version
```

---

## Troubleshooting

### Issue: Still seeing old version after deployment

**Solution 1: Wait for notification**
- Browser caches HTML for 1 hour
- App checks version every 5 minutes
- New version should be detected within 5 minutes
- Click "Refresh" in notification

**Solution 2: Force hard refresh**
- User can manually press `Ctrl+Shift+R` (Windows/Linux) or `Cmd+Shift+R` (Mac)
- This clears entire cache and reloads

**Solution 3: Check version.json exists**
```bash
# Test on production
curl https://happy-village.org/sn-join/version.json
# Should return: {"version": "X.Y.Z", ...}
```

### Issue: Notification not appearing

**Possible causes:**
1. version.json not deployed correctly
2. Browser cache is serving old HTML
3. Composable not initialized

**Debug:**
```javascript
// Open browser DevTools Console
// Should show logs like:
// "📦 Latest version: {version: "1.2.0", ...}"
// "🆕 New version available! Current: 1.1.0, Latest: 1.2.0"
```

---

## Best Practices

### ✅ Do This

1. **Always update version.txt** when deploying
   ```bash
   echo "1.2.0" > version.txt
   ```

2. **Use semantic versioning**
   - `1.0.0` - First release
   - `1.0.1` - Bug fix
   - `1.1.0` - New features
   - `2.0.0` - Major changes

3. **Test locally before deploying**
   ```bash
   npm run build
   npm run preview
   ```

4. **Monitor user updates**
   - Check browser console for version logs
   - Users should see notification within 5 minutes

### ❌ Don't Do This

1. **Don't rely on users clearing cache manually**
   - The system handles it automatically

2. **Don't deploy without updating version.txt**
   - Users won't know about the update

3. **Don't modify .htaccess manually**
   - It's generated automatically during deployment

---

## Performance Impact

| Strategy | Users | Performance |
|----------|-------|-------------|
| All cache (old way) | 100% | Fast, but outdated |
| No cache (bad) | 100% | Slow, always fresh |
| **Smart cache (new way)** | **95% fast** + **5% get update** | **Optimal** |

---

## Files Involved

```
.github/workflows/deploy-web-app.yml
├─ Creates version.json
├─ Creates .htaccess with headers
└─ Deploys to server

src/composables/useVersionCheck.ts
├─ Fetches version.json
├─ Detects new versions
└─ Manages check interval

src/components/UpdateNotification.vue
├─ Shows notification UI
├─ Handles user actions
└─ Triggers refresh

src/App.vue
├─ Initializes version check
├─ Shows notification
└─ Handles refresh callback
```

---

## Monitoring & Metrics

Check version deployment:

```bash
# Test version.json endpoint
curl -i https://happy-village.org/sn-join/version.json

# Check cache headers
curl -i https://happy-village.org/sn-join/index.html
# Look for: Cache-Control: public, max-age=3600, must-revalidate

curl -i https://happy-village.org/sn-join/version.json
# Look for: Cache-Control: public, max-age=0, must-revalidate, no-cache, no-store
```

---

## Summary

The cache-busting system ensures:

✅ **Optimal Performance** - Static assets cached for long periods
✅ **Fresh Content** - version.json always fresh, users notified of updates
✅ **Auto-Detection** - App automatically detects new versions
✅ **User Control** - Users decide when to refresh
✅ **Fallback** - Even if ignored, old HTML expires after 1 hour

**Result**: Users get the latest version within minutes of deployment! 🚀
