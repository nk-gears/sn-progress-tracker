<template>
  <div class="relative">
    <!-- API Status Badge -->
    <button
      @click="showSwitcher = !showSwitcher"
      class="flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-medium transition-colors"
      :class="statusClasses"
    >
      <div class="w-2 h-2 rounded-full" :class="indicatorClasses"></div>
      <span>{{ status.mode.toUpperCase() }}</span>
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
      </svg>
    </button>

    <!-- API Switcher Dropdown -->
    <div
      v-if="showSwitcher"
      class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-lg border p-4 z-50"
      @click.stop
    >
      <div class="mb-4">
        <h3 class="font-semibold text-gray-900 mb-1">API Configuration</h3>
        <p class="text-sm text-gray-600">{{ status.description }}</p>
      </div>

      <!-- API Mode Options -->
      <div class="space-y-3">
        <label class="flex items-center">
          <input
            type="radio"
            value="mock"
            v-model="selectedMode"
            @change="handleModeChange"
            class="form-radio text-primary"
          >
          <div class="ml-3">
            <div class="font-medium text-gray-900">Mock API</div>
            <div class="text-sm text-gray-500">Use simulated data (development)</div>
          </div>
        </label>

        <label class="flex items-center">
          <input
            type="radio"
            value="real"
            v-model="selectedMode"
            @change="handleModeChange"
            class="form-radio text-primary"
          >
          <div class="ml-3">
            <div class="font-medium text-gray-900">Real API</div>
            <div class="text-sm text-gray-500">Connect to PHP backend</div>
          </div>
        </label>

        <label class="flex items-center">
          <input
            type="radio"
            value="auto"
            v-model="selectedMode"
            @change="handleModeChange"
            class="form-radio text-primary"
          >
          <div class="ml-3">
            <div class="font-medium text-gray-900">Auto Mode</div>
            <div class="text-sm text-gray-500">Try real API, fallback to mock</div>
          </div>
        </label>
      </div>

      <!-- Current URL -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <div class="text-xs text-gray-500 mb-2">Current URL:</div>
        <div class="text-xs font-mono bg-gray-100 p-2 rounded break-all">
          {{ currentUrl }}
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-4 flex justify-between">
        <button
          @click="copyUrl"
          class="text-sm text-primary hover:text-primary-dark"
        >
          Copy URL
        </button>
        <button
          @click="showSwitcher = false"
          class="text-sm text-gray-500 hover:text-gray-700"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { apiService } from '@/services/apiService'
import { useAppStore } from '@/stores/app'

const appStore = useAppStore()

// Local state
const showSwitcher = ref(false)
const selectedMode = ref(apiService.getMode())

// Computed properties
const status = computed(() => apiService.getStatus())

const statusClasses = computed(() => {
  const baseClasses = 'hover:opacity-80'
  switch (status.value.color) {
    case 'blue':
      return `${baseClasses} bg-blue-100 text-blue-800`
    case 'green':
      return `${baseClasses} bg-green-100 text-green-800`
    case 'purple':
      return `${baseClasses} bg-purple-100 text-purple-800`
    default:
      return `${baseClasses} bg-gray-100 text-gray-800`
  }
})

const indicatorClasses = computed(() => {
  switch (status.value.color) {
    case 'blue':
      return 'bg-blue-500'
    case 'green':
      return 'bg-green-500'
    case 'purple':
      return 'bg-purple-500'
    default:
      return 'bg-gray-500'
  }
})

const currentUrl = computed(() => window.location.href)

// Methods
const handleModeChange = () => {
  apiService.setMode(selectedMode.value as 'mock' | 'real' | 'auto')
  
  const modeLabels = {
    mock: 'Mock API',
    real: 'Real API',
    auto: 'Auto Mode'
  }
  
  appStore.showSuccess(`Switched to ${modeLabels[selectedMode.value as keyof typeof modeLabels]}`)
  
  // Refresh the page to apply changes
  setTimeout(() => {
    window.location.reload()
  }, 1000)
}

const copyUrl = async () => {
  try {
    await navigator.clipboard.writeText(currentUrl.value)
    appStore.showSuccess('URL copied to clipboard!')
  } catch (error) {
    console.error('Failed to copy URL:', error)
    appStore.showError('Failed to copy URL')
  }
}

// Close dropdown when clicking outside
const handleClickOutside = (event: Event) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) {
    showSwitcher.value = false
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.form-radio:checked {
  background-color: #04349C;
  border-color: #04349C;
}
</style>