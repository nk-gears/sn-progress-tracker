<template>
  <teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full z-50 animate-scale-in relative max-h-[90vh] overflow-y-auto">
        <!-- Close Button -->
        <button
          @click="closeModal"
          class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 z-10"
          aria-label="Close modal"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Header -->
        <div class="bg-gradient-to-r from-primary to-blue-600 text-white p-6 rounded-t-lg">
          <h2 class="text-2xl font-bold">Event Reports</h2>
          <p class="text-white/90 text-sm">View all submitted event reports</p>
        </div>

        <!-- Content -->
        <div class="p-6">
          <!-- Loading state -->
          <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="card p-4 animate-pulse">
              <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>

          <!-- Empty state -->
          <div v-else-if="events.length === 0" class="text-center py-8">
            <div class="text-5xl mb-4">📋</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No event reports yet</h3>
            <p class="text-gray-500">Start by submitting your first event report</p>
          </div>

          <!-- Events list -->
          <div v-else class="space-y-4">
            <div
              v-for="(event, index) in events"
              :key="index"
              class="card p-4 hover:shadow-md transition-shadow border-l-4 border-primary"
            >
              <!-- Event Title and Date -->
              <div class="mb-3">
                <h3 class="text-lg font-bold text-gray-900">{{ event.title }}</h3>
                <p class="text-sm text-gray-600 mt-1">
                  📅 {{ formatDate(event.event_date) }} at ⏰ {{ event.event_time }}
                </p>
              </div>

              <!-- Description preview -->
              <p class="text-gray-700 text-sm mb-3 line-clamp-2">{{ event.description }}</p>

              <!-- Participants count -->
              <div class="text-sm text-gray-600 mb-2">
                <span class="font-medium">👥 Participants:</span> {{ event.participants }}
              </div>

              <!-- Media indicators -->
              <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                <div v-if="event.photo_names && countFiles(event.photo_names) > 0" class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
                  </svg>
                  <span>{{ countFiles(event.photo_names) }} photos</span>
                </div>
                <div v-if="event.video_name" class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17 10.5V7c0 .55-.45 1-1 1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z" />
                  </svg>
                  <span>Video</span>
                </div>
              </div>

              <!-- Submitted time -->
              <div class="text-xs text-gray-500 mb-3">
                Submitted: {{ formatDateTime(event.submitted_at) }}
              </div>

              <!-- View folder link -->
              <div v-if="event.folder_url" class="pt-3 border-t border-gray-200">
                <a
                  :href="event.folder_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-sm text-primary hover:underline flex items-center gap-1 inline-flex"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                  View in Drive
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 p-4 bg-gray-50 rounded-b-lg flex gap-3">
          <button
            @click="closeModal"
            class="flex-1 py-2 bg-gray-200 text-gray-800 font-bold rounded-lg hover:bg-gray-300 transition-colors"
          >
            Close
          </button>
          <button
            @click="$emit('addReport')"
            class="flex-1 py-2 bg-gradient-to-r from-primary to-blue-600 text-white font-bold rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Add Report</span>
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useEventReportStore } from '@/stores/eventReport'

interface Props {
  isOpen: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'addReport'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const authStore = useAuthStore()
const eventReportStore = useEventReportStore()

const isLoading = ref(false)
const events = ref<any[]>([])

const closeModal = () => {
  emit('close')
}

const loadEvents = async () => {
  const branchName = authStore.currentBranch?.name
  console.log('Loading events for branch:', branchName)
  if (!branchName) {
    console.warn('No branch name available')
    return
  }

  isLoading.value = true
  try {
    await eventReportStore.fetchEvents(branchName)
    events.value = eventReportStore.events
    console.log('Events loaded:', events.value.length)
  } catch (error) {
    console.error('Error loading events:', error)
  } finally {
    isLoading.value = false
  }
}

const formatDate = (dateString: string): string => {
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-IN', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch {
    return dateString
  }
}

const formatDateTime = (dateTimeString: string): string => {
  try {
    const date = new Date(dateTimeString)
    return date.toLocaleDateString('en-IN', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateTimeString
  }
}

const countFiles = (fileNames: string): number => {
  if (!fileNames) return 0
  return fileNames.split(', ').filter(name => name.trim()).length
}

// Load events when modal opens
watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      loadEvents()
    }
  }
)
</script>

<style scoped>
@keyframes scaleIn {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-scale-in {
  animation: scaleIn 0.3s ease-out;
}
</style>
