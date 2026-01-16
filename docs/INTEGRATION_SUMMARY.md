# Event Registration Integration Summary

## ✅ Completed Tasks

### 1. Database Setup
**File Created:** `database/event_register_table.sql`

- Created new table `medt_event_register` to store registration data
- Fields: id, name, mobile, centre_id, created_at, updated_at
- Includes proper foreign key constraints and indexes
- Follows the existing naming convention with `medt_` prefix

**To Apply:**
```bash
mysql -u mediuser -p meditation_tracker < database/event_register_table.sql
```

### 2. Backend API Endpoint
**File Modified:** `backend/api.php`

Added new public endpoint for event registration:
- **Endpoint:** `POST /api/event-register`
- **Handler Function:** `handleEventRegister()`
- **Validations:**
  - Name must contain only letters and spaces
  - Mobile number must be exactly 10 digits
  - Centre ID must exist in the database
- **Response:** Returns success message with registration details

**Testing the Endpoint:**
```bash
# Test API connectivity
curl http://192.168.1.13/sn-progress-app/backend/api.php/branches

# Test registration
curl -X POST http://192.168.1.13/sn-progress-app/backend/api.php/event-register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "mobile": "9876543210",
    "centre_id": 1
  }'
```

### 3. Frontend Integration
**File Modified:** `web-app/src/components/WhatsAppJoinForm.vue`

Major Changes:
- ✅ Integrated with backend API for centre loading
- ✅ Implemented form submission to API endpoint
- ✅ Added "Thank You" message that replaces the form on success
- ✅ Added "Register Another Person" button to reset the form
- ✅ Improved error handling with user-friendly messages
- ✅ Added loading states for better UX
- ✅ Removed hardcoded centre data (now fetched from API)

User Flow:
1. User enters name, mobile, and selects centre
2. Clicks "Join WhatsApp Group" button (can be renamed to "Register")
3. Form submits to API
4. On success: Form disappears, Thank you message appears
5. User can click "Register Another Person" to show form again

### 4. Configuration Management
**Files Modified:**
- `web-app/index.html`
- `web-app/src/components/WhatsAppJoinForm.vue`

Made API_BASE_URL configurable from index.html:
```html
<script>
  window.APP_CONFIG = {
    API_BASE_URL: 'http://192.168.1.13/sn-progress-app/backend/api.php'
  };
</script>
```

**Benefits:**
- Easy to change API URL for different environments
- No need to rebuild the app for URL changes
- Just edit index.html in production deployment

### 5. Documentation
**Files Created:**
- `EVENT_REGISTRATION_SETUP.md` - Complete setup and testing guide
- `INTEGRATION_SUMMARY.md` - This summary file

## 🔧 Configuration for Different Environments

### Local Development
In `index.html`:
```javascript
window.APP_CONFIG = {
  API_BASE_URL: 'http://192.168.1.13/sn-progress-app/backend/api.php'
};
```

### Production
In `index.html`:
```javascript
window.APP_CONFIG = {
  API_BASE_URL: 'https://yourdomain.com/api/api.php'
};
```

## 📋 Deployment Checklist

1. **Database Setup**
   ```bash
   # Run the migration on production database
   mysql -u your_user -p your_database < database/event_register_table.sql
   ```

2. **Backend Configuration**
   - Update database credentials in `backend/config.php`
   - Update CORS allowed origins for production domain
   - Test API endpoints

3. **Frontend Configuration**
   - Update `API_BASE_URL` in `index.html` to production URL
   - Build the application: `npm run build`
   - Deploy the `dist` folder

4. **Testing**
   - Test centre loading
   - Test form submission
   - Verify data in database
   - Test error scenarios

## 🎯 Next Steps (Optional Enhancements)

1. **Change Button Text**
   Currently says "Join WhatsApp Group" - can be changed to "Register" in the translation file or component.

2. **Email Notifications**
   Add email notification when someone registers:
   ```php
   // In handleEventRegister() after successful registration
   mail($admin_email, 'New Registration', $message);
   ```

3. **Admin Dashboard**
   Create an endpoint to view registrations in the admin app:
   ```php
   case 'registrations':
       handleRegistrations(); // List all registrations
       break;
   ```

4. **Export to CSV**
   Add functionality to export registrations to CSV for offline use.

5. **SMS Confirmation**
   Send SMS confirmation to users after registration (requires SMS gateway).

6. **Rate Limiting**
   Add rate limiting to prevent spam registrations.

## 🐛 Known Issues / Considerations

1. **API Path Routing**: The current nginx configuration might need adjustment for the API routing. The API uses path-based routing like `/api/branches` and `/api/event-register`.

2. **CORS in Production**: Ensure production domain is added to the CORS allowed origins list in `backend/config.php`.

3. **Mobile Number Uniqueness**: Currently allows duplicate registrations with the same mobile number. Consider adding a unique constraint if needed.

4. **Data Privacy**: Consider adding a privacy policy checkbox before submission.

## 📊 Viewing Registration Data

### SQL Queries

**View all registrations:**
```sql
SELECT
    er.id,
    er.name,
    er.mobile,
    b.name as centre_name,
    er.created_at
FROM medt_event_register er
JOIN medt_branches b ON er.centre_id = b.id
ORDER BY er.created_at DESC;
```

**Count by centre:**
```sql
SELECT
    b.name as centre_name,
    COUNT(*) as registration_count
FROM medt_event_register er
JOIN medt_branches b ON er.centre_id = b.id
GROUP BY b.id, b.name
ORDER BY registration_count DESC;
```

**Recent registrations (last 24 hours):**
```sql
SELECT
    er.name,
    er.mobile,
    b.name as centre_name,
    er.created_at
FROM medt_event_register er
JOIN medt_branches b ON er.centre_id = b.id
WHERE er.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY er.created_at DESC;
```

## 🔐 Security Features Implemented

1. ✅ Input validation on both client and server
2. ✅ SQL injection prevention using prepared statements
3. ✅ XSS protection through proper data escaping
4. ✅ CORS configuration for authorized domains only
5. ✅ Mobile number format validation
6. ✅ Name sanitization (only letters and spaces)

## 📞 Support

If you encounter any issues:
1. Check browser console for frontend errors
2. Check PHP error logs: `tail -f /var/log/nginx/error.log`
3. Check database connection: Test the `/branches` endpoint
4. Verify table exists: `SHOW TABLES LIKE 'medt_event_register';`

---

**Integration completed successfully! ✅**
All files have been modified and are ready for testing and deployment.
