// Application State
const state = {
    userLocation: null,
    centers: [],
    filteredCenters: [],
    maxDistance: 25, // Default 25km
    currentView: 'states', // 'states', 'districts', or 'centers'
    selectedState: null,
    selectedDistrict: null
};

// DOM Elements
const elements = {
    locationInput: document.getElementById('locationInput'),
    getCurrentLocation: document.getElementById('getCurrentLocation'),
    distanceFilter: document.getElementById('distanceFilter'),
    searchBtn: document.getElementById('searchBtn'),
    loading: document.getElementById('loading'),
    error: document.getElementById('error'),
    resultsSummary: document.getElementById('resultsSummary'),
    resultsCount: document.getElementById('resultsCount'),
    centersList: document.getElementById('centersList'),
    breadcrumb: document.getElementById('breadcrumb'),
    browseContent: document.getElementById('browseContent'),
    browseCentersList: document.getElementById('browseCentersList'),
    mapContainer: document.getElementById('mapContainer'),
    mapElement: document.getElementById('map'),
    listViewBtn: document.getElementById('listViewBtn'),
    mapViewBtn: document.getElementById('mapViewBtn')
};

// Map instance
let map = null;
let markers = [];
let infoWindow = null;

// Utility Functions
function showLoading() {
    elements.loading.classList.remove('hidden');
    elements.error.classList.add('hidden');
    elements.resultsSummary.classList.add('hidden');
    elements.centersList.innerHTML = '';
}

function hideLoading() {
    elements.loading.classList.add('hidden');
}

function showError(message) {
    elements.error.textContent = message;
    elements.error.classList.remove('hidden');
    hideLoading();
}

function hideError() {
    elements.error.classList.add('hidden');
}

// Calculate distance between two points using Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of the Earth in kilometers
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;
    return distance;
}

