<template>
  <Teleport to="body">
    <!-- Modal Overlay -->
    <div
      v-if="isOpen"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 transition-opacity"
      @click="handleClose"
    >
      <!-- Modal -->
      <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
        @click.self="handleClose"
      >
        <div
          class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative flex flex-col max-h-[90vh] animate-in fade-in slide-in-from-bottom-4"
          @click.stop
        >
          <!-- Header -->
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-4 rounded-t-2xl">
            <div class="flex items-center justify-between mb-2">
              <button
                v-if="currentStep === 2"
                @click="handleBack"
                class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition"
                title="Go back"
              >
                ←
              </button>
              <div v-else></div>
              <button
                @click="handleClose"
                class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition"
                title="Close"
              >
                ✕
              </button>
            </div>

            <div class="text-right mb-1 text-xs font-semibold">Step {{ currentStep }} of 2</div>

            <!-- Step 1 Header -->
            <div v-if="currentStep === 1">
              <h2 class="text-xl font-bold">{{ $t('campaign.findCentre') }}</h2>
              <p class="text-purple-100 mt-1 text-sm">Register for Shivanum Naanum</p>
            </div>

            <!-- Step 2 Header -->
            <div v-else>
              <h2 class="text-xl font-bold">{{ $t('campaign.completeRegistration') }}</h2>
              <p v-if="selectedCentre" class="text-purple-100 mt-1 text-sm">{{ selectedCentre.name }}</p>
            </div>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto">
            <!-- Step 1: Centre Finder Tabs -->
            <div v-if="currentStep === 1" class="flex flex-col h-full">
              <!-- Tab Navigation -->
              <div class="flex border-b border-gray-200">
                <button
                  @click="activeTab = 'nearMe'"
                  :class="['flex-1 flex items-center justify-center gap-2 px-4 py-3 font-medium border-b-2 transition-colors text-sm',
                    activeTab === 'nearMe'
                      ? 'border-purple-600 text-purple-600'
                      : 'border-transparent text-gray-600 hover:text-purple-600']"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  <span>Near Me</span>
                </button>
                <button
                  @click="activeTab = 'browse'"
                  :class="['flex-1 flex items-center justify-center gap-2 px-4 py-3 font-medium border-b-2 transition-colors text-sm',
                    activeTab === 'browse'
                      ? 'border-purple-600 text-purple-600'
                      : 'border-transparent text-gray-600 hover:text-purple-600']"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                  <span>State/District</span>
                </button>
                <button
                  @click="activeTab = 'search'"
                  :class="['flex-1 flex items-center justify-center gap-2 px-4 py-3 font-medium border-b-2 transition-colors text-sm',
                    activeTab === 'search'
                      ? 'border-purple-600 text-purple-600'
                      : 'border-transparent text-gray-600 hover:text-purple-600']"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                  <span>Search</span>
                </button>
              </div>

              <!-- Tab Content -->
              <div class="flex-1 overflow-y-auto">
                <!-- Near Me Tab -->
                <div v-show="activeTab === 'nearMe'" class="p-4 space-y-4">
                  <button
                    v-show="!locationGranted"
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
                    <span>{{ isLoadingLocation ? 'Getting Location...' : 'Use My Location' }}</span>
                  </button>

                  <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    {{ errorMessage }}
                  </div>

                  <div v-if="filteredCenters.length > 0" class="space-y-3">
                    <p class="text-sm font-medium text-gray-700">
                      <strong>{{ filteredCenters.length }}</strong> centres found near you
                    </p>
                    <CentreCardCampaign
                      v-for="centre in filteredCenters"
                      :key="centre.id"
                      :centre="centre"
                      :distance="centreDistances.get(centre.id)"
                      @select="handleCentreSelected"
                    />
                  </div>

                  <div v-else-if="locationGranted" class="py-8 text-center text-gray-500 text-sm">
                    <p>No centres found nearby</p>
                  </div>
                </div>

                <!-- Search Tab -->
                <div v-show="activeTab === 'search'" class="p-4 space-y-4">
                  <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by name, address, district or state..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent text-sm"
                  />

                  <div v-if="searchQuery.trim()">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Search Results ({{ searchResults.length }})</h3>
                    <div v-if="searchResults.length > 0" class="space-y-3">
                      <CentreCardCampaign
                        v-for="centre in searchResults"
                        :key="centre.id"
                        :centre="centre"
                        @select="handleCentreSelected"
                      />
                    </div>
                    <div v-else class="py-8 text-center text-gray-500 text-sm">
                      No centres found matching "{{ searchQuery }}"
                    </div>
                  </div>

                  <div v-else class="py-8 text-center text-gray-500 text-sm">
                    <p>Start typing to search for centres</p>
                  </div>
                </div>

                <!-- Browse Tab -->
                <div v-show="activeTab === 'browse'" class="p-4 space-y-4">
                  <!-- Breadcrumb -->
                  <nav class="flex text-sm" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1">
                      <li>
                        <button @click="resetBrowse" class="text-purple-600 hover:text-purple-800 font-medium">
                          States
                        </button>
                      </li>
                      <li v-if="selectedState">
                        <div class="flex items-center">
                          <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                          </svg>
                          <button @click="showDistricts" class="text-purple-600 hover:text-purple-800 font-medium">
                            {{ selectedState.name }}
                          </button>
                        </div>
                      </li>
                      <li v-if="selectedDistrict">
                        <div class="flex items-center">
                          <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                          </svg>
                          <span class="text-gray-700 font-medium">{{ selectedDistrict.name }}</span>
                        </div>
                      </li>
                    </ol>
                  </nav>

                  <!-- States List -->
                  <div v-if="!selectedState" class="grid grid-cols-2 gap-2">
                    <button
                      v-for="state in mockStates"
                      :key="state.id"
                      @click="selectState(state)"
                      class="p-3 text-left border border-gray-300 rounded-lg hover:border-purple-600 hover:bg-purple-50 transition-all"
                    >
                      <h3 class="font-bold text-purple-700 text-sm">{{ state.name }}</h3>
                      <p class="text-gray-600 text-xs">{{ state.districtCount }} districts</p>
                    </button>
                  </div>

                  <!-- Districts List -->
                  <div v-else-if="selectedState && !selectedDistrict" class="grid grid-cols-2 gap-2">
                    <button
                      v-for="district in selectedState.districts"
                      :key="district.id"
                      @click="selectDistrict(district)"
                      class="p-3 text-left border border-gray-300 rounded-lg hover:border-purple-600 hover:bg-purple-50 transition-all"
                    >
                      <h3 class="font-bold text-purple-700 text-sm">{{ district.name }}</h3>
                      <p class="text-gray-600 text-xs">{{ district.centers.length }} centres</p>
                    </button>
                  </div>

                  <!-- Centers List -->
                  <div v-else class="space-y-3">
                    <CentreCardCampaign
                      v-for="centre in selectedDistrict.centers"
                      :key="centre.id"
                      :centre="centre"
                      @select="handleCentreSelected"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Step 2: Registration Form Modal (separate Teleport) -->
  <JoinEventModal
    :is-open="currentStep === 2 && !!selectedCentre"
    :centre="selectedCentre"
    @close="handleClose"
    @success="handleRegistrationSuccess"
  />
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Centre } from '@/types'
import { useCampaign } from '@/composables/useCampaign'
import { getApiUrl } from '@/config'
import JoinEventModal from './JoinEventModal.vue'
import CentreCardCampaign from './CentreCardCampaign.vue'

