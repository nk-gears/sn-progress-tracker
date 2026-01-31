<template>
  <div class="flex flex-col h-full">
    <!-- Search Box -->
    <div class="p-4 border-b border-gray-200 bg-white sticky top-0">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="🔍 Search centres..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center p-4">
      <div class="text-center">
        <div class="animate-spin w-8 h-8 border-4 border-purple-600 border-t-transparent rounded-full mx-auto mb-3"></div>
        <p class="text-gray-600 text-sm">Loading centres...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex-1 flex items-center justify-center p-4">
      <div class="text-center">
        <p class="text-red-600 text-sm font-medium mb-2">⚠️ Unable to load centres</p>
        <p class="text-gray-600 text-xs">{{ error }}</p>
        <button
          @click="loadCentres"
          class="mt-3 px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition"
        >
          Try Again
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-else class="flex-1 overflow-y-auto">
      <!-- Browse View (Accordion) -->
      <div class="p-4 space-y-2">
        <!-- Collapsible States -->
        <div
          v-for="state in filteredStates"
          :key="state.name"
          class="border border-gray-200 rounded-lg overflow-hidden"
        >
          <!-- State Header -->
          <button
            @click="toggleState(state.name)"
            class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition"
          >
            <span class="font-semibold text-gray-800 text-sm">{{ state.name }}</span>
            <span class="text-gray-500">{{ expandedStates.has(state.name) ? '▼' : '▶' }}</span>
          </button>

          <!-- Districts List -->
          <transition
            enter-active-class="transition-all duration-200"
            leave-active-class="transition-all duration-200"
            enter-from-class="max-h-0 overflow-hidden"
            enter-to-class="max-h-96 overflow-hidden"
            leave-from-class="max-h-96 overflow-hidden"
            leave-to-class="max-h-0 overflow-hidden"
          >
            <div v-show="expandedStates.has(state.name)" class="bg-white">
              <!-- District Collapse -->
              <div
                v-for="district in state.districts"
                :key="`${state.name}-${district.name}`"
                class="border-t border-gray-100"
              >
                <button
                  @click="toggleDistrict(`${state.name}-${district.name}`)"
                  class="w-full px-4 py-2 flex items-center justify-between hover:bg-gray-50 transition text-left"
                >
                  <span class="text-gray-700 text-sm font-medium">{{ district.name }}</span>
                  <span class="text-gray-400 text-xs">
                    {{ expandedDistricts.has(`${state.name}-${district.name}`) ? '▼' : '▶' }}
                  </span>
                </button>

                <!-- Centres List -->
                <transition
                  enter-active-class="transition-all duration-200"
                  leave-active-class="transition-all duration-200"
                  enter-from-class="max-h-0 overflow-hidden"
                  enter-to-class="max-h-96 overflow-hidden"
                  leave-from-class="max-h-96 overflow-hidden"
                  leave-to-class="max-h-0 overflow-hidden"
                >
                  <div
                    v-show="expandedDistricts.has(`${state.name}-${district.name}`)"
                    class="bg-gray-50 py-2"
                  >
                    <div
                      v-for="centre in district.centers"
                      :key="centre.id"
                      class="px-4 py-2 hover:bg-white transition cursor-pointer"
                      @click="selectCentre(centre)"
                    >
                      <p class="font-semibold text-purple-600 text-sm">{{ centre.name }}</p>
                      <p class="text-gray-600 text-xs mt-1">{{ centre.address }}</p>
                      <button
                        @click.stop="selectCentre(centre)"
                        class="mt-2 w-full px-3 py-1 text-xs font-semibold text-purple-600 border border-purple-600 rounded hover:bg-purple-50 transition"
                      >
                        Select Centre
                      </button>
                    </div>
                    <p v-if="district.centers.length === 0" class="px-4 py-2 text-gray-500 text-xs">
                      No centres available
                    </p>
                  </div>
                </transition>
              </div>
            </div>
          </transition>
        </div>

        <!-- No Results Message -->
        <div v-if="filteredStates.length === 0" class="py-8 text-center">
          <p class="text-gray-500 text-sm">No centres match your search</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Centre } from '@/types'
