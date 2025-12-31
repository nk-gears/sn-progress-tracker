# Shivanum Naanum - Web App

A Vue.js 3 + TypeScript web application for the Shivanum Naanum Shivratri meditation experience organized by Brahma Kumaris.

## 📋 Project Overview

**Purpose**: Public-facing website for end-users to find meditation centres, learn about the Shivratri event, and join WhatsApp groups for updates.

**Event Details**:
- **Event Name**: Shivanum Naanum (Shiva and I)
- **Dates**: 14-16 February 2026
- **Timings**: 7:00 AM – 12:00 Noon & 4:00 PM – 8:00 PM
- **Entry**: Free and open to all

## 🎨 Design Reference

The application is based on the design provided in `/Users/Nirmal/Downloads/sn-website.pdf`

## 🏗️ Tech Stack

- **Framework**: Vue.js 3 with Composition API
- **Language**: TypeScript
- **Build Tool**: Vite
- **Styling**: Tailwind CSS
- **State Management**: Pinia
- **Routing**: Vue Router 4

## 📁 Project Structure

```
web-app/
├── public/
│   └── images/           # Event images, logos, backgrounds
├── src/
│   ├── components/       # Reusable Vue components
│   │   ├── Navbar.vue
│   │   ├── Footer.vue
│   │   ├── CentreCard.vue
│   │   ├── FAQAccordion.vue
│   │   └── WhatsAppJoinForm.vue
│   ├── views/
│   │   └── HomeView.vue  # Main single-page view
│   ├── router/
│   │   └── index.ts      # Vue Router configuration
│   ├── stores/
│   │   └── centres.ts    # Pinia store for centres data
│   ├── types/
│   │   └── index.ts      # TypeScript type definitions
│   ├── App.vue           # Root component
│   ├── main.ts           # Application entry point
│   └── style.css         # Global Tailwind CSS
├── index.html
├── package.json
├── vite.config.ts
├── tsconfig.json
└── tailwind.config.js
```

## 🎯 Key Features (From PDF Design)

### 1. Hero Section
- **Title**: "Shivanum Naanum - Few Moments with God Shiva"
- **Subtitle**: Event description and tagline
- **Event Dates & Timings**: Prominently displayed
- **CTA Buttons**:
  - Join WhatsApp Group (primary)
  - Find Nearest Centre (secondary)

### 2. Find Your Nearest Centre
- **State Dropdown**: Select from Tamil Nadu, South Kerala, Puducherry
- **District Dropdown**: Cascading based on selected state
- **Centre Dropdown**: List of centres in selected district
- **Location Button**: "Use My Location to Find Nearest Centres"
- **Centre Cards**: Display with:
  - Centre name & address
  - Phone number
  - Distance from user (if location enabled)
  - Event dates & timings
  - Map icon/link

### 3. What is Shivanum Naanum?
- **Description**: Detailed explanation of the meditation experience
- **Key Points**:
  - Silent meditation-based environment
  - Experience Room concept
  - No rituals or procedures
  - Suitable for first-timers
  - Free entry, no registration needed

### 4. About Brahma Kumaris
- Organization information
- Rajayoga meditation focus
- Volunteer-driven activities
- Link to know more

### 5. Join WhatsApp Group
- **Form Fields**:
  - Name (text input)
  - Mobile Number (tel input with validation)
  - Centre Selection (dropdown)
- **Submit Button**: "Join"
- **Purpose**: Get centre updates, meditation timings, latest videos

### 6. Contact Us
- **Phone Numbers**:
  - Call: 8XXXXXX
  - WhatsApp: 9XXXXXXXX
- Message to visit nearest centre

### 7. FAQ Section
- **Accordion Style**: Expandable questions
- **Questions**:
  - Is this a religious program?
  - Do I need to know meditation before coming?
  - What exactly will I experience there?
  - Is there any entry fee?
  - Do I need to register in advance?

### 8. Navigation
- **Fixed Top Navigation**: Home, Find Centre, About, Organiser, Join Us, Contact Us, FAQ
- **Mobile Menu**: Hamburger menu for mobile devices
- **Smooth Scroll**: Anchor links to sections

## 🎨 Design Theme

### Colors (Tailwind Extended)
```javascript
colors: {
  primary: {
    // Purple gradient shades
    500: '#8b5cf6',
    600: '#7c3aed',
    700: '#6d28d9',
  },
  spiritual: {
    purple: '#6d28d9',
    blue: '#3b82f6',
    orange: '#f97316',
  }
}
```

### Typography
- **Headings**: Bold, large sizes
- **Body**: Readable, good line height
- **Emphasis**: Purple/blue gradient accents

### Visual Elements
- Gradient backgrounds
- Rounded corners (cards, buttons)
- Shadows for depth
- Smooth animations
- Spiritual/calming aesthetic

