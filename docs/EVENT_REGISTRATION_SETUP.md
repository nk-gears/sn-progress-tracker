# Event Registration Setup Guide

## Overview
This document describes the event registration feature that allows users to register for events through the public website. The data is stored in the shared database and can be accessed by both the admin app and the public web app.

## Database Setup

### 1. Create the Event Registration Table

Run the following SQL script on your database:

```bash
mysql -u mediuser -p meditation_tracker < database/event_register_table.sql
```

Or manually execute:
```sql
USE meditation_tracker;

CREATE TABLE IF NOT EXISTS medt_event_register (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    centre_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (centre_id) REFERENCES medt_branches(id) ON DELETE CASCADE,
    INDEX idx_mobile (mobile),
    INDEX idx_centre_date (centre_id, created_at)
);
```

## API Endpoints

### 1. Get All Branches (Public)
**Endpoint:** `GET /api/branches`

**Response:**
```json
{
  "success": true,
  "branches": [
    {
      "id": 1,
      "name": "Chennai Central Branch"
    },
    ...
  ]
}
```

### 2. Event Registration (Public)
**Endpoint:** `POST /api/event-register`

**Request Body:**
```json
{
  "name": "John Doe",
  "mobile": "9876543210",
  "centre_id": 1
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registration successful! Thank you for registering.",
  "data": {
    "id": 123,
    "name": "John Doe",
    "centre": "Chennai Central Branch"
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Mobile number must be exactly 10 digits"
}
```

## Frontend Integration

### Configuration

Update the API base URL in `src/components/WhatsAppJoinForm.vue`:

```typescript
const API_BASE_URL = 'http://your-api-domain.com/backend/api.php'
```

For local development:
```typescript
const API_BASE_URL = 'http://192.168.1.13/sn-progress-app/backend/api.php'
```

For production:
```typescript
const API_BASE_URL = 'https://yourdomain.com/api/api.php'
```

### Component Features

1. **Auto-load Centres**: Centres are automatically fetched from the API when the page loads
2. **Form Validation**:
   - Name must contain only letters and spaces
   - Mobile number must be exactly 10 digits
   - Centre selection is required
3. **Success Flow**:
   - Form is hidden after successful submission
   - Thank you message is displayed
   - Option to register another person
4. **Error Handling**:
   - User-friendly error messages
   - Form remains visible for corrections

## Testing

### 1. Test Database Connection

```bash
# Test that the API can connect to the database
curl http://192.168.1.13/sn-progress-app/backend/api.php/branches
```

Expected output:
```json
{
  "success": true,
  "branches": [...]
}
```

### 2. Test Event Registration

```bash
curl -X POST http://192.168.1.13/sn-progress-app/backend/api.php/event-register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "mobile": "9876543210",
    "centre_id": 1
  }'
```

Expected output:
```json
{
  "success": true,
  "message": "Registration successful! Thank you for registering.",
  "data": {...}
}
```

### 3. Verify Database Entry

```sql
SELECT * FROM medt_event_register ORDER BY created_at DESC LIMIT 10;
```

## CORS Configuration

The API is configured to allow cross-origin requests from:
- `http://localhost:8080`
- `http://127.0.0.1:8080`

For production, update `backend/config.php`:

```php
$allowed_origins = [
    'http://localhost:8080',
    'https://yourdomain.com',
    'https://www.yourdomain.com'
];
```

## Security Considerations

1. **Input Validation**: All inputs are validated on both client and server side
2. **SQL Injection Prevention**: Uses prepared statements throughout
3. **XSS Protection**: Data is properly escaped before database insertion
4. **Rate Limiting**: Consider adding rate limiting for production (not implemented yet)

## Accessing Registration Data

### Via Database Query

```sql
-- Get all registrations
SELECT
    er.id,
    er.name,
    er.mobile,
    b.name as centre_name,
    er.created_at
FROM medt_event_register er
JOIN medt_branches b ON er.centre_id = b.id
ORDER BY er.created_at DESC;

-- Get registrations by centre
SELECT
    er.name,
    er.mobile,
    er.created_at
FROM medt_event_register er
WHERE er.centre_id = 1
ORDER BY er.created_at DESC;

-- Get registration count by centre
SELECT
    b.name as centre_name,
    COUNT(*) as registration_count
FROM medt_event_register er
JOIN medt_branches b ON er.centre_id = b.id
GROUP BY b.id, b.name
ORDER BY registration_count DESC;
```

### Future Enhancement: Admin Dashboard

Consider adding an endpoint to view registrations in the admin app:

```php
// In api.php - add a new endpoint for admin users
case 'registrations':
    handleRegistrations();
    break;
```

## Troubleshooting

### Issue: CORS Error

**Solution**:
- Check that your domain is added to `$allowed_origins` in `config.php`
- Ensure the API is responding with proper CORS headers

### Issue: "Failed to load centres"

**Solution**:
- Verify the API URL is correct
- Check that the database connection is working
- Ensure there are branches in the `medt_branches` table

### Issue: "Registration failed"

**Solution**:
- Check that the `medt_event_register` table exists
- Verify the centre_id exists in `medt_branches` table
- Check API error logs for detailed error messages

## Production Deployment Checklist

- [ ] Update API_BASE_URL to production domain
- [ ] Run database migration on production database
- [ ] Update CORS allowed origins
- [ ] Test registration flow end-to-end
- [ ] Verify data is being stored correctly
- [ ] Set up monitoring for registration errors
- [ ] Consider adding rate limiting
- [ ] Set up email notifications for new registrations (optional)

## Support

For issues or questions, check:
1. Browser console for frontend errors
2. PHP error logs for backend errors
3. Database logs for SQL errors
