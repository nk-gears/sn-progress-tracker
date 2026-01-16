# Event Registration Debugging Guide

## Issue: Name appearing empty in database

### Check Database Data

Run this SQL query to inspect the registrations:

```sql
SELECT id, name, mobile, centre_id, created_at FROM medt_event_register ORDER BY id DESC LIMIT 10;
```

Look for:
- **Empty name field** - If name is empty or NULL
- **Mobile number** - Should be 10 digits
- **centre_id** - Should match an ID from medt_center_addresses

### Check API Logs

```bash
# Check PHP error logs (usually in /var/log/apache2/ or /var/log/php-fpm/)
tail -50 /var/log/apache2/error.log
tail -50 /var/log/php-fpm.log
```

### Test the API Endpoint

Use this curl command to test the registration:

```bash
curl -X POST http://localhost:8080/api.php/event-register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "mobile": "9876543210",
    "center_code": "CENTER_001"
  }'
```

**Expected Response (Success):**
```json
{
  "success": true,
  "message": "Registration successful! Thank you for registering.",
  "data": {
    "id": 1,
    "name": "John Doe",
    "centre_code": "CENTER_001",
    "centre_name": "T. Nagar"
  }
}
```

**Expected Response (Error):**
```json
{
  "success": false,
  "message": "Invalid centre selected"
}
```

## Changes Made

### 1. API Changes (backend/api.php)

**Before:**
- Accepted `centre_id` (integer)
- Validated centre exists in `medt_branches`
- Stored centre_id directly

**After:**
- Accepts `center_code` (string)
- Looks up centre in `medt_center_addresses`
- Stores the `id` from `medt_center_addresses` to `centre_id` column
- Validates against actual center addresses

### 2. Frontend Changes

**JoinEventModal.vue:**
- Now sends `center_code` instead of `centre_id`
- Gets center_code from the Centre object

**CentreFinder.vue:**
- Adds `center_code` to the Centre object when transforming API data

**Types (types/index.ts):**
- Added optional `center_code` field to Centre interface

## Troubleshooting

### Problem: "Invalid centre selected" error

**Causes:**
- center_code doesn't exist in medt_center_addresses
- center_code is empty or NULL
- Typo in center_code

**Solution:**
1. Check that centers are synced to database:
   ```sql
   SELECT id, center_code, locality FROM medt_center_addresses LIMIT 5;
   ```
2. Verify the center_code being sent matches exactly (case-sensitive)

### Problem: Name is empty in database

**Causes:**
- Name validation is failing (contains special characters)
- Form is not sending the name
- Database column is set to NOT NULL but receives empty string

**Solution:**
1. Check the form validation in browser console (F12)
2. Ensure name only contains letters and spaces
3. Verify database column accepts text:
   ```sql
   SHOW COLUMNS FROM medt_event_register WHERE Field = 'name';
   ```

### Problem: Mobile number is invalid

**Causes:**
- Not exactly 10 digits
- Contains non-numeric characters
- Leading zeros issue

**Solution:**
- Use exactly 10-digit number
- No spaces, dashes, or country codes (+91)
- Example: `9876543210` (not `+91 9876543210`)

## Database Schema Expected

```sql
CREATE TABLE IF NOT EXISTS medt_event_register (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  mobile VARCHAR(10) NOT NULL,
  centre_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (centre_id) REFERENCES medt_center_addresses(id)
);
```

## API Flow

```
User clicks "Join the Event"
  ↓
Modal opens with Centre info (including center_code)
  ↓
User enters name & mobile
  ↓
Clicks "Join the Event"
  ↓
Frontend validates:
  - Name: letters and spaces only
  - Mobile: exactly 10 digits
  ↓
API receives: {name, mobile, center_code}
  ↓
API validates:
  - Name format
  - Mobile format
  - center_code exists in medt_center_addresses
  ↓
If valid: Insert to medt_event_register
  ↓
Return success with registration ID
```

## Useful SQL Queries

### View all registrations
```sql
SELECT
  r.id,
  r.name,
  r.mobile,
  c.center_code,
  c.locality,
  r.created_at
FROM medt_event_register r
LEFT JOIN medt_center_addresses c ON r.centre_id = c.id
ORDER BY r.created_at DESC;
```

### Count registrations by centre
```sql
SELECT
  c.center_code,
  c.locality,
  COUNT(r.id) as registrations
FROM medt_center_addresses c
LEFT JOIN medt_event_register r ON c.id = r.centre_id
GROUP BY c.id
ORDER BY registrations DESC;
```

### Find registrations with empty/invalid names
```sql
SELECT id, name, mobile, centre_id FROM medt_event_register
WHERE name IS NULL OR name = '' OR name NOT REGEXP '^[A-Za-z\s]+$';
```
