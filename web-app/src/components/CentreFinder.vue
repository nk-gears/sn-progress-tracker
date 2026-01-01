<template>
  <section id="find-centre" class="section bg-gray-50">
    <div class="container mx-auto px-4">
      <h2 class="section-title">{{ $t('findCentre.title') }}</h2>
      <p class="section-subtitle">{{ $t('findCentre.subtitle') }}</p>

      <div class="max-w-6xl mx-auto">
        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-200 mb-6">
          <button
            @click="activeTab = 'nearMe'"
            :class="['flex items-center gap-2 px-6 py-3 font-medium border-b-2 transition-colors',
              activeTab === 'nearMe'
                ? 'border-purple-600 text-purple-600'
                : 'border-transparent text-gray-600 hover:text-purple-600']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Near Me
          </button>
          <button
            @click="activeTab = 'browse'"
            :class="['flex items-center gap-2 px-6 py-3 font-medium border-b-2 transition-colors',
              activeTab === 'browse'
                ? 'border-purple-600 text-purple-600'
                : 'border-transparent text-gray-600 hover:text-purple-600']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Browse by Location
          </button>
        </div>

        <!-- Near Me Tab -->
        <div v-show="activeTab === 'nearMe'" class="content-card">
          <div class="space-y-4">
            <!-- Current Location Button -->
            <button
              @click="getCurrentLocation"
              :disabled="isLoadingLocation"
              class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="!isLoadingLocation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <svg v-else class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ isLoadingLocation ? 'Getting Location...' : $t('findCentre.useLocation') }}</span>
            </button>

            <!-- Distance Filter - Temporarily Hidden -->
            <!-- <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Distance</label>
              <select v-model="maxDistance" class="form-select">
                <option :value="10">Within 10 km</option>
                <option :value="25">Within 25 km</option>
                <option :value="50">Within 50 km</option>
                <option :value="100">Within 100 km</option>
                <option :value="500">Within 500 km</option>
                <option :value="1000">All centers</option>
              </select>
            </div> -->

            <!-- Error Message -->
            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
              {{ errorMessage }}
            </div>

            <!-- Results Summary and View Toggle -->
            <div v-if="filteredCenters.length > 0" class="flex items-center justify-between py-4 border-t border-gray-200">
              <p class="font-medium text-gray-700">
                <strong>{{ filteredCenters.length }}</strong> centers found
              </p>
              <div class="flex gap-2">
                <button
                  @click="viewMode = 'list'"
                  :class="['p-2 rounded', viewMode === 'list' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300']"
                  title="List View"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                  </svg>
                </button>
                <button
                  @click="viewMode = 'map'"
                  :class="['p-2 rounded', viewMode === 'map' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300']"
                  title="Map View"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Map View -->
            <div v-show="viewMode === 'map' && filteredCenters.length > 0" class="h-96 bg-gray-100 rounded-lg border border-gray-300 overflow-hidden">
              <div ref="mapContainer" class="w-full h-full"></div>
            </div>

            <!-- List View -->
            <div v-show="viewMode === 'list'" class="space-y-4">
              <CentreCard
                v-for="centre in filteredCenters"
                :key="centre.id"
                :centre="centre"
              />
            </div>
          </div>
        </div>

        <!-- Browse Tab -->
        <div v-show="activeTab === 'browse'" class="content-card">
          <!-- Search Box -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Centers</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by center name, location, district or state..."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
            />
          </div>

          <div v-if="searchQuery.trim()" class="mb-6">
            <!-- Search Results -->
            <h3 class="text-lg font-semibold text-gray-700 mb-4">
              Search Results ({{ searchResults.length }})
            </h3>
            <div v-if="searchResults.length > 0" class="space-y-4">
              <CentreCard
                v-for="centre in searchResults"
                :key="centre.id"
                :centre="centre"
              />
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              No centers found matching "{{ searchQuery }}"
            </div>
          </div>

          <div v-else>
          <!-- Browse by State/District (hidden when searching) -->
          <!-- Breadcrumb -->
          <nav class="flex mb-6 text-sm" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1">
              <li>
                <button @click="resetBrowse" class="text-purple-600 hover:text-purple-800 font-medium">
                  States
                </button>
              </li>
              <li v-if="selectedState">
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <button @click="showDistricts" class="text-purple-600 hover:text-purple-800 font-medium">
                    {{ selectedState.name }}
                  </button>
                </div>
              </li>
              <li v-if="selectedDistrict">
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-gray-700 font-medium">{{ selectedDistrict.name }}</span>
                </div>
              </li>
            </ol>
          </nav>

          <!-- States List -->
          <div v-if="!selectedState" class="grid md:grid-cols-3 gap-4">
            <button
              v-for="state in mockStates"
              :key="state.id"
              @click="selectState(state)"
              class="p-4 text-left border border-gray-300 rounded-lg hover:border-purple-600 hover:bg-purple-50 transition-all"
            >
              <h3 class="font-bold text-lg text-purple-700">{{ state.name }}</h3>
              <p class="text-sm text-gray-600">{{ state.districtCount }} districts</p>
            </button>
          </div>

          <!-- Districts List -->
          <div v-else-if="selectedState && !selectedDistrict" class="grid md:grid-cols-3 gap-4">
            <button
              v-for="district in selectedState.districts"
              :key="district.id"
              @click="selectDistrict(district)"
              class="p-4 text-left border border-gray-300 rounded-lg hover:border-purple-600 hover:bg-purple-50 transition-all"
            >
              <h3 class="font-bold text-lg text-purple-700">{{ district.name }}</h3>
              <p class="text-sm text-gray-600">{{ district.centers.length }} centers</p>
            </button>
          </div>

          <!-- Centers List -->
          <div v-else class="space-y-4">
            <CentreCard
              v-for="centre in selectedDistrict.centers"
              :key="centre.id"
              :centre="centre"
            />
          </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import CentreCard from './CentreCard.vue'
