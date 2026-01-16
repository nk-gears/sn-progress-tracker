# Deployment Scripts Comparison

## Overview

This project has **two separate deployment scripts** for different purposes:

## 1. `deploy.sh` - Admin App + Backend API

### What it deploys:
- ✅ Admin frontend (Vue.js) from `frontend/` folder
- ✅ Backend API (PHP) from `backend/` folder
- ✅ Database integration

### Deployment path:
```
/public_html/sn-progress/
├── index.html          # Admin app
├── assets/
└── api/
    └── api.php         # Backend API
```

### Who uses it:
- **Volunteer coordinators** (login required)
- Internal admin users

### Features:
- Participant management
- Session recording
- Dashboard analytics
- Branch management

### When to use:
```bash
# When you modify admin app or backend
./deploy.sh production
```

---

## 2. `deploy-public-app.sh` - Public Web App

### What it deploys:
- ✅ Public website (Vue.js) from `web-app/` folder
- ❌ No backend (uses existing API from deploy.sh)

### Deployment path:
```
/public_html/sn-join/
├── index.html          # Public website
├── assets/
└── .htaccess
```

### Who uses it:
- **General public** (no login)
- Event participants

### Features:
- Event information
- Centre finder (Near Me / Search / Browse)
- Registration form
- FAQs

### When to use:
```bash
# When you modify public website
./deploy-public-app.sh production
```

---

## Quick Comparison Table

| Feature | deploy.sh | deploy-public-app.sh |
|---------|-----------|---------------------|
| **Source Folder** | `frontend/` + `backend/` | `web-app/` |
| **Target Path** | `/public_html/sn-progress` | `/public_html/sn-join` |
| **Frontend** | Admin Vue.js app | Public Vue.js app |
| **Backend** | ✅ Includes PHP API | ❌ No backend |
| **Access** | Login required | Public access |
| **Users** | Volunteers | General public |
| **Build Command** | `cd frontend && npm run build` | `cd web-app && npm run build` |
| **URL** | `domain.com/sn-progress` | `domain.com/sn-join` |

---

## Deployment Workflow

### Scenario 1: Updated Admin App Only
```bash
# Only deploy admin app
./deploy.sh production
```

### Scenario 2: Updated Public Website Only
```bash
# Only deploy public website
./deploy-public-app.sh production
```

### Scenario 3: Updated Backend API
```bash
# Deploy admin app (includes backend)
./deploy.sh production

# Public website will automatically use updated API
# No need to redeploy public website
```

### Scenario 4: Updated Both Apps
```bash
# Deploy admin app first (includes backend)
./deploy.sh production

# Then deploy public website
./deploy-public-app.sh production
```

---

## Shared Resources

### API Endpoint
Both apps use the **same backend API**:
```
/public_html/sn-progress/api/api.php
```

**Admin App:** Uses API for all operations
**Public Web App:** Uses API for:
- Fetching centres list
- Event registration

### Database
Both apps share the **same MySQL database**:
- Admin adds/manages participants and sessions
- Public registers new participants via API

---

## Configuration Differences

### Admin App API URL (`frontend/`)
```javascript
// Uses relative path or environment variable
const API_BASE_URL = '../backend'
```

### Public App API URL (`web-app/`)
```html
<!-- Configured in index.html -->
<script>
  window.APP_CONFIG = {
    API_BASE_URL: 'https://yourdomain.com/sn-progress/api/api.php'
  };
</script>
```

**Important:** The public app needs absolute URL to the API!

---

## FTP Directory Structure

After deploying both:

```
/public_html/
│
├── sn-progress/              # Deploy: ./deploy.sh
│   ├── index.html           # Admin login page
│   ├── assets/              # Admin app assets
│   │   ├── index-abc.js
│   │   └── index-def.css
│   └── api/                 # Backend API
│       ├── api.php          # Main API file
│       └── config.php       # DB config (preserved)
│
└── sn-join/                 # Deploy: ./deploy-public-app.sh
    ├── index.html           # Public homepage
    ├── assets/              # Public app assets
    │   ├── index-xyz.js
    │   └── index-uvw.css
    ├── favicon.svg
    └── .htaccess            # SPA routing
```

---

## When to Deploy What

### ✅ Use `deploy.sh` when:
- Changed admin dashboard UI
- Modified participant management
- Updated session recording
- Changed backend API logic
- Updated database queries
- Modified authentication

### ✅ Use `deploy-public-app.sh` when:
- Changed public website design
- Updated event information
- Modified centre finder
- Changed registration form UI
- Updated FAQ content
- Modified homepage

### ✅ Use both when:
- API response format changed (affects both apps)
- Database schema updated (may affect both apps)
- Major feature added to both sides

---

## Important Notes

### 1. API Dependencies
The public web app **depends on** the backend API deployed by `deploy.sh`:
- Always deploy admin app first if API changed
- Public app will fail without API

### 2. Independent Frontend Builds
The two apps are **completely separate Vue.js projects**:
- Different dependencies
- Different configurations
- Different routes
- No shared code

### 3. CORS Configuration
Backend API must allow **both domains**:
```php
// In backend/config.php
$allowed_origins = [
    'http://localhost:8080',
    'https://yourdomain.com',
    'https://yourdomain.com/sn-join'  // Public app
];
```

### 4. Database Tables
Shared database tables:
- `medt_branches` - Used by both
- `medt_event_register` - Public app writes, admin can read
- `medt_participants` - Admin manages, public may create
- `medt_users` - Admin only

---

## Deployment Checklist

### Before Deploying Admin App
- [ ] Test admin login locally
- [ ] Test participant CRUD operations
- [ ] Test session recording
- [ ] Verify database migrations applied
- [ ] Check backend API responses

### Before Deploying Public App
- [ ] Test event information displays
- [ ] Test centre finder (all 3 tabs)
- [ ] Test registration form
- [ ] Verify API URL is correct in index.html
- [ ] Check CORS configuration
- [ ] Test on mobile devices

### After Both Deployments
- [ ] Test admin app live
- [ ] Test public app live
- [ ] Verify registration data reaches database
- [ ] Check both apps can access API
- [ ] Monitor for errors

---

## Rollback Strategy

### Rollback Admin App
```bash
git checkout <previous-commit>
./deploy.sh production --force
git checkout main
```

### Rollback Public App
```bash
git checkout <previous-commit>
./deploy-public-app.sh production --force
git checkout main
```

**Note:** Backend rollback may affect both apps!

---

## Summary

| Task | Command |
|------|---------|
| Deploy admin + API | `./deploy.sh production` |
| Deploy public website | `./deploy-public-app.sh production` |
| Deploy to staging | `./deploy.sh staging` or `./deploy-public-app.sh staging` |
| Force full deploy | Add `--force` flag |

**Remember:** The public web app is just the frontend. It depends on the backend API deployed by `deploy.sh`.
