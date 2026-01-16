# Settings Configuration Guide

## Overview

The application now has a centralized settings management system using the `medt_settings` table. This allows for dynamic configuration of values like WhatsApp links without needing to redeploy the application.

## Database Table: medt_settings

### Schema

```sql
CREATE TABLE IF NOT EXISTS medt_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(255) NOT NULL UNIQUE,
    key_value TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    description VARCHAR(500),
    INDEX idx_key_name (key_name),
    INDEX idx_is_active (is_active)
);
```

### Columns

| Column | Type | Required | Description |
|--------|------|----------|-------------|
| id | INT | Yes | Auto-incrementing primary key |
| key_name | VARCHAR(255) | Yes | Unique setting key (e.g., 'whatsapp-link') |
| key_value | TEXT | Yes | The setting value (e.g., URL) |
| is_active | TINYINT | No | Flag to enable/disable setting (default: 1) |
| created_at | TIMESTAMP | No | Auto-set on creation |
| updated_at | TIMESTAMP | No | Auto-updated on modification |
| description | VARCHAR(500) | No | Human-readable description of the setting |

## Current Settings

### WhatsApp Link

**Key Name:** `whatsapp-link`

**Purpose:** Store the WhatsApp group link to be displayed in the event registration success message.

**Default Value:** `https://chat.whatsapp.com/CmYtLn6dJU4IzsE4EOtAv0`

**Example Query:**

```sql
-- View the active WhatsApp link
SELECT key_value FROM medt_settings
WHERE key_name = 'whatsapp-link' AND is_active = 1;

-- Update the WhatsApp link
UPDATE medt_settings
SET key_value = 'https://chat.whatsapp.com/NEW_LINK_HERE'
WHERE key_name = 'whatsapp-link';

-- Disable the WhatsApp link
UPDATE medt_settings
SET is_active = 0
WHERE key_name = 'whatsapp-link';
```

## API Endpoint: GET /api/whatsapp-link

### Description
Retrieves the active WhatsApp link from the settings table.

### Request
```bash
curl -X GET http://localhost:8080/api.php/whatsapp-link
```

### Response (Success)
```json
{
  "success": true,
  "data": {
    "link": "https://chat.whatsapp.com/CmYtLn6dJU4IzsE4EOtAv0",
    "key": "whatsapp-link"
  }
}
```

### Response (Error - Link Not Configured)
```json
{
  "success": false,
  "message": "WhatsApp link not configured"
}
```

### Response (Error - Server Error)
```json
{
  "success": false,
  "message": "Failed to fetch WhatsApp link"
}
```

## Frontend Implementation

### Component: JoinEventModal.vue

The modal now:
1. Fetches the WhatsApp link on component mount
2. Displays a "Join Our WhatsApp Group" button after successful registration
3. Opens the WhatsApp link in a new tab when clicked
4. Shows the button only if the link is successfully fetched

### Code Example

```typescript
// Fetch WhatsApp link from API
const fetchWhatsAppLink = async () => {
  try {
    const url = getApiUrl('eventRegister').replace('/event-register', '/whatsapp-link')
    const response = await fetch(url)
    const data = await response.json()

    if (data.success && data.data?.link) {
      whatsappLink.value = data.data.link
    }
  } catch (err) {
    console.error('Error fetching WhatsApp link:', err)
  }
}
```

## How to Add New Settings

### Step 1: Create the Setting in Database

```sql
INSERT INTO medt_settings (key_name, key_value, is_active, description)
VALUES ('setting_key', 'setting_value', 1, 'Description of the setting');
```

### Step 2: Create API Endpoint (Optional)

Create a GET endpoint to fetch the setting:

```php
function handleSettingKey() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(['success' => false, 'message' => 'Only GET method allowed'], 405);
        return;
    }

    try {
        $setting = fetchRow(
            "SELECT key_value FROM medt_settings WHERE key_name = ? AND is_active = 1",
            ['setting_key'],
            's'
        );

        if ($setting && !empty($setting['key_value'])) {
            sendResponse(['success' => true, 'data' => ['value' => $setting['key_value']]]);
        } else {
            sendResponse(['success' => false, 'message' => 'Setting not configured'], 404);
        }
    } catch (Exception $e) {
        error_log('Setting error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to fetch setting'], 500);
    }
}
```

### Step 3: Use in Frontend

```typescript
const fetchSetting = async () => {
  const response = await fetch('/api.php/setting-key')
  const data = await response.json()
  if (data.success) {
    settingValue.value = data.data.value
  }
}
```

## Management Commands

### View All Active Settings

```sql
SELECT key_name, key_value, description FROM medt_settings WHERE is_active = 1;
```

### View All Settings (Including Inactive)

```sql
SELECT * FROM medt_settings ORDER BY key_name;
```

### Disable a Setting

```sql
UPDATE medt_settings SET is_active = 0 WHERE key_name = 'setting_key';
```

### Enable a Setting

```sql
UPDATE medt_settings SET is_active = 1 WHERE key_name = 'setting_key';
```

### Delete a Setting

```sql
DELETE FROM medt_settings WHERE key_name = 'setting_key';
```

## Language Support

All UI text related to WhatsApp join button is translatable via i18n:

**Key:** `joinEvent.joinGroup`

**Available in:**
- English: "Join Our WhatsApp Group"
- Tamil: "எங்கள் WhatsApp குழுவில் சேரவும்"
- Malayalam: "ഞങ്ങളുടെ WhatsApp ഗ്രൂപ്പിൽ ചേരുക"

## Troubleshooting

### WhatsApp Link Button Not Appearing

**Cause:** Setting not configured or inactive

**Solution:**
```sql
-- Check if setting exists and is active
SELECT * FROM medt_settings WHERE key_name = 'whatsapp-link';

-- If not found, insert it
INSERT INTO medt_settings (key_name, key_value, is_active, description)
VALUES ('whatsapp-link', 'https://chat.whatsapp.com/YOUR_LINK', 1, 'WhatsApp group link');

-- If found but inactive, activate it
UPDATE medt_settings SET is_active = 1 WHERE key_name = 'whatsapp-link';
```

### API Returns 404 Error

**Cause:** No active setting found

**Solution:** Ensure the setting exists with `is_active = 1`

### Check Browser Console

If the button still doesn't appear, check browser console (F12) for errors:
- Network tab: Verify the API call succeeded
- Console tab: Look for fetch errors

## Best Practices

1. **Always use unique key_name values** - This prevents duplicate settings
2. **Set meaningful descriptions** - Helps with database administration
3. **Use is_active flag** - Instead of deleting settings, deactivate them for audit trails
4. **Validate in frontend** - Check if link is valid before displaying button
5. **Cache API responses** - Consider caching the WhatsApp link at app startup for performance
6. **Monitor changes** - Use updated_at timestamp to track when settings were modified

## Future Settings

The system is designed to be extensible. Future settings could include:

- `app-banner-message` - Promotional banner text
- `event-registration-enabled` - Enable/disable registrations
- `contact-email` - Contact email for inquiries
- `privacy-policy-link` - Privacy policy URL
- `terms-of-service-link` - Terms of service URL
- `support-phone` - Support phone number
