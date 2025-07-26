# Backend API Documentation

Updated PHP backend for the Meditation Time Tracker Vue.js application with full CRUD operations, enhanced participant management, and session editing capabilities.

## 🚀 **Updated Features**

### **Enhanced Session Management**
- ✅ **Create** sessions with participant validation
- ✅ **Read** sessions by branch and date
- ✅ **Update** sessions with field-level validation
- ✅ **Delete** sessions with proper error handling

### **Smart Participant Management**
- ✅ **Find-or-create** logic for participants
- ✅ **Autocomplete** search with participant details
- ✅ **Conditional updates** for age/gender fields
- ✅ **Branch-scoped** participant management

### **Improved Dashboard Analytics**
- ✅ **Monthly statistics** with detailed breakdowns
- ✅ **Top participants** ranking
- ✅ **Time distribution** analysis
- ✅ **Daily statistics** with unique participant counts

## 📁 **API Endpoints**

### **Authentication**
```php
POST /backend/auth.php
```
**Request:**
```json
{
  "mobile": "928318122",
  "password": "meditation123"
}
```
**Response:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Ramesh Kumar",
    "mobile": "928318122"
  },
  "branches": [
    {
      "id": 1,
      "name": "Chennai Central Branch",
      "location": "Chennai, Tamil Nadu"
    }
  ],
  "token": "auth_token_1_1642598400"
}
```

### **Participants Management**

#### **Get Participants by Branch**
```php
GET /backend/participants.php?branch_id=1&search=anand
```
**Response:**
```json
{
  "success": true,
  "participants": [
    {
      "id": 1,
      "name": "Anand Krishna",
      "age": 35,
      "gender": "Male",
      "branch_id": 1,
      "created_at": "2024-01-01 10:00:00"
    }
  ]
}
```

#### **Search Participants (Autocomplete)**
```php
GET /backend/participants.php?branch_id=1&search=krishna&action=search
```

#### **Create/Find Participant**
```php
POST /backend/participants.php
```
**Request:**
```json
{
  "name": "New Participant",
  "branch_id": 1,
  "age": 30,
  "gender": "Female"
}
```
**Response:**
```json
{
  "success": true,
  "participant": {
    "id": 11,
    "name": "New Participant",
    "age": 30,
    "gender": "Female",
    "branch_id": 1
  },
  "message": "Participant created successfully"
}
```

#### **Update Participant**
```php
PUT /backend/participants.php
```
**Request:**
```json
{
  "id": 1,
  "age": 36,
  "gender": "Male"
}
```

### **Session Management**

#### **Get Sessions by Date**
```php
GET /backend/sessions.php?branch_id=1&date=2024-01-15
```
**Response:**
```json
{
  "success": true,
  "sessions": [
    {
      "id": 1,
      "participant_id": 1,
      "participant_name": "Anand Krishna",
      "branch_id": 1,
      "volunteer_id": 1,
      "session_date": "2024-01-15",
      "start_time": "07:00",
      "duration_minutes": 30,
      "created_at": "2024-01-15 07:05:00",
      "updated_at": "2024-01-15 07:05:00"
    }
  ]
}
```

#### **Create Session**
```php
POST /backend/sessions.php
```
**Request:**
```json
{
  "participant_id": 1,
  "branch_id": 1,
  "volunteer_id": 1,
  "session_date": "2024-01-15",
  "start_time": "07:00",
  "duration_minutes": 30
}
```

#### **Update Session**
```php
PUT /backend/sessions.php
```
**Request:**
```json
{
  "id": 1,
  "start_time": "07:30",
  "duration_minutes": 60
}
```

#### **Delete Session**
```php
DELETE /backend/sessions.php?id=1
```

### **Dashboard Analytics**
```php
GET /backend/dashboard.php?branch_id=1&month=2024-01
```
**Response:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_participants": 25,
      "total_hours": 68.5,
      "total_sessions": 47,
      "month": "2024-01"
    },
    "daily_stats": [
      {
        "session_date": "2024-01-15",
        "sessions_count": 5,
        "unique_participants": 4,
        "total_minutes": 240
      }
    ],
    "top_participants": [
      {
        "name": "Anand Krishna",
        "session_count": 15,
        "total_minutes": 900
      }
    ],
    "time_distribution": [
      {
        "time_period": "Morning",
        "session_count": 20,
        "total_minutes": 1200
      }
    ]
  }
}
```

