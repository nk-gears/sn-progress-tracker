# Configuration Guide

This document explains how to configure the application for different environments.

## Frontend Configuration

The frontend uses environment variables to configure the API endpoints at runtime.

### Configuration File: `config.ts`

Location: `/web-app/src/config.ts`

This file exports all API endpoints and configuration constants:

```typescript
import { config, getApiUrl } from '@/config'

// Get base URL
const baseUrl = config.api.baseUrl

// Get full endpoint URL
const centerAddressesUrl = getApiUrl('centerAddresses')
```

**Available endpoints:**
- `auth` - Authentication
- `participants` - Participant management
- `sessions` - Session management
- `dashboard` - Dashboard analytics
- `profile` - User profile
- `centerAddresses` - Center addresses (NEW)
- `branches` - Branches list
- `eventRegister` - Event registration
- And more...

### Environment Variables

Configuration is controlled via `.env` files:

#### Development: `.env`

```env
# Development environment variables
VITE_API_MODE=real
VITE_API_BASE_URL=http://localhost/backend
VITE_ENABLE_API_SWITCHER=false
VITE_HIDE_NAV_TABS=false
```

#### Production: `.env.production`

```env
# Production environment variables
VITE_API_MODE=real
VITE_API_BASE_URL=/sn-progress/backend
VITE_ENABLE_API_SWITCHER=false
VITE_ENABLE_MOCK_DELAY=false
```

### Changing the API Base URL

To change where the frontend points to:

1. **For local development:**
   ```env
   VITE_API_BASE_URL=http://localhost/backend
   ```

2. **For remote server:**
   ```env
   VITE_API_BASE_URL=https://api.example.com/backend
   ```

3. **For subdirectory deployment:**
   ```env
   VITE_API_BASE_URL=/sn-progress/backend
   ```

### How It Works

1. Environment variables are read from `.env` files at build time
2. The `config.ts` file uses `import.meta.env.VITE_API_BASE_URL` to get the URL
3. Components import `getApiUrl()` to construct full endpoint URLs
4. All API calls use the configured base URL

Example in a component:

```typescript
import { getApiUrl } from '@/config'

// Get the full URL
const url = getApiUrl('centerAddresses')
// Result: http://localhost/backend/api.php/center-addresses

// Use in fetch
const response = await fetch(url)
```

---

## Google Apps Script Configuration

The Google Apps Script for syncing centers from Google Sheets is also configurable.

### Configuration: `google-appscript-sync.gs`

```javascript
const CONFIG = {
  // API Configuration
  API_BASE_URL: "http://localhost/backend",
  API_ENDPOINT: "api.php/center-addresses",

  // Sheet Configuration
  SHEET_NAME: "Sheet1",
  HEADER_ROW: 1,
};
```

### Changing the API Endpoint

To change where the Google Apps Script syncs data to:

1. **Open your Google Sheet**
2. Click `Extensions > Apps Script`
3. Find the line with `API_BASE_URL`
4. Update it to your backend URL:

**Examples:**
```javascript
// Local development
API_BASE_URL: "http://localhost/backend"

// Remote server
API_BASE_URL: "https://api.example.com/backend"

// Subdirectory
API_BASE_URL: "https://example.com/sn-progress/backend"
```

5. Click `Save`
6. Refresh your Google Sheet
7. Click `🔄 Sync Centers > Sync to API`

---

## Backend Configuration

The backend API (`api.php`) uses the same database for all environments. No additional configuration is needed at the API level.

### API Endpoints

All endpoints are available at:
```
{API_BASE_URL}/api.php/{endpoint}
```

Example:
```
http://localhost/backend/api.php/center-addresses
```

---

## Deployment Checklist

### Development Environment
- [ ] Set `VITE_API_BASE_URL` to local backend URL
- [ ] Set `VITE_API_MODE=real`
- [ ] Run `npm run dev` to start development server
- [ ] Backend should be running on the same or configured URL

### Production Environment
- [ ] Update `VITE_API_BASE_URL` in `.env.production`
- [ ] Build with `npm run build`
- [ ] Deploy built files to web server
- [ ] Update Google Apps Script `API_BASE_URL` if using it
- [ ] Test API endpoints before going live

### Common Issues

**"API endpoint not reachable" error**
- Check that `VITE_API_BASE_URL` is correct
- Verify the backend is running
- Check CORS headers if using different domain

**"Centers not loading" in frontend**
- Check browser console for the actual URL being called
- Verify database has data via API directly: `curl {API_BASE_URL}/api.php/center-addresses`
- Check that `getApiUrl('centerAddresses')` is being used

**Google Apps Script fails to sync**
- Check `API_BASE_URL` in the script
- Verify the API is accessible from Google's servers
- Check logs: `Extensions > Apps Script > Execution`

---

## Quick Start

### For Development

1. **Update `.env` if needed:**
   ```env
   VITE_API_BASE_URL=http://localhost/backend
   ```

2. **Start frontend:**
   ```bash
   cd web-app
   npm run dev
   ```

3. **The app will use the configured URL automatically**

### For Google Sheets Sync

1. **Open `google-appscript-sync.gs`**
2. **Set API_BASE_URL:**
   ```javascript
   API_BASE_URL: "http://localhost/backend"
   ```
3. **Click 🔄 Sync Centers > Sync to API**

---

## Summary

- **Frontend**: Configure via `VITE_API_BASE_URL` in `.env` files
- **Google Apps Script**: Configure `API_BASE_URL` in `CONFIG` object
- **Backend**: No configuration needed (uses database)
- **All changes take effect immediately** (no restart needed for frontend, Apps Script)
