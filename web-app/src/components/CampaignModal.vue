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
                v-if="currentStep === 2 && !showSuccess"
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

            <!-- Step 1 Header (Registration) -->
            <div v-if="currentStep === 1">
              <h2 class="text-xl font-bold">{{ $t('campaign.completeRegistration') }}</h2>
              <p class="text-purple-100 mt-1 text-sm">Enter your details</p>
            </div>

            <!-- Step 2 Header (Centre Selection) -->
            <div v-else>
              <h2 class="text-xl font-bold">{{ $t('campaign.findCentre') }}</h2>
              <p class="text-purple-100 mt-1 text-sm">Select your nearest centre</p>
            </div>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto">
            <!-- Success Message -->
            <div v-if="showSuccess" class="flex flex-col items-center justify-center h-full p-6 text-center">
              <div class="mb-4">
                <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $t('joinEvent.success') || 'Thank You!' }}</h3>
              <p class="text-gray-600 mb-6">{{ $t('joinEvent.successMessage') || 'You have been registered for the event.' }}</p>
              <div class="space-y-3 w-full">
                <!-- Registration Details -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left text-sm">
                  <p class="text-gray-700"><strong>Name:</strong> {{ registrationDetails?.name }}</p>
                  <p class="text-gray-700 mt-1"><strong>Mobile:</strong> {{ registrationDetails?.mobile }}</p>
                </div>

                <!-- Centre Details -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-left text-sm">
                  <p class="text-gray-700"><strong>📍 Centre:</strong> {{ registrationDetails?.centre_name }}</p>
                  <p v-if="registrationDetails?.centre_address" class="text-gray-600 mt-2 text-xs leading-relaxed">
                    {{ registrationDetails.centre_address }}
                  </p>
                  <p v-if="registrationDetails?.centre_contact" class="text-gray-700 mt-2">
                    <strong>📞</strong> {{ registrationDetails.centre_contact }}
                  </p>
                  <!-- Get Directions Link -->
                  <a
                    v-if="registrationDetails?.centre_lat && registrationDetails?.centre_lng"
                    :href="`https://www.google.com/maps/@${registrationDetails.centre_lat},${registrationDetails.centre_lng},15z`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-block mt-3 text-blue-600 hover:text-blue-800 text-xs font-medium underline"
                  >
                    🗺️ Get Directions
                  </a>
                </div>

                <!-- Join WhatsApp Group Button -->
                <a
                  v-if="whatsappLink"
                  :href="whatsappLink"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn w-full mb-2 flex items-center justify-center gap-2 text-white font-semibold rounded-lg py-3 px-4 transition hover:opacity-90"
                  style="background-color: #25BD4B;"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                  </svg>
                  {{ $t('joinEvent.joinGroup') || 'Join Our WhatsApp Group' }}
                </a>

                <button
                  @click="handleClose"
                  class="w-full btn btn-primary py-3 font-semibold"
                >
                  {{ $t('joinEvent.close') || 'Close' }}
                </button>
              </div>
            </div>

            <!-- Step 1: Registration Form -->
            <div v-else-if="currentStep === 1" class="p-6">
              <form @submit.prevent="handleJoinEventSubmit" class="space-y-4">
                <!-- Name Field -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('joinEvent.nameLabel') || 'Name' }}</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                    :placeholder="$t('joinEvent.namePlaceholder') || 'Enter your name'"
                    required
                  />
                </div>

                <!-- Mobile Field -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('joinEvent.mobileLabel') || 'WhatsApp Number' }}</label>
                  <input
                    v-model="form.mobile"
                    type="tel"
                    class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                    :placeholder="$t('joinEvent.mobilePlaceholder') || '10-digit number'"
                    pattern="[0-9]*"
                    maxlength="10"
                    inputmode="numeric"
                    required
                  />
                  <p class="text-xs text-gray-500 mt-1.5">{{ $t('joinEvent.mobileHelp') || '10-digit WhatsApp number only' }}</p>
                </div>

                <!-- Number of People -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('joinEvent.numberOfPeopleLabel') || 'Number of People' }}</label>
                  <div class="flex items-center gap-3">
                    <button
                      type="button"
                      @click="form.numberOfPeople = Math.max(1, form.numberOfPeople - 1)"
                      class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition"
                    >
                      −
                    </button>
                    <input
                      v-model.number="form.numberOfPeople"
                      type="number"
                      min="1"
                      max="50"
                      class="form-input flex-1 px-4 py-2 border border-gray-300 rounded-lg text-center"
                    />
                    <button
                      type="button"
                      @click="form.numberOfPeople = Math.min(50, form.numberOfPeople + 1)"
                      class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition"
                    >
                      +
                    </button>
                  </div>
                </div>

                <!-- Submit Button -->
                <button
                  type="submit"
                  class="w-full btn btn-primary py-3 font-semibold transition"
                >
                  Continue to Centre Selection
                </button>
              </form>
            </div>

            <!-- Step 2: Centre Finder Tabs -->
            <div v-else class="flex flex-col h-full">
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

</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { Centre } from '@/types'
import { useCampaign } from '@/composables/useCampaign'
import { getApiUrl } from '@/config'
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

// Form state for step 1
const form = reactive({
  name: '',
  mobile: '',
  numberOfPeople: 1
})