interface Props {
  isOpen: boolean
  campaignSource: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
  success: []
}>()

const currentStep = ref<1 | 2>(1)
const selectedCentre = ref<Centre | undefined>(undefined)
const { trackCampaignEvent } = useCampaign()

// Tab management
const activeTab = ref<'nearMe' | 'search' | 'browse'>('nearMe')

// Location detection
const isLoadingLocation = ref(false)
const locationGranted = ref(false)
const errorMessage = ref('')
const userLocation = ref<{ lat: number; lng: number } | null>(null)
const maxDistance = ref(25)

// Search
const searchQuery = ref('')

// Browse
const mockStates = ref<any[]>([])
const selectedState = ref<any>(null)
const selectedDistrict = ref<any>(null)
const isLoadingCenters = ref(false)

// All centres
const allCenters = ref<Centre[]>([])
const centreDistances = ref(new Map<number, number>())

/**
 * Load center addresses from API
 */
async function loadCenterAddresses() {
  isLoadingCenters.value = true
  try {
    const url = getApiUrl('centerAddresses')
    const response = await fetch(url)
    const result = await response.json()

    if (result.success && result.data) {
      const structuredStates = transformCentersToStatesStructure(result.data)
      mockStates.value = structuredStates

      // Store all centers for searching and filtering
      const centers: Centre[] = []
      structuredStates.forEach(state => {
        state.districts.forEach((district: any) => {
          centers.push(...district.centers)
        })
      })
      allCenters.value = centers
      console.log('Loaded', result.data.length, 'centres successfully')
    }
  } catch (error) {
    console.error('Error loading center addresses:', error)
  } finally {
    isLoadingCenters.value = false
  }
}

