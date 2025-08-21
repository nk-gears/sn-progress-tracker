# User Onboarding API

This API endpoint allows external systems to automatically onboard new users and manage their branch access in the Meditation Tracker application.

## Endpoint

```
POST /api/onboard
```

## Headers

```
Content-Type: application/json
```

## Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Full name of the user |
| `mobile` | string | ✅ | 10-digit mobile number (used as username) |
| `password` | string | ✅ | User password (minimum 6 characters) |
| `branch_name` | string | ✅ | Exact name of the branch to assign user to |
| `email` | string | ❌ | Email address (optional) |

## Response

### Success Response (201/200)

```json
{
  "success": true,
  "message": "User 'John Doe' created successfully",
  "user_id": 123,
  "branch_id": 45,
  "branch_name": "Chennai Central",
  "action": "created"
}
```

### Update Response (200)

```json
{
  "success": true,
  "message": "User 'John Doe' updated successfully",
  "user_id": 123,
  "branch_id": 67,
  "branch_name": "Chennai South",
  "action": "updated"
}
```

### Error Response (400/404/500)

```json
{
  "success": false,
  "message": "Branch 'Invalid Branch' not found. Please check the branch name."
}
```

## Behavior

### New User
- Creates a new user with the provided details
- Assigns the user to the specified branch
- Returns `action: "created"`

### Existing User (same mobile number)
- Updates user details if different
- Adds access to the new branch (if not already granted)
- Keeps existing branch access intact
- Returns `action: "updated"`

## Validation Rules

1. **Mobile Number**: Must be exactly 10 digits
2. **Password**: Minimum 6 characters
3. **Branch Name**: Must exist in the database (case-insensitive match)
4. **Name**: Cannot be empty

## Example Usage

### Create New User

```bash
curl -X POST https://your-domain.com/api/onboard \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "mobile": "9876543210",
    "password": "securepass123",
    "branch_name": "Chennai Central",
    "email": "john@example.com"
  }'
```

### Add Existing User to New Branch

```bash
curl -X POST https://your-domain.com/api/onboard \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "mobile": "9876543210",
    "password": "newpassword",
    "branch_name": "Chennai South"
  }'
```

## Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Invalid or missing required fields |
| 404 | Branch not found |
| 405 | Method not allowed (only POST accepted) |
| 500 | Internal server error |

## Security Notes

- Passwords are automatically hashed using bcrypt
- Mobile numbers must be unique across the system
- Branch names are matched case-insensitively
- User can have access to multiple branches
- Existing branch access is never removed, only new access is added

## Testing

Run the test script to verify API functionality:

```bash
./test-onboard.sh
```

This will test various scenarios including:
- New user creation
- Existing user updates
- Invalid branch names
- Missing required fields
- Invalid mobile number formats