## 🚀 Getting Started

### Prerequisites
- Node.js 16+
- pnpm (or npm)

### Installation

```bash
# Install dependencies
pnpm install

# Run development server
pnpm dev

# Build for production
pnpm build

# Preview production build
pnpm preview
```

The app will run on `http://localhost:5174`

## 📝 Development Guide

### Creating Components

#### Example: CentreCard Component
```vue
<template>
  <div class="centre-card">
    <h3>{{ centre.name }}</h3>
    <p>{{ centre.address }}</p>
    <p v-if="centre.distance">📍 {{ centre.distance }} km away</p>
    <a :href="`tel:${centre.phone}`">📞 {{ centre.phone }}</a>
  </div>
</template>

<script setup lang="ts">
import type { Centre } from '@/types'

interface Props {
  centre: Centre
}

defineProps<Props>()
</script>
```

### Using Stores (Pinia)

```typescript
// stores/centres.ts
import { defineStore } from 'pinia'
import type { State, District, Centre } from '@/types'

export const useCentresStore = defineStore('centres', {
  state: () => ({
    states: [] as State[],
    selectedState: null as State | null,
    selectedDistrict: null as District | null,
    userLocation: null as { lat: number, lng: number } | null
  }),

  actions: {
    async fetchStates() {
      // Fetch from API or use static data
    },

    calculateDistance(centre: Centre) {
      // Haversine formula to calculate distance
    }
  }
})
```

### Integrating with Existing API

The app should integrate with the same centres API used in the `location` folder:

```typescript
// Fetch centres data
const response = await fetch('/api/centres')
const data = await response.json()

// Structure matches centers.json format
{
  "success": true,
  "data": {
    "states": [...]
  }
}
```

## 🔗 Integration Points

### With Location App
- **Shared API**: Use the same `/api/centers` endpoint
- **Data Format**: Same JSON structure for states/districts/centres
- **Location Service**: Same geolocation and distance calculation logic

### With Frontend (Admin/Volunteer App)
- **Separate Purpose**: This is public-facing, frontend is for admins
- **Different Routes**: This doesn't need authentication
- **Shared Data Source**: Both use same backend database

## 📱 Mobile Responsiveness

- **Mobile-First**: Design prioritizes mobile experience
- **Breakpoints**:
  - sm: 640px
  - md: 768px
  - lg: 1024px
  - xl: 1280px
- **Touch-Friendly**: Large buttons, easy navigation
- **Fast Loading**: Optimized images and code splitting

## 🌐 Deployment

### Build for Production
```bash
pnpm build
```

### Deploy to Vercel
```bash
vercel --prod
```

### Environment Variables
```env
VITE_API_BASE_URL=https://your-api-domain.com
VITE_GOOGLE_MAPS_KEY=your-maps-api-key
```

## 📋 TODO: Components to Build

### Priority 1 (Core Functionality)
- [ ] `HomeView.vue` - Main page with all sections
- [ ] `Navbar.vue` - Navigation with mobile menu
- [ ] `HeroSection.vue` - Hero with event info and CTAs
- [ ] `FindCentreSection.vue` - Centre finder with dropdowns
- [ ] `CentreCard.vue` - Individual centre display card

### Priority 2 (Forms & Interaction)
- [ ] `WhatsAppJoinForm.vue` - Join form with validation
- [ ] `FAQAccordion.vue` - Expandable FAQ list
- [ ] `LocationButton.vue` - Geolocation detection

### Priority 3 (Content)
- [ ] `AboutSection.vue` - What is Shivanum Naanum
- [ ] `OrganiserSection.vue` - About Brahma Kumaris
- [ ] `ContactSection.vue` - Contact information
- [ ] `Footer.vue` - Footer with links

### Priority 4 (Enhancements)
- [ ] Add Google Maps integration for centres
- [ ] Add loading states and skeletons
- [ ] Add error handling and user feedback
- [ ] Add social media share buttons
- [ ] Add image gallery/carousel

## 🎯 Next Steps

1. **Install Dependencies**: Run `pnpm install`
2. **Start Dev Server**: Run `pnpm dev`
3. **Build Components**: Create components in priority order
4. **Integrate API**: Connect to centres API endpoint
5. **Test Responsiveness**: Test on various devices
6. **Deploy**: Deploy to Vercel or hosting platform

## 📞 Support

For questions about the project structure or implementation, refer to:
- Vue.js docs: https://vuejs.org
- Tailwind CSS docs: https://tailwindcss.com
- TypeScript docs: https://www.typescriptlang.org

---

**Design Reference**: `/Users/Nirmal/Downloads/sn-website.pdf`
**Event**: Shivanum Naanum - Shivratri 2026
**Organizer**: Brahma Kumaris