// Get user's current location
async function getCurrentLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation is not supported by your browser'));
            return;
        }

        elements.getCurrentLocation.disabled = true;

        navigator.geolocation.getCurrentPosition(
            (position) => {
                elements.getCurrentLocation.disabled = false;
                resolve({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    address: 'Your Current Location'
                });
            },
            (error) => {
                elements.getCurrentLocation.disabled = false;
                reject(new Error('Unable to get your location. Please check permissions.'));
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
}

// Fetch meditation centers from Brahma Kumaris API
async function fetchCenters(lat, lng) {
    try {
        const url = `https://www.brahmakumaris.com/centers?lat=${lat}&lng=${lng}`;

        // Using a CORS proxy since we're calling from a different domain
        const proxyUrl = 'https://api.allorigins.win/raw?url=';
        const response = await fetch(proxyUrl + encodeURIComponent(url));

        if (!response.ok) {
            throw new Error('Failed to fetch centers');
        }

        const html = await response.text();

        // Parse centers from the HTML response
        // This is a simplified parser - you may need to adjust based on actual API response
        const centers = parseCentersFromHTML(html);

        return centers;
    } catch (error) {
        console.error('Error fetching centers:', error);
        throw error;
    }
}

// Parse centers from HTML (or use mock data if parsing fails)
function parseCentersFromHTML(html) {
    // Since we can't easily parse the HTML without a proper API,
    // let's use mock data for demonstration
    // In a real scenario, you would parse the actual response or use a proper API endpoint

    return getMockCenters();
}

// Mock centers data for demonstration
function getMockCenters() {
    return [
        {
            name: "Brahma Kumaris Raja Yoga Center - Chennai Central",
            address: "123 Anna Salai, Mount Road, Chennai, Tamil Nadu 600002",
            lat: 13.0827,
            lng: 80.2707,
            phone: "+91 44 1234 5678",
            email: "chennai.central@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - T. Nagar",
            address: "456 Usman Road, T. Nagar, Chennai, Tamil Nadu 600017",
            lat: 13.0418,
            lng: 80.2341,
            phone: "+91 44 2345 6789",
            email: "tnagar@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Adyar",
            address: "789 Lattice Bridge Road, Adyar, Chennai, Tamil Nadu 600020",
            lat: 13.0067,
            lng: 80.2571,
            phone: "+91 44 3456 7890",
            email: "adyar@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Velachery",
            address: "321 Velachery Main Road, Chennai, Tamil Nadu 600042",
            lat: 12.9750,
            lng: 80.2220,
            phone: "+91 44 4567 8901",
            email: "velachery@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Porur",
            address: "654 Mount Poonamallee Road, Porur, Chennai, Tamil Nadu 600116",
            lat: 13.0350,
            lng: 80.1570,
            phone: "+91 44 5678 9012",
            email: "porur@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Tambaram",
            address: "987 GST Road, Tambaram, Chennai, Tamil Nadu 600045",
            lat: 12.9249,
            lng: 80.1000,
            phone: "+91 44 6789 0123",
            email: "tambaram@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Coimbatore",
            address: "111 Avinashi Road, Coimbatore, Tamil Nadu 641018",
            lat: 11.0168,
            lng: 76.9558,
            phone: "+91 422 1234 567",
            email: "coimbatore@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Madurai",
            address: "222 West Veli Street, Madurai, Tamil Nadu 625001",
            lat: 9.9252,
            lng: 78.1198,
            phone: "+91 452 2345 678",
            email: "madurai@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Trichy",
            address: "333 Salai Road, Tiruchirappalli, Tamil Nadu 620001",
            lat: 10.7905,
            lng: 78.7047,
            phone: "+91 431 3456 789",
            email: "trichy@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Salem",
            address: "444 Cherry Road, Salem, Tamil Nadu 636001",
            lat: 11.6643,
            lng: 78.1460,
            phone: "+91 427 4567 890",
            email: "salem@brahmakumaris.com"
        },
        // South Kerala Centers
        {
            name: "Brahma Kumaris - Thiruvananthapuram Central",
            address: "555 MG Road, Thiruvananthapuram, Kerala 695001",
            lat: 8.5241,
            lng: 76.9366,
            phone: "+91 471 2345 678",
            email: "tvm.central@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Thiruvananthapuram East",
            address: "666 Kowdiar Road, Thiruvananthapuram, Kerala 695003",
            lat: 8.5324,
            lng: 76.9496,
            phone: "+91 471 3456 789",
            email: "tvm.east@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Kollam",
            address: "777 Main Road, Kollam, Kerala 691001",
            lat: 8.8932,
            lng: 76.6141,
            phone: "+91 474 2345 678",
            email: "kollam@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Alappuzha",
            address: "888 Beach Road, Alappuzha, Kerala 688001",
            lat: 9.4981,
            lng: 76.3388,
            phone: "+91 477 2345 678",
            email: "alappuzha@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Kottayam",
            address: "999 MC Road, Kottayam, Kerala 686001",
            lat: 9.5916,
            lng: 76.5222,
            phone: "+91 481 2345 678",
            email: "kottayam@brahmakumaris.com"
        },
        // Puducherry Centers
        {
            name: "Brahma Kumaris - Puducherry White Town",
            address: "101 Beach Road, White Town, Puducherry 605001",
            lat: 11.9340,
            lng: 79.8306,
            phone: "+91 413 2345 678",
            email: "pondy.whitetown@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Puducherry Lawspet",
            address: "202 ECR Main Road, Lawspet, Puducherry 605008",
            lat: 11.9416,
            lng: 79.8083,
            phone: "+91 413 3456 789",
            email: "pondy.lawspet@brahmakumaris.com"
        },
        {
            name: "Brahma Kumaris - Karaikal",
            address: "303 Main Street, Karaikal, Puducherry 609602",
            lat: 10.9254,
            lng: 79.8380,
            phone: "+91 4368 234 567",
            email: "karaikal@brahmakumaris.com"
        }
    ];
}

// States and Districts Data
const statesData = {
    "Tamil Nadu": {
        districts: [
            { name: "Chennai", centerCount: 4 },
            { name: "Coimbatore", centerCount: 1 },
            { name: "Madurai", centerCount: 1 },
            { name: "Tiruchirappalli", centerCount: 1 },
            { name: "Salem", centerCount: 1 },
            { name: "Tirunelveli", centerCount: 0 },
            { name: "Vellore", centerCount: 0 },
            { name: "Erode", centerCount: 0 },
            { name: "Thanjavur", centerCount: 0 },
            { name: "Kanchipuram", centerCount: 0 }
        ]
    },
    "South Kerala": {
        districts: [
            { name: "Thiruvananthapuram", centerCount: 2 },
            { name: "Kollam", centerCount: 1 },
            { name: "Pathanamthitta", centerCount: 0 },
            { name: "Alappuzha", centerCount: 1 },
            { name: "Kottayam", centerCount: 1 },
            { name: "Idukki", centerCount: 0 }
        ]
    },
    "Puducherry": {
        districts: [
            { name: "Puducherry", centerCount: 2 },
            { name: "Karaikal", centerCount: 1 },
            { name: "Mahe", centerCount: 0 },
            { name: "Yanam", centerCount: 0 }
        ]
    }
};

// Get total centers for a state
function getStateCenterCount(stateName) {
    const stateData = statesData[stateName];
    if (!stateData) return 0;
    return stateData.districts.reduce((sum, district) => sum + district.centerCount, 0);
}

// Get centers by district
function getCentersByDistrict(districtName) {
    const allCenters = getMockCenters();
    return allCenters.filter(center => center.address.includes(districtName));
}

// Render states list
function renderStates() {
    const stateNames = Object.keys(statesData);
    elements.browseContent.innerHTML = `
        <div class="location-grid">
            ${stateNames.map(stateName => {
                const centerCount = getStateCenterCount(stateName);
                return `
                    <div class="location-item" onclick="selectState('${stateName}')">
                        <span class="location-name">${stateName}</span>
                        <span class="location-count">${centerCount} center${centerCount !== 1 ? 's' : ''}</span>
                    </div>
                `;
            }).join('')}
        </div>
    `;
    elements.browseCentersList.innerHTML = '';
}

// Render districts for selected state
function renderDistricts(stateName) {
    const stateData = statesData[stateName];
    if (!stateData) return;

    elements.browseContent.innerHTML = `
        <div class="location-grid">
            ${stateData.districts.map(district => `
                <div class="location-item" onclick="selectDistrict('${district.name}')">
                    <span class="location-name">${district.name}</span>
                    <span class="location-count">${district.centerCount} center${district.centerCount !== 1 ? 's' : ''}</span>
                </div>
            `).join('')}
        </div>
    `;
    elements.browseCentersList.innerHTML = '';
}

// Render centers for selected district
function renderBrowseCenters(centers, districtName) {
    elements.browseContent.innerHTML = '';
    elements.browseCentersList.innerHTML = '';

    if (centers.length === 0) {
        elements.browseCentersList.innerHTML = `
            <div style="background: #f8f9fa; padding: 40px; border-radius: 12px; text-align: center;">
                <p style="color: #666; font-size: 16px;">No meditation centers found in ${districtName}.</p>
                <p style="color: #999; font-size: 14px; margin-top: 8px;">Check back later or explore other districts.</p>
            </div>
        `;
        return;
    }

    centers.forEach(center => {
        const card = document.createElement('div');
        card.className = 'center-card browse-center-card';

        card.innerHTML = `
            <div class="center-header">
                <h3 class="center-name">${center.name}</h3>
            </div>
            <p class="center-address">${center.address}</p>
            <div class="center-contact">
                ${center.phone ? `
                    <div class="contact-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>${center.phone}</span>
                    </div>
                ` : ''}
                ${center.email ? `
                    <div class="contact-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>${center.email}</span>
                    </div>
                ` : ''}
            </div>
            <div class="center-actions">
                <button class="btn-action btn-directions" onclick="openDirections(${center.lat}, ${center.lng})">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Directions
                </button>
                ${center.phone ? `
                    <button class="btn-action btn-call" onclick="callCenter('${center.phone}')">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Call
                    </button>
                ` : ''}
            </div>
        `;

        elements.browseCentersList.appendChild(card);
    });
}

// Select state
function selectState(stateName) {
    state.selectedState = stateName;
    state.selectedDistrict = null;
    state.currentView = 'districts';

    // Update breadcrumb
    elements.breadcrumb.innerHTML = `
        <button class="breadcrumb-item" onclick="backToStates()">States</button>
        <button class="breadcrumb-item active">${stateName}</button>
    `;

    // Render districts for this state
    renderDistricts(stateName);
}

// Select district
function selectDistrict(districtName) {
    state.selectedDistrict = districtName;
    state.currentView = 'centers';

    // Update breadcrumb
    elements.breadcrumb.innerHTML = `
        <button class="breadcrumb-item" onclick="backToStates()">States</button>
        <button class="breadcrumb-item" onclick="backToDistricts()">${state.selectedState}</button>
        <button class="breadcrumb-item active">${districtName}</button>
    `;

    // Get and render centers for this district
    const centers = getCentersByDistrict(districtName);
    renderBrowseCenters(centers, districtName);
}

// Back to states
function backToStates() {
    state.selectedState = null;
    state.selectedDistrict = null;
    state.currentView = 'states';

    // Update breadcrumb
    elements.breadcrumb.innerHTML = `
        <button class="breadcrumb-item active">States</button>
    `;

    // Render states
    renderStates();
}

// Back to districts
function backToDistricts() {
    state.selectedDistrict = null;
    state.currentView = 'districts';

    // Update breadcrumb
    elements.breadcrumb.innerHTML = `
        <button class="breadcrumb-item" onclick="backToStates()">States</button>
        <button class="breadcrumb-item active">${state.selectedState}</button>
    `;

    // Render districts
    renderDistricts(state.selectedState);
}

// Filter centers by distance
function filterCentersByDistance(centers, userLat, userLng, maxDistance) {
    return centers
        .map(center => {
            const distance = calculateDistance(userLat, userLng, center.lat, center.lng);
            return {
                ...center,
                distance: distance
            };
        })
        .filter(center => center.distance <= maxDistance)
        .sort((a, b) => a.distance - b.distance);
}

// Render center card
function renderCenterCard(center) {
    const card = document.createElement('div');
    card.className = 'center-card';

    const distanceText = center.distance < 1
        ? `${Math.round(center.distance * 1000)} m`
        : `${center.distance.toFixed(1)} km`;

    card.innerHTML = `
        <div class="center-header">
            <h3 class="center-name">${center.name}</h3>
            <span class="center-distance">${distanceText}</span>
        </div>
        <p class="center-address">${center.address}</p>
        <div class="center-contact">
            ${center.phone ? `
                <div class="contact-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>${center.phone}</span>
                </div>
            ` : ''}
            ${center.email ? `
                <div class="contact-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>${center.email}</span>
                </div>
            ` : ''}
        </div>
        <div class="center-actions">
            <button class="btn-action btn-directions" onclick="openDirections(${center.lat}, ${center.lng})">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Directions
            </button>
            ${center.phone ? `
                <button class="btn-action btn-call" onclick="callCenter('${center.phone}')">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Call
                </button>
            ` : ''}
        </div>
    `;

    return card;
}

// Render all centers
function renderCenters(centers) {
    elements.centersList.innerHTML = '';

    if (centers.length === 0) {
        elements.centersList.innerHTML = `
            <div style="background: white; padding: 40px; border-radius: 12px; text-align: center;">
                <p style="color: #666; font-size: 16px;">No meditation centers found within the selected distance.</p>
                <p style="color: #999; font-size: 14px; margin-top: 8px;">Try increasing the distance filter.</p>
            </div>
        `;
        return;
    }

    centers.forEach(center => {
        const card = renderCenterCard(center);
        elements.centersList.appendChild(card);
    });

    elements.resultsCount.textContent = centers.length;
    elements.resultsSummary.classList.remove('hidden');
}

// Initialize Google Map
function initializeMap(centers, userLocation) {
    if (!map) {
        // Create map centered on user location or first center
        const center = userLocation || (centers.length > 0 ? { lat: centers[0].lat, lng: centers[0].lng } : { lat: 13.0827, lng: 80.2707 });

        map = new google.maps.Map(elements.mapElement, {
            center: center,
            zoom: userLocation ? 12 : 10,
            mapTypeControl: true,
            fullscreenControl: true,
            streetViewControl: false,
            styles: [
                {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }
            ]
        });

        infoWindow = new google.maps.InfoWindow();
    }

    // Clear existing markers
    clearMarkers();

    // Add user location marker if available
    if (userLocation) {
        const userMarker = new google.maps.Marker({
            position: { lat: userLocation.lat, lng: userLocation.lng },
            map: map,
            title: 'Your Location',
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: '#4285F4',
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: 3
            },
            zIndex: 1000
        });
        markers.push(userMarker);
    }

    // Add markers for each center
    const bounds = new google.maps.LatLngBounds();

    centers.forEach((center, index) => {
        const position = { lat: center.lat, lng: center.lng };

        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: center.name,
            animation: google.maps.Animation.DROP,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="32" height="42" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 0C7.164 0 0 7.164 0 16c0 12 16 26 16 26s16-14 16-26c0-8.836-7.164-16-16-16z" fill="#04349C"/>
                        <circle cx="16" cy="16" r="6" fill="white"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(32, 42),
                anchor: new google.maps.Point(16, 42)
            }
        });

        // Create info window content
        const distanceText = center.distance
            ? (center.distance < 1
                ? `${Math.round(center.distance * 1000)} m away`
                : `${center.distance.toFixed(1)} km away`)
            : '';

        const infoContent = `
            <div style="max-width: 300px; padding: 12px; font-family: system-ui, -apple-system, sans-serif;">
                <h3 style="margin: 0 0 8px 0; color: #04349C; font-size: 16px; font-weight: 700;">${center.name}</h3>
                ${distanceText ? `<p style="margin: 0 0 8px 0; color: #667eea; font-size: 13px; font-weight: 600;">${distanceText}</p>` : ''}
                <p style="margin: 0 0 8px 0; color: #666; font-size: 14px; line-height: 1.4;">${center.address}</p>
                ${center.phone ? `<p style="margin: 4px 0; color: #555; font-size: 13px;">📞 ${center.phone}</p>` : ''}
                ${center.email ? `<p style="margin: 4px 0; color: #555; font-size: 13px;">✉️ ${center.email}</p>` : ''}
                <div style="margin-top: 12px; display: flex; gap: 8px;">
                    <a href="https://www.google.com/maps/dir/?api=1&destination=${center.lat},${center.lng}"
                       target="_blank"
                       style="flex: 1; padding: 8px 12px; background: #04349C; color: white; text-decoration: none; border-radius: 6px; text-align: center; font-size: 13px; font-weight: 600;">
                        Directions
                    </a>
                    ${center.phone ? `
                        <a href="tel:${center.phone}"
                           style="flex: 1; padding: 8px 12px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; text-align: center; font-size: 13px; font-weight: 600;">
                            Call
                        </a>
                    ` : ''}
                </div>
            </div>
        `;

        marker.addListener('click', () => {
            infoWindow.setContent(infoContent);
            infoWindow.open(map, marker);
        });

        markers.push(marker);
        bounds.extend(position);
    });

    // Fit map to show all markers
    if (centers.length > 0) {
        if (userLocation) {
            bounds.extend({ lat: userLocation.lat, lng: userLocation.lng });
        }
        map.fitBounds(bounds);

        // Don't zoom in too much for single location
        google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
            if (this.getZoom() > 15) {
                this.setZoom(15);
            }
        });
    }
}