// Success state
const showSuccess = ref(false)
const whatsappLink = ref<string>('')
const registrationDetails = ref<{
  id?: number
  name?: string
  mobile?: string
  centre_name?: string
  centre_address?: string
  centre_contact?: string
  centre_lat?: number
  centre_lng?: number
} | null>(null)

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
async function handleCentreSelected(centre: Centre) {
  // Get registration ID from previously stored selectedCentre
  const registrationId = (selectedCentre.value as any)?.registrationId

  if (!registrationId) {
    errorMessage.value = 'Registration data not found. Please start over.'
    currentStep.value = 1
    return
  }

  // Track centre selection
  trackCampaignEvent('campaign_centre_selected', {
    centre_code: centre.center_code,
    centre_name: centre.name
  })

  // Update registration with centre details (PATCH request for step 2)
  try {
    const url = getApiUrl('eventRegister')

    const payload = {
      registration_id: registrationId,
      mobile: form.mobile.trim(),
      center_code: centre.center_code
    }

    console.log('Updating registration with centre:', { url, payload })

    const response = await fetch(url, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    console.log('Response status:', response.status, response.statusText)

    const data = await response.json()
    console.log('Response data:', data)

    if (data.success) {
      // Update selectedCentre with actual centre data
      selectedCentre.value = centre
      handleRegistrationSuccess()
    } else {
      errorMessage.value = data.message || 'Failed to select centre. Please try again.'
    }
  } catch (err: any) {
    console.error('Centre update error:', err)
    console.error('Error details:', err.message, err.cause)
    errorMessage.value = `Error: ${err.message || 'An error occurred. Please try again.'}`
  }
}

/**
 * Handle form submission in step 1
 */
async function handleJoinEventSubmit() {
  // Basic validation
  if (!form.name.trim()) {
    errorMessage.value = 'Please enter your name'
    return
  }

  if (form.name.trim().length < 2) {
    errorMessage.value = 'Name must be at least 2 characters'
    return
  }

  if (!/^[A-Za-z\s]+$/.test(form.name.trim())) {
    errorMessage.value = 'Name can only contain letters and spaces'
    return
  }

  if (!form.mobile.trim()) {
    errorMessage.value = 'Please enter your mobile number'
    return
  }

  if (!/^\d{10}$/.test(form.mobile.trim())) {
    errorMessage.value = 'Mobile number must be exactly 10 digits'
    return
  }

  if (!/^[6-9]/.test(form.mobile.trim())) {
    errorMessage.value = 'Number must start with 6, 7, 8, or 9'
    return
  }

  // Submit to API (step 1: without centre)
  try {
    const url = getApiUrl('eventRegister')
    const { getCampaignSource } = useCampaign()
    const campaignSource = getCampaignSource()

    const payload: any = {
      name: form.name.trim(),
      mobile: form.mobile.trim(),
      number_of_people: form.numberOfPeople
    }

    if (campaignSource) {
      payload.campaign_source = campaignSource
    }

    console.log('Submitting registration step 1:', payload)

    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    const data = await response.json()

    if (data.success && data.data?.id) {
      // Store registration ID for step 2
      const registrationId = data.data.id
      selectedCentre.value = { registrationId } as any

      trackCampaignEvent('campaign_registration_step1_completed', {
        registration_id: registrationId
      })

      errorMessage.value = ''
      currentStep.value = 2
    } else {
      errorMessage.value = data.message || 'Registration failed. Please try again.'
    }
  } catch (err) {
    console.error('Registration error:', err)
    errorMessage.value = 'An error occurred. Please try again.'
  }
}

/**
 * Handle registration success (after selecting centre in step 2)
 */
async function handleRegistrationSuccess() {
  // API call is now handled in handleCentreSelected
  trackCampaignEvent('campaign_registration_completed', {
    centre_code: selectedCentre.value?.center_code,
    centre_name: selectedCentre.value?.name
  })

  // Store registration details for thank you screen (including centre info)
  // Parse latitude_longitude if it's a string (format: "lat,lng")
  let centreLat: number | undefined
  let centreLng: number | undefined

  if (selectedCentre.value?.latitude_longitude) {
    const coords = (selectedCentre.value.latitude_longitude as any).toString().split(',')
    centreLat = parseFloat(coords[0])
    centreLng = parseFloat(coords[1])
  } else if (selectedCentre.value?.latitude && selectedCentre.value?.longitude) {
    centreLat = selectedCentre.value.latitude
    centreLng = selectedCentre.value.longitude
  }

  registrationDetails.value = {
    name: form.name,
    mobile: form.mobile,
    centre_name: selectedCentre.value?.name,
    centre_address: selectedCentre.value?.address,
    centre_contact: selectedCentre.value?.contact_no,
    centre_lat: centreLat,
    centre_lng: centreLng
  }

  // Show success message
  showSuccess.value = true
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
  // If showing success screen, emit success event before closing
  if (showSuccess.value) {
    emit('success')
  }

  currentStep.value = 1
  selectedCentre.value = undefined
  showSuccess.value = false
  registrationDetails.value = null
  form.name = ''
  form.mobile = ''
  form.numberOfPeople = 1
  activeTab.value = 'nearMe'
  userLocation.value = null
  locationGranted.value = false
  searchQuery.value = ''
  selectedState.value = null
  selectedDistrict.value = null
  errorMessage.value = ''
  whatsappLink.value = ''
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
/**
 * Fetch WhatsApp link from API
 */
async function fetchWhatsAppLink() {
  try {
    const baseUrl = getApiUrl('eventRegister').replace('/api/event-register', '')
    const url = baseUrl + '/api/whatsapp-link'
    const response = await fetch(url)
    const data = await response.json()

    if (data.success && data.data?.link) {
      whatsappLink.value = data.data.link
    } else {
      console.warn('Failed to fetch WhatsApp link:', data.message)
    }
  } catch (err) {
    console.error('Error fetching WhatsApp link:', err)
  }
}

onMounted(() => {
  loadCenterAddresses()
  fetchWhatsAppLink()
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