import { getApiUrl } from '@/config'

interface District {
  id: number
  name: string
  centers: Centre[]
}

interface State {
  id: number
  name: string
  districts: District[]
}

const emit = defineEmits<{
  centreSelected: [centre: Centre]
}>()

const searchQuery = ref('')
const isLoading = ref(true)
const error = ref<string | null>(null)
const states = ref<State[]>([])
const expandedStates = ref(new Set<string>())
const expandedDistricts = ref(new Set<string>())

/**
 * Load centres from API
 */
async function loadCentres() {
  isLoading.value = true
  error.value = null

  try {
    const url = getApiUrl('centerAddresses')
    const response = await fetch(url)
    const result = await response.json()

    if (result.success && result.data) {
      // Transform API data to state/district hierarchy
      states.value = transformCentres(result.data)
      // Expand first state by default
      if (states.value.length > 0) {
        expandedStates.value.add(states.value[0].name)
      }
    } else {
      error.value = 'Failed to load centres'
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred'
    console.error('Error loading centres:', err)
  } finally {
    isLoading.value = false
  }
}

/**
 * Transform API response to state/district hierarchy
 */
function transformCentres(centers: any[]): State[] {
  const statesMap = new Map<string, State>()

  centers.forEach((center: any) => {
    const state = center.state || 'Unknown'
    const district = center.district || 'Unknown'

    if (!statesMap.has(state)) {
      statesMap.set(state, {
        id: statesMap.size + 1,
        name: state,
        districts: new Map<string, District>() as any
      })
    }

    const stateObj = statesMap.get(state)!
    const districtsMap = stateObj.districts as Map<string, District>

    if (!districtsMap.has(district)) {
      districtsMap.set(district, {
        id: districtsMap.size + 1,
        name: district,
        centers: []
      })
    }

    const centerObj: Centre = {
      id: center.id,
      center_code: center.center_code,
      name: center.locality || center.center_code || 'Unknown',
      address: center.address,
      district: district,
      state: state,
      phone: center.contact_no,
      contact_no: center.contact_no,
      campaign_details: center.campaign_details || undefined,
      latitude: parseFloat(center.latitude_longitude?.split(',')[0] || '0'),
      longitude: parseFloat(center.latitude_longitude?.split(',')[1] || '0')
    }

    districtsMap.get(district)!.centers.push(centerObj)
  })

  // Convert Map to Array
  return Array.from(statesMap.values()).map((state: any) => ({
    ...state,
    districts: Array.from(state.districts.values())
  }))
}

/**
 * Filter states based on search query
 */
const filteredStates = computed(() => {
  if (!searchQuery.value.trim()) {
    return states.value
  }

  const query = searchQuery.value.toLowerCase()

  return states.value
    .map((state) => ({
      ...state,
      districts: state.districts
        .map((district) => ({
          ...district,
          centers: district.centers.filter(
            (center) =>
              center.name.toLowerCase().includes(query) ||
              center.address.toLowerCase().includes(query) ||
              district.name.toLowerCase().includes(query) ||
              state.name.toLowerCase().includes(query)
          )
        }))
        .filter((district) => district.centers.length > 0)
    }))
    .filter((state) => state.districts.length > 0)
})

/**
 * Toggle state accordion
 */
function toggleState(stateName: string) {
  if (expandedStates.value.has(stateName)) {
    expandedStates.value.delete(stateName)
  } else {
    expandedStates.value.add(stateName)
  }
}

/**
 * Toggle district accordion
 */
function toggleDistrict(key: string) {
  if (expandedDistricts.value.has(key)) {
    expandedDistricts.value.delete(key)
  } else {
    expandedDistricts.value.add(key)
  }
}

/**
 * Select a centre and emit event
 */
function selectCentre(centre: Centre) {
  emit('centreSelected', centre)
}

/**
 * Load centres on mount
 */
onMounted(() => {
  loadCentres()
})
</script>

<style scoped>
/* Smooth transitions */
.transition {
  transition: all 0.2s ease;
}
</style>