// Clear all markers from map
function clearMarkers() {
    markers.forEach(marker => marker.setMap(null));
    markers = [];
}

// Toggle between list and map view
function showListView() {
    elements.listViewBtn.classList.add('active');
    elements.mapViewBtn.classList.remove('active');
    elements.centersList.classList.remove('hidden');
    elements.mapContainer.classList.add('hidden');
}

function showMapView() {
    elements.listViewBtn.classList.remove('active');
    elements.mapViewBtn.classList.add('active');
    elements.centersList.classList.add('hidden');
    elements.mapContainer.classList.remove('hidden');

    // Initialize or update map
    if (state.filteredCenters.length > 0) {
        initializeMap(state.filteredCenters, state.userLocation);
    }
}

// Search for centers
async function searchCenters() {
    try {
        hideError();
        showLoading();

        if (!state.userLocation) {
            throw new Error('Please set your location first');
        }

        // Fetch centers (using mock data for now)
        state.centers = getMockCenters();

        // Filter by distance
        state.filteredCenters = filterCentersByDistance(
            state.centers,
            state.userLocation.lat,
            state.userLocation.lng,
            state.maxDistance
        );

        hideLoading();
        renderCenters(state.filteredCenters);

    } catch (error) {
        showError(error.message);
    }
}

// Open directions in Google Maps
function openDirections(lat, lng) {
    const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    window.open(url, '_blank');
}