/**
 * Transform API center data to structured states/districts
 */
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
    const centerObj: Centre = {
      id: center.id,
      center_code: center.center_code,
      name: center.locality || center.center_code,
      address: center.address,
      district: district,
      state: state,
      phone: center.contact_no,
      contact_no: center.contact_no,
      campaign_details: center.campaign_details || undefined,
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

/**
 * Parse latitude_longitude string to get lat/lng
 */
function parseLatLng(latLngString: string): { lat: number; lng: number } | null {
  if (!latLngString) return null
  const parts = latLngString.split(',').map((p: string) => parseFloat(p.trim()))
  if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
    return { lat: parts[0], lng: parts[1] }
  }
  return null
}

/**
 * Calculate distance between two points using Haversine formula
 */
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

/**
 * Get current user location
 */
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
    locationGranted.value = true
  } catch (error) {
    errorMessage.value = 'Unable to get your location. Please check permissions.'
  } finally {
    isLoadingLocation.value = false
  }
}

/**
 * Filtered centers based on location and distance
 */
const filteredCenters = computed(() => {
  if (!userLocation.value) return []

  const centers = allCenters.value
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

  // Store distances for display
  const distances = new Map<number, number>()
  centers.forEach(c => {
    distances.set(c.id, c.distance!)
  })
  centreDistances.value = distances

  return centers
})

/**
 * Search results
 */
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

/**
 * Browse functions
 */
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

/**
 * Handle centre selection
 */
function handleCentreSelected(centre: Centre) {
  selectedCentre.value = centre
  currentStep.value = 2

  // Track with Google Analytics
  trackCampaignEvent('campaign_centre_selected', {
    centre_code: centre.center_code,
    centre_name: centre.name
  })
}

/**
 * Handle registration success
 */
function handleRegistrationSuccess() {
  trackCampaignEvent('campaign_registration_completed', {
    centre_code: selectedCentre.value?.center_code,
    centre_name: selectedCentre.value?.name
  })

  emit('success')
}

/**
 * Go back from step 2 to step 1
 */
function handleBack() {
  currentStep.value = 1
}

/**
 * Close modal
 */
function handleClose() {
  currentStep.value = 1
  selectedCentre.value = undefined
  activeTab.value = 'nearMe'
  userLocation.value = null
  locationGranted.value = false
  searchQuery.value = ''
  selectedState.value = null
  selectedDistrict.value = null
  emit('close')
}

/**
 * Format campaign name for display
 */
function formatCampaignName(source: string): string {
  const names: Record<string, string> = {
    fb: 'Facebook',
    ig: 'Instagram',
    google: 'Google Ads',
    email: 'Email Campaign',
    whatsapp: 'WhatsApp',
    twitter: 'Twitter',
    linkedin: 'LinkedIn'
  }
  return names[source] || source.charAt(0).toUpperCase() + source.slice(1)
}

/**
 * Initialize on mount
 */
onMounted(() => {
  loadCenterAddresses()
})
</script>

<style scoped>
@keyframes slide-in-from-bottom-4 {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation: slide-in-from-bottom-4 0.3s ease-out;
}

.fade-in {
  animation: fade-in 0.3s ease-out;
}

@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
