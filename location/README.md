# Meditation Centers Finder

A simple, standalone web application to find nearby meditation centers with location-based search and distance filtering.

## Features

### Near Me Tab
- **Current Location Detection**: Click to detect your current location using browser geolocation
- **Distance Filtering**: Filter centers by distance (10km, 25km, 50km, 100km, 500km, or all)
- **Responsive Design**: Mobile-first design that works on all devices
- **Dual View Modes**:
  - **List View**: Cards showing all center details with distance
  - **Map View**: Interactive Google Maps with custom markers
- **Interactive Map Features**:
  - Custom markers for meditation centers
  - Blue dot for your current location
  - Click markers to see center details in info windows
  - Auto-zoom to fit all centers in view
  - Direct links to get directions or call from map
- **Interactive Cards**: Each center shows:
  - Name and address
  - Distance from your location
  - Contact information (phone & email)
  - Quick actions (Get Directions, Call)

### Browse by Location Tab
- **Multi-State Support**: Choose from Tamil Nadu, South Kerala, or Puducherry
- **District Browsing**: View all districts in selected state with center counts
- **District Centers**: Click any district to see all centers in that area
- **Breadcrumb Navigation**: Easy 3-level navigation (States → Districts → Centers)
- **Organized View**: Centers organized by state and district

## How to Use

### Near Me Tab
1. Open `index.html` in your web browser
2. Click the location icon button to request location access
3. Allow location access when your browser prompts you
4. The app will automatically search for nearby centers
5. Use the distance filter to adjust search radius
6. Toggle between **List View** (default) and **Map View**:
   - **List View**: Scrollable cards with center details
   - **Map View**: Interactive Google Map with markers
7. Click markers on the map to see center details in a popup
8. Click "Directions" to navigate to a center or "Call" to contact them

### Browse by Location Tab
1. Switch to the "Browse by Location" tab
2. Select a state (Tamil Nadu, South Kerala, or Puducherry)
3. View the list of districts in that state with center counts
4. Click on any district to see centers in that area
5. Use the breadcrumb navigation to go back (States → Districts → Centers)
6. Contact centers directly or get directions

## Files

- `index.html` - Main HTML structure
- `styles.css` - Responsive CSS styling
- `app.js` - JavaScript for functionality (location detection, filtering, rendering)
- `centers.json` - **Sample API response format** (use this as reference for your backend)
- `API_REFERENCE.md` - Complete API documentation with backend implementation guide
- `README.md` - This file

## Technical Details

- **Pure JavaScript**: No frameworks required
- **Google Maps JavaScript API**: Interactive maps with custom markers and info windows
- **Haversine Formula**: Accurate distance calculations between coordinates
- **Mock Data**: Currently uses sample meditation centers across 3 states
- **CORS Handling**: Includes proxy setup for API calls (when needed)
- **Responsive Design**: CSS Grid and Flexbox for mobile-first layouts

## Customization

### Adding Real API Integration

To connect to a real API, modify the `fetchCenters()` function in `app.js`:

```javascript
async function fetchCenters(lat, lng) {
    const response = await fetch(`YOUR_API_URL?lat=${lat}&lng=${lng}`);
    const data = await response.json();
    return data;
}
```

### Adding More Centers

1. Add center data to the `getMockCenters()` function in `app.js`:

```javascript
{
    name: "Center Name",
    address: "Full Address with State/District",
    lat: 13.0827,
    lng: 80.2707,
    phone: "+91 XXX XXX XXXX",
    email: "email@example.com"
}
```

2. Update the district count in the `statesData` object:

```javascript
"Tamil Nadu": {
    districts: [
        { name: "Chennai", centerCount: 5 }, // Update count
        // ... more districts
    ]
}
```

## Browser Compatibility

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Mobile browsers: Full support with touch optimization

## Notes

- Location permission is required for automatic detection
- The app works offline with cached centers data
- Distance calculations are approximate using the Haversine formula
- Currently uses mock data for demonstration purposes

## Future Enhancements

- Real-time API integration with Brahma Kumaris centers
- Search by city/address (geocoding)
- Save favorite centers
- Center details page with photos and timings
- Multi-language support
