# Project Context: Meditation Time Tracker for NGO

## 📋 Project Overview

**Purpose**: Build a meditation time tracker web application for an NGO with branches across Tamil Nadu  
**Target Users**: Volunteer coordinators who track meditation sessions for participants  
**Primary Device**: Mobile-first design (users primarily use mobile devices)  
**Tech Stack**: Vue.js 3 + TypeScript, PHP backend, MySQL database

## 🏗️ Architecture & Technology Stack

### Frontend (Vue.js 3 Application)
- **Framework**: Vue.js 3 with Composition API
- **Language**: TypeScript for type safety
- **Build Tool**: Vite for fast development and optimized builds
- **Styling**: Tailwind CSS with custom design system
- **State Management**: Pinia stores
- **Routing**: Vue Router 4 with authentication guards
- **Location**: `/frontend/` directory

### Backend (PHP APIs)
- **Language**: PHP with MySQL
- **Database**: MySQL with proper foreign keys and constraints
- **APIs**: RESTful endpoints for auth, participants, sessions, dashboard
- **Location**: `/backend/` directory
- **Key Features**: CRUD operations, participant autocomplete, session analytics

### Database Schema
- **Location**: `/database/schema.sql`
- **Tables**: users, branches, participants, meditation_sessions, user_branches
- **Key Constraints**: Session duration (30, 60, 90, 120 minutes), foreign keys

### Mock API System
- **Purpose**: Development and standalone deployment
- **Location**: `/frontend/src/services/mockApi.ts` and `/mock-api/`
- **Features**: Realistic data simulation, network delay simulation, persistent sessions

## 🎯 Core Features Implemented

### 1. Authentication System
- **Login**: Mobile number + password authentication
- **Demo Credentials**: 9283181228 / meditation123
- **Multi-branch**: Users can access multiple branches
- **Session Management**: Token-based authentication

### 2. Participant Management
- **Smart Autocomplete**: Real-time search with participant details
- **Conditional Fields**: Age/gender fields only show for new participants
- **Find-or-Create Logic**: Automatically handles existing vs new participants
- **Branch Scoped**: Participants are isolated per branch

### 3. Session Recording
- **Touch-Enabled Time Selection**: Drag to select time ranges on mobile
- **Duration Options**: 30, 60, 90, 120 minutes
- **Time Periods**: All, Morning (6-12), Afternoon (12-17), Evening (17-22)
- **Visual Feedback**: Haptic feedback, animations, progress indicators
- **Quick Duration Buttons**: One-tap selection for common durations

### 4. Session Management (CRUD)
- **Create**: Record new meditation sessions
- **Read**: View sessions by date and branch
- **Update**: Edit existing session details
- **Delete**: Remove sessions with confirmation

### 5. Dashboard Analytics
- **Monthly Summary**: Total participants, hours, sessions
- **Top Participants**: Leaderboard with session counts
- **Time Distribution**: Morning/Afternoon/Evening breakdown
- **Daily Statistics**: Sessions per day with unique participant counts

### 6. Branch Management
- **Multi-Branch Support**: Switch between different locations
- **Branch Isolation**: Data is separated per branch
- **Easy Switching**: Quick branch selection interface

## 📱 Mobile-First Design Decisions

### UI/UX Optimizations
- **Touch Targets**: Minimum 44px for all interactive elements
- **Responsive Grid**: Optimized for mobile screens first
- **Compact Navigation**: Small bottom tab bar to save screen space
- **Modern Header**: Gradient background for contemporary look
- **Visual Feedback**: Loading states, success/error messages, haptic feedback

### Component Architecture
- **BaseLayout.vue**: Main layout with navigation
- **ParticipantInput.vue**: Smart autocomplete component
- **TimeSlotSelector.vue**: Touch-enabled time selection
- **ApiSwitcher.vue**: Development tool for API mode switching

### Responsive Breakpoints
- **Mobile First**: Primary design target
- **Tablet**: Optimized layouts for larger screens
- **Desktop**: Enhanced spacing and multi-column layouts

## 🔧 Development Workflow & Patterns

### Code Organization
```
src/
├── components/          # Reusable Vue components
├── views/              # Page-level components
├── stores/             # Pinia state management
├── services/           # API communication layer
├── types/              # TypeScript type definitions
└── router/             # Vue Router configuration
```

### API Service Layer Pattern
- **Auto-switching**: Automatically tries real API, falls back to mock
- **Environment Modes**: mock, real, auto modes via environment variables
- **Error Handling**: Graceful degradation with user feedback
- **Type Safety**: Full TypeScript interfaces for all API calls

### State Management Pattern
- **app.ts**: Global loading states, toasts, UI state
- **auth.ts**: User authentication and session management
- **participants.ts**: Participant data and search functionality
- **sessions.ts**: Session recording, editing, and dashboard data

## 🚀 Deployment Configuration

### Vercel Deployment
- **Configuration**: `vercel.json` with SPA routing support
- **Environment**: Mock API mode for standalone deployment
- **Build Process**: Optimized production builds with code splitting
- **Security Headers**: CSP, XSS protection, content type validation

### Environment Variables
```bash
# Development
VITE_API_MODE=auto
VITE_API_BASE_URL=../backend

# Production (Vercel)
VITE_API_MODE=mock
VITE_API_BASE_URL=
VITE_ENABLE_MOCK_DELAY=false
```

## 📊 Data Models & Business Logic

### Session Duration Rules
- **Allowed Durations**: 30, 60, 90, 120 minutes only
- **Time Slots**: 30-minute intervals (07:00, 07:30, 08:00, etc.)
- **Validation**: Both frontend and backend validation

