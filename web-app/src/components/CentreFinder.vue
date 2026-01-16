<template>
  <section id="find-centre" class="section bg-gray-50 pt-0 -mt-2">
    <div class="container mx-auto px-0">
      <h2 class="section-title">{{ $t('findCentre.title') }}</h2>
      <p class="section-subtitle">{{ $t('findCentre.subtitle') }}</p>

      <!-- Join Event Modal -->
      <JoinEventModal
        :is-open="showJoinModal"
        :centre="selectedCentreForModal"
        @close="showJoinModal = false"
        @success="handleJoinSuccess"
      />

      <div class="max-w-6xl mx-auto px-0">
        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
          <button
            @click="activeTab = 'nearMe'"
            :class="['flex items-center gap-2 px-6 py-3 font-medium border-b-2 transition-colors whitespace-nowrap',
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
            @click="activeTab = 'search'"
            :class="['flex items-center gap-2 px-6 py-3 font-medium border-b-2 transition-colors whitespace-nowrap',
              activeTab === 'search'
                ? 'border-purple-600 text-purple-600'
                : 'border-transparent text-gray-600 hover:text-purple-600']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Search Center
          </button>
          <button
            @click="activeTab = 'browse'"
            :class="['flex items-center gap-2 px-6 py-3 font-medium border-b-2 transition-colors whitespace-nowrap',
              activeTab === 'browse'
                ? 'border-purple-600 text-purple-600'
                : 'border-transparent text-gray-600 hover:text-purple-600']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            By State/District
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
                @join-now="handleJoinNow"
              />
            </div>
          </div>
        </div>

        <!-- Search Center Tab -->
        <div v-show="activeTab === 'search'" class="content-card">
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

          <!-- Search Results -->
          <div v-if="searchQuery.trim()">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">
              Search Results ({{ searchResults.length }})
            </h3>
            <div v-if="searchResults.length > 0" class="space-y-4">
              <CentreCard
                v-for="centre in searchResults"
                :key="centre.id"
                :centre="centre"
                @join-now="handleJoinNow"
              />
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              No centers found matching "{{ searchQuery }}"
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-lg">Start typing to search for centers</p>
            <p class="text-sm mt-2">Search by center name, location, district, or state</p>
          </div>
        </div>

        <!-- Browse Tab -->
        <div v-show="activeTab === 'browse'" class="content-card">
          <!-- Browse by State/District -->
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
              @join-now="handleJoinNow"
            />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import CentreCard from './CentreCard.vue'
import JoinEventModal from './JoinEventModal.vue'
import { getApiUrl } from '@/config'
import type { Centre } from '@/types'

// Emit event to parent
const emit = defineEmits<{
  joinNow: [centre: Centre]
}>()

// Modal state
const showJoinModal = ref(false)
const selectedCentreForModal = ref<Centre | undefined>(undefined)

const activeTab = ref<'nearMe' | 'search' | 'browse'>('nearMe')
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

// States and districts loaded from API
const mockStates = ref<any[]>([])
const isLoadingCenters = ref(false)

// Parse latitude_longitude string to get lat/lng
function parseLatLng(latLngString: string): { lat: number; lng: number } | null {
  if (!latLngString) return null
  const parts = latLngString.split(',').map((p: string) => parseFloat(p.trim()))
  if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
    return { lat: parts[0], lng: parts[1] }
  }
  return null
}

// Transform API center data to structured states/districts
function transformCentersToStatesStructure(centers: any[]): any[] {
  const statesMap = new Map<string, any>()

  centers.forEach((center: any) => {
    const state = center.state || 'Unknown'
    const district = center.district || 'Unknown'

    if (!statesMap.has(state)) {
      statesMap.set(state, {
        id: statesMap.size + 1,
        name: state,
        districts: new Map<string, any>()
      })
    }

    const stateObj = statesMap.get(state)
    if (!stateObj.districts.has(district)) {
      stateObj.districts.set(district, {
        id: stateObj.districts.size + 1,
        name: district,
        centers: []
      })
    }

    const latLng = parseLatLng(center.latitude_longitude)
    const centerObj = {
      id: center.id,
      center_code: center.center_code,
      name: center.locality || center.center_code,
      address: center.address,
      district: district,
      state: state,
      phone: center.contact_no,
      latitude: latLng?.lat || 0,
      longitude: latLng?.lng || 0
    }

    stateObj.districts.get(district).centers.push(centerObj)
  })

  // Convert Map to Array and add district count to state
  return Array.from(statesMap.values()).map((state: any) => ({
    ...state,
    districtCount: state.districts.size,
    districts: Array.from(state.districts.values())
  }))
}

// Load center addresses from API
async function loadCenterAddresses() {
  isLoadingCenters.value = true
  try {
    const url = getApiUrl('centerAddresses')
    console.log('Loading center addresses from:', url)

    const response = await fetch(url)
    const result = await response.json()

    console.log('Center addresses API response:', result)

    if (result.success && result.data) {
      // Log first few centers to verify center_code is present
      if (result.data.length > 0) {
        console.log('First center object:', result.data[0])
        if (!result.data[0].center_code) {
          console.warn('⚠️ Warning: First center missing center_code field!')
        }
      }

      const structuredStates = transformCentersToStatesStructure(result.data)
      mockStates.value = structuredStates
      console.log('Centers loaded and structured:', structuredStates)
      console.log('Loaded', result.data.length, 'centers successfully')
    } else {
      console.warn('Failed to load center addresses:', result.message)
    }
  } catch (error) {
    console.error('Error loading center addresses:', error)
  } finally {
    isLoadingCenters.value = false
  }
}

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

// Handle Join Now button click - Opens registration modal
function handleJoinNow(centre: Centre) {
  selectedCentreForModal.value = centre
  showJoinModal.value = true
}

// Handle successful registration
function handleJoinSuccess() {
  console.log('User successfully registered for:', selectedCentreForModal.value?.name)
  // Modal stays open for user to interact with success message and WhatsApp join button
  // User can close it manually by clicking the Close button
}

// Watch for changes to reinitialize map
watch([viewMode, filteredCenters], async () => {
  if (viewMode.value === 'map' && filteredCenters.value.length > 0) {
    await nextTick()
    initializeMap()
  }
})

onMounted(() => {
  // Load center addresses from API
  loadCenterAddresses()

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