import type { Centre } from '@/types'

const activeTab = ref<'nearMe' | 'browse'>('nearMe')
const viewMode = ref<'list' | 'map'>('list')
const isLoadingLocation = ref(false)
const errorMessage = ref('')
const maxDistance = ref(25)
const userLocation = ref<{ lat: number; lng: number } | null>(null)
const selectedState = ref<any>(null)
const selectedDistrict = ref<any>(null)
const searchQuery = ref('')

// Google Maps
const mapContainer = ref<HTMLElement | null>(null)
let map: google.maps.Map | null = null
let markers: google.maps.Marker[] = []

// Mock data for states and districts
const mockStates = ref([
  {
    id: 1,
    name: 'Tamil Nadu',
    districtCount: 3,
    districts: [
      {
        id: 1,
        name: 'Chennai',
        centers: [
          { id: 1, name: 'West Mambalam, Chennai', address: '203, Murugan illam, Chennai 73', district: 'Chennai', state: 'Tamil Nadu', latitude: 13.0418, longitude: 80.2341, phone: '+91-XXXXXXXXXX' },
          { id: 2, name: 'Ashok Nagar, Chennai', address: '13, Vadivel street, Chennai - 83', district: 'Chennai', state: 'Tamil Nadu', latitude: 13.0358, longitude: 80.2102, phone: '+91-XXXXXXXXXX' },
          { id: 3, name: 'Anna Nagar, Chennai', address: '13, Vadivel street, Chennai - 83', district: 'Chennai', state: 'Tamil Nadu', latitude: 13.0850, longitude: 80.2101, phone: '+91-XXXXXXXXXX' }
        ]
      },
      {
        id: 2,
        name: 'Coimbatore',
        centers: [
          { id: 4, name: 'RS Puram, Coimbatore', address: 'Sample Address, Coimbatore', district: 'Coimbatore', state: 'Tamil Nadu', latitude: 11.0168, longitude: 76.9558, phone: '+91-XXXXXXXXXX' }
        ]
      },
      {
        id: 3,
        name: 'Madurai',
        centers: [
          { id: 5, name: 'Anna Nagar, Madurai', address: 'Sample Address, Madurai', district: 'Madurai', state: 'Tamil Nadu', latitude: 9.9252, longitude: 78.1198, phone: '+91-XXXXXXXXXX' }
        ]
      }
    ]
  },
  {
    id: 2,
    name: 'South Kerala',
    districtCount: 2,
    districts: [
      {
        id: 4,
        name: 'Thiruvananthapuram',
        centers: [
          { id: 6, name: 'Kesavadasapuram, TVM', address: 'Sample Address, TVM', district: 'Thiruvananthapuram', state: 'Kerala', latitude: 8.5241, longitude: 76.9366, phone: '+91-XXXXXXXXXX' }
        ]
      },
      {
        id: 5,
        name: 'Kollam',
        centers: [
          { id: 7, name: 'Main Centre, Kollam', address: 'Sample Address, Kollam', district: 'Kollam', state: 'Kerala', latitude: 8.8932, longitude: 76.6141, phone: '+91-XXXXXXXXXX' }
        ]
      }
    ]
  },
  {
    id: 3,
    name: 'Puducherry',
    districtCount: 1,
    districts: [
      {
        id: 6,
        name: 'Puducherry',
        centers: [
          { id: 8, name: 'Main Centre, Puducherry', address: 'Sample Address, Puducherry', district: 'Puducherry', state: 'Puducherry', latitude: 11.9416, longitude: 79.8083, phone: '+91-XXXXXXXXXX' }
        ]
      }
    ]
  }
])