### Time Period Classification
- **Morning**: 06:00-11:59
- **Afternoon**: 12:00-16:59  
- **Evening**: 17:00-21:59
- **All**: No time filtering (default selection)

### Participant Data Structure
- **Required**: name, branch_id
- **Optional**: age, gender (can be added later)
- **Unique Constraint**: name + branch_id combination

### Hour Calculation Business Logic
- **Total Hours Calculation**: Based on unique time slots, not sum of participant hours
- **Example**: If 5 people meditate 7:30-8:30 AM, it counts as 1 hour total for the branch
- **Logic**: `SELECT DISTINCT session_date, start_time, duration_minutes` then sum the durations
- **Impact**: Dashboard analytics, daily stats, and time distribution all follow this logic

## 🔄 Key User Workflows

### 1. Daily Session Recording Workflow
1. Volunteer logs in with mobile/password
2. Selects branch (if multiple access)
3. Navigates to "Record Session"
4. Types participant name (autocomplete shows suggestions)
5. If new participant: age/gender fields appear
6. Selects time period filter (All/Morning/Afternoon/Evening)
7. Drag-selects time range or uses quick buttons
8. Confirms and saves session

### 2. Session Editing Workflow
1. Navigate to Dashboard
2. Find session in recent list
3. Click edit button
4. Modify time, duration, or participant details
5. Save changes with validation

### 3. Analytics Review Workflow
1. Access Dashboard
2. View monthly summary statistics
3. Check top participants leaderboard
4. Review time distribution patterns
5. Analyze daily session trends

## 🐛 Known Issues & Solutions

### Resolved Issues
- **Component Separation**: Initially tried separate component files, caused blank pages. Solution: Single-file components with proper imports.
- **Touch Selection**: Mobile users couldn't select multiple time slots. Solution: Proper touch event handling with preventDefault.
- **JavaScript Syntax Errors**: Duplicate method blocks caused parse errors. Solution: Clean component structure.
- **Autocomplete Logic**: Age/gender fields showing incorrectly. Solution: Conditional rendering based on participant existence.

### Current Limitations
- **TypeScript Version**: vue-tsc has compatibility issues, removed from build process
- **Offline Support**: Not implemented (could be added as PWA)
- **Real-time Updates**: No websocket support for multi-user scenarios

## 🔐 Security Considerations

### Authentication
- **Password Hashing**: PHP password_verify() for secure authentication
- **SQL Injection**: Prepared statements throughout backend
- **Input Validation**: Both client and server-side validation
- **CORS Configuration**: Proper headers for frontend integration

### Data Privacy
- **Branch Isolation**: Participants data separated by branch
- **No Sensitive Data**: No medical or personally identifiable information stored
- **Demo Data**: All mock data uses fictional names and details

## 📈 Performance Optimizations

### Frontend Optimizations
- **Code Splitting**: Lazy-loaded routes and components
- **Bundle Size**: ~122KB main bundle with tree shaking
- **Asset Optimization**: Responsive images and lazy loading
- **Service Worker Ready**: PWA-ready architecture

### Database Optimizations
- **Indexes**: Proper indexing on foreign keys and search fields
- **Constraints**: Database-level validation for data integrity
- **Query Optimization**: Efficient joins for dashboard analytics

## 🎨 Design System

### Color Palette
- **Primary**: #04349C (Meditation blue)
- **Primary Dark**: #032975
- **Gradients**: Blue-to-indigo for modern appearance
- **Status Colors**: Green (success), Red (error), Yellow (warning)

### Typography & Spacing
- **Font**: System fonts for optimal performance
- **Scale**: Tailwind spacing scale (4px base unit)
- **Responsive**: Mobile-first font sizing
- **Contrast**: WCAG AA compliant contrast ratios

## 📝 Testing & Quality Assurance

### Manual Testing Approach
- **Mobile Testing**: Primary focus on mobile device functionality
- **Cross-browser**: Chrome, Safari, Firefox compatibility
- **Touch Interaction**: Gesture and touch event validation
- **Data Integrity**: CRUD operations verification

### Code Quality
- **ESLint**: Code linting with Vue.js rules
- **TypeScript**: Type checking for better code quality
- **Git Hooks**: Pre-commit validation setup ready

## 🔮 Future Enhancement Opportunities

### Potential Features
- **PWA Support**: Offline capability and app installation
- **Push Notifications**: Session reminders and analytics
- **Export Features**: CSV/PDF export for reports
- **Multi-language**: Tamil language support
- **Advanced Analytics**: Trend analysis and predictive insights

### Technical Improvements
- **WebSocket Support**: Real-time collaborative features
- **GraphQL API**: More efficient data fetching
- **Server-Side Rendering**: SEO and performance improvements
- **E2E Testing**: Automated testing suite

## 🤝 Development Notes for Future Changes

### Code Modification Guidelines
1. **Always read existing files** before making changes to understand current patterns
2. **Follow established conventions**: Use existing component structure and naming
3. **Test mobile-first**: Priority on mobile device functionality
4. **Maintain type safety**: Keep TypeScript interfaces updated
5. **Update both mock and real APIs**: Ensure feature parity

### Common Change Patterns
- **Adding new fields**: Update types, API endpoints, mock data, and UI components
- **UI modifications**: Consider mobile impact first, then desktop
- **API changes**: Update both PHP backend and TypeScript service layer
- **State changes**: Update Pinia stores and ensure reactivity

### Quick Command Reference
```bash
# Navigate to frontend
cd frontend

# Development
npm run dev

# Build for production
npm run build

# Type checking
npm run type-check

# Linting
npm run lint

# Deploy to Vercel
vercel --prod
```

---

**Last Updated**: 2025-07-26  
**Project Status**: Ready for deployment  
**Next Priority**: Deployment to Vercel complete, ready for production use