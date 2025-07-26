# Meditation Time Tracker App

A Vue.js 3 web application for NGO volunteers to track meditation sessions at different branches across Tamil Nadu.

## Features

- **User Authentication**: Login with mobile number and password
- **Branch Management**: Volunteers can manage multiple branches
- **Session Recording**: Record meditation sessions with participant details
- **Participant Management**: Add and edit participant information (name, age, gender)
- **Time Slot Selection**: Touch/drag interface for selecting meditation duration (30, 60, or 90 minutes)
- **Dashboard**: View monthly statistics and reports
- **Autocomplete**: Smart participant name suggestions

## Technology Stack

- **Frontend**: Vue.js 3 (standalone, no build process)
- **Backend**: PHP with MySQL
- **Styling**: Tailwind CSS
- **Database**: MySQL

## Setup Instructions

### 1. Database Setup

1. Create a MySQL database named `meditation_tracker`
2. Import the database schema:
   ```bash
   mysql -u root -p meditation_tracker < database/schema.sql
   ```

### 2. Backend Configuration

1. Update database connection settings in `backend/config.php`:
   ```php
   $host = 'localhost';
   $dbname = 'meditation_tracker';
   $username = 'your_username';
   $password = 'your_password';
   ```

### 3. Web Server Setup

1. Place the project files in your web server directory (e.g., `/var/www/html/meditation-tracker/`)
2. Ensure PHP and MySQL are running
3. Make sure the `backend/` directory is accessible via web server

### 4. Access the Application

#### Option 1: Using Mock API (Development Mode)
1. Navigate to `frontend/index.html?mock=true` in your web browser
2. This uses mock data without requiring a backend setup
3. Use the default login credentials:
   - Mobile: `9283181228`
   - Password: `meditation123`

#### Option 2: Using Real Backend
1. Set up the backend as described above
2. Navigate to `frontend/index.html` in your web browser
3. Use the database credentials

## Default Sample Data

The database includes:
- 5 branches across Tamil Nadu
- 5 volunteer users with branch assignments
- Sample login credentials (password: `meditation123`)

## Project Structure

```
├── backend/
│   ├── config.php          # Database configuration
│   ├── auth.php            # Authentication API
│   ├── participants.php    # Participant management API
│   ├── sessions.php        # Session recording API
│   └── dashboard.php       # Dashboard data API
├── frontend/
│   ├── index.html          # Main application file
│   ├── index-old.html      # Original monolithic version (backup)
│   ├── config.js           # Application configuration
│   ├── js/
│   │   └── utils.js        # Shared utilities and API functions
│   └── components/
│       ├── LoginForm.js            # Login component
│       ├── BranchSelection.js      # Branch selection component
│       ├── Navigation.js           # Navigation bar component
│       ├── Dashboard.js            # Dashboard component
│       ├── DataEntry.js            # Session recording component
│       ├── TimeSlotSelector.js     # Time slot selection component
│       └── ParticipantsManagement.js # Participant management component
├── mock-api/
│   ├── index.html          # Mock API test interface
│   ├── mockApi.js          # Main mock API handler
│   ├── auth.js             # Mock authentication endpoint
│   ├── participants.js     # Mock participants endpoints
│   ├── sessions.js         # Mock sessions endpoints
│   ├── dashboard.js        # Mock dashboard endpoint
│   └── data/
│       └── mockData.js     # Mock data store and generator
├── database/
│   └── schema.sql          # Database schema with sample data
└── README.md
```

## Component Architecture

The application is now organized into reusable Vue.js components:

- **LoginForm**: Handles user authentication
- **BranchSelection**: Allows volunteers to select their branch
- **Navigation**: Top navigation bar with view switching
- **Dashboard**: Monthly statistics and reports
- **DataEntry**: Session recording form with time slot selection
- **TimeSlotSelector**: Interactive time selection component
- **ParticipantsManagement**: Participant editing interface

Each component is self-contained with its own template, props, data, and methods, making the codebase much more maintainable and scalable.

## Mock API System

The application includes a comprehensive mock API system for development without backend dependencies:

### Features
- **Complete API Simulation**: All endpoints (auth, participants, sessions, dashboard)
- **Realistic Data**: Auto-generated sample data with relationships
- **Network Simulation**: Configurable delays to simulate real network conditions
- **Data Persistence**: Mock data persists during session (resets on page reload)
- **Easy Switching**: Toggle between mock and real API with a single button

### Usage
1. **Enable Mock Mode**: Add `?mock=true` to the URL or click the toggle button
2. **Development Interface**: Visit `mock-api/index.html` for testing and data management
3. **Test Credentials**: Use any of the sample user credentials (password: `meditation123`)

### Mock API Endpoints
- `auth.php` - User authentication
- `participants.php` - Participant management (GET, POST, PUT)
- `sessions.php` - Session recording and retrieval (GET, POST)
- `dashboard.php` - Statistics and reporting (GET)

### Benefits
- ✅ **Frontend Development**: Work on UI without backend setup
- ✅ **Testing**: Consistent test data for development
- ✅ **Demos**: Show functionality without database dependencies
- ✅ **API Design**: Validate API contracts before backend implementation

## Usage

1. **Login**: Enter mobile number and password
2. **Select Branch**: Choose the branch you're managing
3. **Record Session**: 
   - Enter participant name (with autocomplete)
   - Optionally add age and gender for new participants
   - Select time period (Morning/Afternoon/Evening)
   - Touch and drag to select meditation duration
   - Submit the session
4. **View Dashboard**: Check monthly statistics and reports
5. **Manage Participants**: Edit participant details (age, gender)

## API Endpoints

- `POST /backend/auth.php` - User authentication
- `GET/POST/PUT /backend/participants.php` - Participant management
- `GET/POST /backend/sessions.php` - Session management
- `GET /backend/dashboard.php` - Dashboard data

## Color Theme

The application uses a blue color theme with `#04349C` as the primary color, creating a calm and peaceful interface suitable for the meditation context.