// Call center
function callCenter(phone) {
    window.location.href = `tel:${phone}`;
}

// Event Listeners
elements.getCurrentLocation.addEventListener('click', async () => {
    try {
        hideError();
        const location = await getCurrentLocation();
        state.userLocation = location;
        elements.locationInput.value = location.address;

        // Automatically search when location is detected
        searchCenters();
    } catch (error) {
        showError(error.message);
    }
});

elements.distanceFilter.addEventListener('change', (e) => {
    state.maxDistance = parseInt(e.target.value);

    // Re-filter and render if we have centers
    if (state.centers.length > 0 && state.userLocation) {
        state.filteredCenters = filterCentersByDistance(
            state.centers,
            state.userLocation.lat,
            state.userLocation.lng,
            state.maxDistance
        );
        renderCenters(state.filteredCenters);
    }
});

elements.searchBtn.addEventListener('click', searchCenters);

// View toggle event listeners
elements.listViewBtn.addEventListener('click', showListView);
elements.mapViewBtn.addEventListener('click', showMapView);

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const targetTab = btn.dataset.tab;

        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });

        if (targetTab === 'nearMe') {
            document.getElementById('nearMeTab').classList.add('active');
        } else if (targetTab === 'browse') {
            document.getElementById('browseTab').classList.add('active');
            // Initialize browse tab with states list
            if (state.currentView === 'states') {
                renderStates();
            }
        }
    });
});

// Initialize on load
window.addEventListener('load', async () => {
    // Initialize browse tab with states
    renderStates();

    // Don't automatically request location - wait for user action
    console.log('App loaded. Click "Use my current location" to enable location services.');
});