// All centers from all states
const allCenters = computed<Centre[]>(() => {
  const centers: Centre[] = []
  mockStates.value.forEach(state => {
    state.districts.forEach((district: any) => {
      centers.push(...district.centers)
    })
  })
  return centers
})

// Calculate distance between two points using Haversine formula
function calculateDistance(lat1: number, lon1: number, lat2: number, lon2: number): number {
  const R = 6371 // Radius of Earth in km
  const dLat = (lat2 - lat1) * Math.PI / 180
  const dLon = (lon2 - lon1) * Math.PI / 180
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2)
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  return R * c
}

// Filtered centers based on location and distance
const filteredCenters = computed(() => {
  if (!userLocation.value) return []

  return allCenters.value
    .map(centre => ({
      ...centre,
      distance: calculateDistance(
        userLocation.value!.lat,
        userLocation.value!.lng,
        centre.latitude,
        centre.longitude
      )
    }))
    .filter(centre => centre.distance! <= maxDistance.value)
    .sort((a, b) => a.distance! - b.distance!)
})

// Search results - filters all centers by search query
const searchResults = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return []

  return allCenters.value.filter(centre => {
    const searchableText = [
      centre.name,
      centre.address,
      centre.district,
      centre.state
    ].join(' ').toLowerCase()

    return searchableText.includes(query)
  })
})

// Get current location
async function getCurrentLocation() {
  if (!navigator.geolocation) {
    errorMessage.value = 'Geolocation is not supported by your browser'
    return
  }

  isLoadingLocation.value = true
  errorMessage.value = ''

  try {
    const position = await new Promise<GeolocationPosition>((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      })
    })

    userLocation.value = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    }
  } catch (error) {
    errorMessage.value = 'Unable to get your location. Please check permissions.'
  } finally {
    isLoadingLocation.value = false
  }
}

// Initialize Google Maps
function initializeMap() {
  if (!mapContainer.value || !userLocation.value) return

  const center = userLocation.value

  map = new google.maps.Map(mapContainer.value, {
    center,
    zoom: 11,
    styles: [
      {
        featureType: 'poi',
        elementType: 'labels',
        stylers: [{ visibility: 'off' }]
      }
    ]
  })

  // Add user location marker
  new google.maps.Marker({
    position: center,
    map,
    title: 'Your Location',
    icon: {
      path: google.maps.SymbolPath.CIRCLE,
      scale: 8,
      fillColor: '#4285F4',
      fillOpacity: 1,
      strokeColor: '#ffffff',
      strokeWeight: 2
    }
  })

  // Add center markers
  markers.forEach(marker => marker.setMap(null))
  markers = []

  filteredCenters.value.forEach(centre => {
    const marker = new google.maps.Marker({
      position: { lat: centre.latitude, lng: centre.longitude },
      map,
      title: centre.name,
      icon: {
        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
          <svg width="32" height="40" viewBox="0 0 32 40" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 0C7.163 0 0 7.163 0 16c0 12 16 24 16 24s16-12 16-24c0-8.837-7.163-16-16-16zm0 22c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z" fill="#7c3aed"/>
          </svg>
        `)
      }
    })

    const infoWindow = new google.maps.InfoWindow({
      content: `
        <div style="padding: 8px;">
          <h3 style="margin: 0 0 4px 0; font-weight: bold; color: #7c3aed;">${centre.name}</h3>
          <p style="margin: 0; font-size: 14px; color: #666;">${centre.address}</p>
          <p style="margin: 4px 0 0 0; font-size: 12px; color: #999;">📍 ${centre.distance?.toFixed(1)} km away</p>
        </div>
      `
    })

    marker.addListener('click', () => {
      infoWindow.open(map, marker)
    })

    markers.push(marker)
  })
}

// Browse functions
function selectState(state: any) {
  selectedState.value = state
  selectedDistrict.value = null
}

function selectDistrict(district: any) {
  selectedDistrict.value = district
}

function showDistricts() {
  selectedDistrict.value = null
}

function resetBrowse() {
  selectedState.value = null
  selectedDistrict.value = null
}

// Watch for changes to reinitialize map
watch([viewMode, filteredCenters], async () => {
  if (viewMode.value === 'map' && filteredCenters.value.length > 0) {
    await nextTick()
    initializeMap()
  }
})

onMounted(() => {
  // Load Google Maps script if not already loaded
  if (!window.google) {
    const script = document.createElement('script')
    script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyB80mIsZ2S4llsDA3rzssAqjvDGvZgFLv8'
    script.async = true
    script.defer = true
    document.head.appendChild(script)
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>
