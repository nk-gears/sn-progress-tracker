# Meditation Tracker API Documentation

This directory contains comprehensive API documentation for the Meditation Time Tracker backend system.

## 📋 Files

- **`swagger.yaml`** - Complete OpenAPI 3.0 specification
- **`index.html`** - Interactive Swagger UI documentation viewer
- **`README.md`** - This file with setup instructions

## 🚀 Quick Start

### Option 1: View Online (Swagger Editor)
1. Copy the contents of `swagger.yaml`
2. Go to [editor.swagger.io](https://editor.swagger.io/)
3. Paste the YAML content
4. Test the APIs directly from the editor

### Option 2: View Locally 
1. Open `index.html` in your web browser
2. The Swagger UI will load automatically
3. Test APIs against your local backend

### Option 3: Command Line (if you have swagger-ui installed)
```bash
# Install swagger-ui globally
npm install -g swagger-ui-cli

# Serve the documentation
swagger-ui-serve swagger.yaml
```

## 🧪 Testing the APIs

### Prerequisites
1. **Backend Setup**: Ensure your PHP backend is running
2. **Database**: MySQL database is set up with the schema
3. **CORS**: Backend should handle CORS for API testing

### Authentication Flow
1. **Login First**: Use `/auth.php` endpoint with demo credentials:
   ```json
   {
     "mobile": "9283181228",
     "password": "meditation123"
   }
   ```
2. **Get Token**: Copy the `token` from the login response
3. **Add to Headers**: Use the token in subsequent requests:
   ```
   Authorization: Bearer auth_token_1_1640995200
   ```

### Demo Data
The swagger file includes realistic examples for all endpoints:
- **Users**: Demo volunteer accounts with different branch access
- **Participants**: Sample meditation participants with various demographics  
- **Sessions**: Example meditation sessions with different durations and times

## 📚 API Overview

### Core Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/auth.php` | POST | User authentication |
| `/participants.php` | GET/POST/PUT | Manage participants |
| `/sessions.php` | GET/POST/PUT/DELETE | Manage meditation sessions |
| `/dashboard.php` | GET | Analytics and reports |

### Key Features Documented

#### 🔐 Authentication
- Token-based authentication system
- User and branch relationship management
- Demo credentials for testing

#### 👥 Participant Management
- Branch-scoped participant lists
- Search and autocomplete functionality
- Create/update participant profiles
- Optional age and gender fields

#### ⏰ Session Management
- **Duration Constraints**: Exactly 30, 60, 90, or 120 minutes
- **Multiple Time Ranges**: Support for multiple sessions per participant/date
- **Date/Time Validation**: Proper format validation
- **CRUD Operations**: Complete session lifecycle management

#### 📊 Dashboard Analytics
- Monthly summary statistics
- Top participants by session count
- Time distribution analysis (Morning/Afternoon/Evening)
- Daily session statistics

## 🔧 Backend Configuration

### Base URLs
Update the server URLs in `swagger.yaml` to match your setup:

```yaml
servers:
  - url: http://localhost/meditation-tracker/backend
    description: Local development server
  - url: https://your-domain.com/api  
    description: Production server
```

### CORS Setup
Ensure your PHP backend includes proper CORS headers:

```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

## 🎯 Multiple Time Range Support

The API documentation includes detailed examples for handling multiple time ranges:

### Frontend Implementation
1. User selects multiple time ranges (e.g., 7:00-7:30 AM + 10:00-11:00 AM)
2. Frontend makes separate API calls for each range
3. Each call creates individual session record
4. Same `participant_id` and `session_date` for all ranges

### API Example
```json
// First range: 7:00-7:30 AM (30 minutes)
POST /sessions.php
{
  "participant_id": 101,
  "branch_id": 1,
  "volunteer_id": 1,
  "session_date": "2024-08-10",
  "start_time": "07:00",
  "duration_minutes": 30
}

// Second range: 10:00-11:00 AM (60 minutes)  
POST /sessions.php
{
  "participant_id": 101,
  "branch_id": 1,
  "volunteer_id": 1,
  "session_date": "2024-08-10", 
  "start_time": "10:00",
  "duration_minutes": 60
}
```

## ✅ Testing Checklist

- [ ] Authentication with demo credentials works
- [ ] Can create new participants
- [ ] Can search existing participants
- [ ] Can create meditation sessions with different durations
- [ ] Can create multiple sessions for same participant/date
- [ ] Can update existing sessions
- [ ] Can delete sessions
- [ ] Dashboard returns analytics data
- [ ] All validation rules work (duration constraints, date formats, etc.)

## 🐛 Common Issues

### CORS Errors
- Ensure backend has proper CORS headers
- Test from same domain as backend initially

### Authentication Failures
- Check database has demo user data
- Verify password hashing matches PHP implementation

### Duration Validation
- Only 30, 60, 90, 120 minutes are allowed
- Frontend should validate before API call

## 📞 Support

For questions about the API documentation or backend setup, check:
1. The main project README
2. Backend PHP files for implementation details
3. Database schema for data structure

---

**Last Updated**: August 2024  
**API Version**: 1.0.0  
**OpenAPI Version**: 3.0.3