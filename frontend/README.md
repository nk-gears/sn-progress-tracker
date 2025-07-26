# Meditation Time Tracker - Vue.js 3 App

A modern, mobile-first Vue.js 3 application for NGO volunteers to track meditation sessions at different branches across Tamil Nadu.

## 🚀 Features

- **Modern Vue.js 3**: Built with Composition API, TypeScript, and Vite
- **Mobile-First Design**: Optimized for mobile devices with touch-friendly interactions
- **Smart Participant Management**: Autocomplete with conditional age/gender fields
- **Session Recording**: Touch-enabled time slot selection with visual feedback
- **Session Editing**: Full CRUD operations on recorded sessions
- **Dashboard Analytics**: Monthly statistics, top participants, and time distribution
- **Branch Management**: Multi-branch support with easy switching
- **State Management**: Pinia for reactive state management
- **Type Safety**: Full TypeScript support for better development experience
- **Responsive Design**: Works seamlessly across all device sizes

## 🛠️ Technology Stack

- **Frontend**: Vue.js 3 + TypeScript + Vite
- **Styling**: Tailwind CSS with custom components
- **State Management**: Pinia
- **Routing**: Vue Router 4
- **Build Tool**: Vite
- **Type Checking**: TypeScript + Vue TSC
- **Linting**: ESLint + Vue ESLint plugin

## 📱 Mobile Optimizations

- Touch-enabled time slot selection with drag support
- Haptic feedback for better user experience
- Safe area support for notched devices
- Optimized touch targets (minimum 44px)
- Mobile-first responsive breakpoints
- Smooth animations and transitions

## 🏗️ Project Structure

```
src/
├── components/          # Reusable Vue components
│   ├── BaseLayout.vue   # Main layout with navigation
│   ├── ParticipantInput.vue  # Smart participant input with autocomplete
│   └── TimeSlotSelector.vue  # Touch-enabled time selection
├── views/               # Page components
│   ├── LoginView.vue    # Authentication page
│   ├── BranchSelectionView.vue  # Branch selection
│   ├── DashboardView.vue  # Analytics dashboard
│   ├── DataEntryView.vue  # Session recording
│   └── ParticipantsView.vue  # Participant management
├── stores/              # Pinia state stores
│   ├── app.ts          # Global app state
│   ├── auth.ts         # Authentication state
│   ├── participants.ts  # Participant management
│   └── sessions.ts     # Session management
├── services/            # API and business logic
│   ├── mockApi.ts      # Mock API implementation
│   └── mockData.ts     # Mock data management
├── types/               # TypeScript type definitions
│   └── index.ts        # All type definitions
├── router/              # Vue Router configuration
│   └── index.ts        # Routes and guards
├── style.css           # Global styles and Tailwind
└── main.ts             # Application entry point
```

## 🚀 Getting Started

### Prerequisites

- Node.js 18+ 
- npm or yarn

### Installation

1. **Clone and navigate to the frontend directory:**
   ```bash
   cd frontend
   ```

2. **Install dependencies:**
   ```bash
   npm install
   ```

3. **Start development server:**
   ```bash
   npm run dev
   ```

4. **Open your browser:**
   Navigate to `http://localhost:3000`

### Development Commands

```bash
# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Type checking
npm run type-check

# Linting
npm run lint
```

## 🔐 Demo Credentials

**Mobile:** 9283181228  
**Password:** meditation123

## 📊 Key Features Breakdown

### Smart Particle Input
- Real-time autocomplete with participant details
- Conditional age/gender fields (only for new participants)
- Visual indicators for existing vs new participants
- Seamless data entry experience

### Touch-Enabled Time Selection
- Drag to select time ranges
- Visual feedback with animations
- Haptic feedback on supported devices
- Quick duration buttons (30, 60, 90, 120 minutes)
- Mobile-optimized grid layout

### Session Management
- Create, edit, and delete sessions
- Real-time validation
- Form persistence during editing
- Success/error feedback with toast notifications

### Dashboard Analytics
- Monthly summary statistics
- Top participants leaderboard
- Time distribution analysis
- Daily session breakdowns
- Interactive data visualization

### Branch Management
- Multi-branch support
- Easy branch switching
- Isolated data per branch
- Persistent branch selection

## 🎨 Design System

### Colors
- **Primary**: #04349C (Meditation blue)
- **Primary Dark**: #032975
- **Gradients**: Blue-to-indigo gradients for modern look

### Typography
- System fonts for optimal performance
- Responsive font sizes
- Proper contrast ratios

### Components
- Consistent spacing using Tailwind
- Touch-friendly sizing (44px minimum)
- Smooth transitions and animations
- Glass-morphism effects

## 🔄 State Management

The app uses Pinia for state management with separate stores for:

- **App Store**: Global loading, toasts, and UI state
- **Auth Store**: User authentication and session management
- **Participants Store**: Participant data and search functionality
- **Sessions Store**: Session recording, editing, and dashboard data

## 🛣️ Routing

Vue Router 4 with:
- Route guards for authentication
- Lazy loading for better performance
- Meta tags for page titles
- Automatic redirects based on auth state

## 🎯 Performance Optimizations

- **Code Splitting**: Lazy loaded routes and components
- **Tree Shaking**: Unused code elimination
- **Optimized Builds**: Vite's fast build process
- **Image Optimization**: Responsive images and lazy loading
- **Service Worker Ready**: PWA-ready architecture

## 🧪 Mock API

The app includes a comprehensive mock API system:
- Realistic data generation
- Network delay simulation
- Error handling simulation
- Persistent session data
- Easy switching between mock and real APIs

## 🔧 Configuration

### Environment Variables
```env
VITE_API_MODE=mock  # mock | real | auto
VITE_API_BASE_URL=http://localhost/backend
VITE_ENABLE_MOCK_DELAY=true
```

### Tailwind Configuration
Custom theme with meditation-focused colors and mobile-first breakpoints.

### TypeScript Configuration
Strict mode enabled with path aliases for clean imports.

## 📱 PWA Ready

The app is structured to easily add Progressive Web App features:
- Service worker ready
- Manifest file template included
- Offline-first architecture
- App-like experience on mobile

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request

## 📄 License

This project is proprietary software developed for the NGO meditation tracking system.

---

Built with ❤️ using Vue.js 3, TypeScript, and modern web technologies.