## 🗄️ **Database Schema Updates**

### **Updated Sessions Table**
```sql
CREATE TABLE meditation_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    participant_id INT NOT NULL,
    branch_id INT NOT NULL,
    volunteer_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    duration_minutes INT NOT NULL CHECK (duration_minutes IN (30, 60, 90, 120)),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Foreign keys and indexes...
);
```

## 🔧 **Configuration**

### **Environment Setup**
1. **Database Configuration** (`config.php`):
   ```php
   $host = 'localhost';
   $dbname = 'meditation_tracker';
   $username = 'your_username';
   $password = 'your_password';
   ```

2. **CORS Headers** (already configured):
   ```php
   header('Access-Control-Allow-Origin: *');
   header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
   header('Access-Control-Allow-Headers: Content-Type, Authorization');
   ```

## 🧪 **API Testing**

### **Using cURL**
```bash
# Test authentication
curl -X POST http://localhost/backend/auth.php \
  -H "Content-Type: application/json" \
  -d '{"mobile":"928318122","password":"meditation123"}'

# Test participant search
curl "http://localhost/backend/participants.php?branch_id=1&search=anand&action=search"

# Test session creation
curl -X POST http://localhost/backend/sessions.php \
  -H "Content-Type: application/json" \
  -d '{
    "participant_id": 1,
    "branch_id": 1,
    "volunteer_id": 1,
    "session_date": "2024-01-15",
    "start_time": "07:00",
    "duration_minutes": 30
  }'
```

## 🔄 **Vue.js Integration**

The backend now perfectly integrates with the Vue.js frontend through:

### **API Service Layer**
- ✅ **Auto-switching** between mock and real APIs
- ✅ **Error handling** with graceful fallbacks
- ✅ **Type safety** with TypeScript interfaces
- ✅ **Network timeout** and retry logic

### **Environment Variables**
```env
# Development
VITE_API_MODE=auto
VITE_API_BASE_URL=../backend

# Production
VITE_API_MODE=real
VITE_API_BASE_URL=./backend
```

### **API Modes**
- **Mock Mode**: Use simulated data (development)
- **Real Mode**: Connect to PHP backend (production)
- **Auto Mode**: Try real API, fallback to mock (recommended)

## 🚦 **Status Codes**

- **200**: Success
- **400**: Bad Request (validation errors)
- **401**: Unauthorized (invalid credentials)
- **404**: Not Found (resource doesn't exist)
- **405**: Method Not Allowed
- **500**: Internal Server Error

## 🔐 **Security Features**

- ✅ **Password hashing** with PHP's `password_verify()`
- ✅ **SQL injection protection** with prepared statements
- ✅ **Input validation** and sanitization
- ✅ **CORS configuration** for frontend integration
- ✅ **Error message standardization**

## 📊 **Sample Data**

The database includes:
- **5 branches** across Tamil Nadu
- **5 volunteer users** with branch assignments
- **10 sample participants** with varying details
- **Generated sessions** for current month testing (30, 60, 90, or 120 minute durations)

**Default Login:**
- Mobile: `928318122`
- Password: `meditation123`

## 🔧 **Installation & Setup**

1. **Import Database:**
   ```bash
   mysql -u root -p meditation_tracker < ../database/schema.sql
   ```

2. **Configure Backend:**
   ```bash
   # Update database credentials in config.php
   ```

3. **Test API:**
   ```bash
   # Ensure web server is running and test endpoints
   ```

The backend is now fully compatible with the Vue.js frontend and supports all the enhanced features including session editing, smart participant management, and robust error handling.