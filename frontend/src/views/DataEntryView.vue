<template>
  <BaseLayout>
    <div class="p-4 max-w-md mx-auto">
      <!-- Metrics Section -->
      <div class="card p-4 mb-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <span class="text-xl">⏱️</span>
            </div>
            <div>
              <p class="text-sm text-gray-600">Total Hours of Meditation so far</p>
              <p class="text-xl font-bold text-primary">{{ totalHours }} hours</p>
            </div>
          </div>
          
         
        </div>
      </div>
      
      <div class="card p-6">
        <h2 class="text-xl font-bold text-primary mb-6 flex items-center">
          <span class="mr-2">📝</span>
          <span>Record Session</span>
        </h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">

          <!-- Session Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Session Date
            </label>
            <input
              v-model="sessionForm.session_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200"
              :min="minDate"
              :max="today"
            >
          </div>

          <!-- Participant Input Component -->
          <ParticipantInput
            v-model="sessionForm.participant_name"
            @participant-selected="handleParticipantSelected"
          />

          <!-- Time Slot Selector Component - Show when participant is selected -->
          <div v-if="selectedParticipant">
            <!-- Last session info message - TEMPORARILY HIDDEN -->
            <!-- <div v-if="lastSessionInfo" class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
              {{ lastSessionInfo }}
              <span class="block text-xs text-green-600 mt-1">You can adjust the time if needed</span>
            </div> -->
            
            <TimeSlotSelector
              v-model="selectedTimeRanges"
              @duration-changed="handleDurationChanged"
            />
          </div>

          <!-- Message when no participant is selected -->
          <div v-if="!selectedParticipant" class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-center">
            <div class="flex items-center justify-center mb-2">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span class="font-medium">Please select a participant first</span>
            </div>
            <p class="text-sm text-blue-600">Start typing a name above to search and select from existing participants</p>
          </div>

          <!-- Error Display -->
          <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            {{ error }}
          </div>

          <!-- Success Display -->
          <div v-if="success" class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ success }}
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading || !isFormValid"
            class="w-full btn-primary"
          >
            <span v-if="isLoading" class="flex items-center justify-center">
              <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
              Recording...
            </span>
            <span v-else>Record Session</span>
          </button>
        </form>
      </div>

      <!-- Sessions for Selected Date -->
      <div v-if="dateSessionsList.length > 0" class="card p-4 mt-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center justify-between">
          <div class="flex items-center">
            <span class="mr-2">📋</span>
            Sessions for {{ formatSelectedDate }}
            <span class="ml-2 text-sm font-normal text-gray-600">({{ dateSessionsList.length }})</span>
          </div>
          <div class="text-sm font-medium text-primary bg-blue-50 px-3 py-1 rounded-full">
            {{ totalSessionHours }}h total
          </div>
        </h3>
        
        <div class="space-y-2">
          <div
            v-for="session in dateSessionsList"
            :key="session.id"
            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex-1">
              <div class="font-medium text-gray-900">{{ session.participant_name }}</div>
              <div class="text-sm text-gray-600 flex items-center space-x-1">
                <span class="flex items-baseline space-x-1">
                  <span class="font-medium">{{ getTimeOnly(session.start_time) }}</span>
                  <span class="text-xs">{{ getAmPm(session.start_time) }}</span>
                </span>
                <span>•</span>
                <span>{{ session.duration_minutes }}min</span>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="text-xs text-gray-500">
                {{ new Date(session.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }}
              </div>
              <button
                @click="deleteSession(session)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                title="Delete session"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BaseLayout from '@/components/BaseLayout.vue'
import ParticipantInput from '@/components/ParticipantInput.vue'
import TimeSlotSelector from '@/components/TimeSlotSelector.vue'
import { useSessionsStore } from '@/stores/sessions'
import { useParticipantsStore } from '@/stores/participants'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import { apiService } from '@/services/apiService'
import type { Participant, Session } from '@/types'

const router = useRouter()
const sessionsStore = useSessionsStore()
const participantsStore = useParticipantsStore()
const authStore = useAuthStore()
const appStore = useAppStore()

// Local state
const isLoading = ref(false)
const error = ref('')
const success = ref('')
const selectedParticipant = ref<Participant | null>(null)
const currentDuration = ref(0)
const dateSessionsList = ref<Session[]>([])
const lastSessionInfo = ref<string>('')

// Computed properties
const sessionForm = computed(() => sessionsStore.sessionForm)
const selectedTimeRanges = computed({
  get: () => sessionsStore.selectedTimeRanges,
  set: (value) => {
    sessionsStore.selectedTimeRanges = value
  }
})
const today = computed(() => new Date().toISOString().slice(0, 10))
const minDate = computed(() => '2025-08-25')

// Metrics computed properties
const totalHours = computed(() => {
  return sessionsStore.dashboardData?.summary?.total_hours || 0
})

const catchupHours = computed(() => {
  // Assuming a target of 48 hours for catchup (example)
  const targetHours = 48
  const currentHours = totalHours.value
  return Math.max(0, targetHours - currentHours)
})

const daysRemaining = computed(() => {
  // Calculate days remaining until end of month
  const now = new Date()
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0)
  const diffTime = endOfMonth.getTime() - now.getTime()
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.max(0, diffDays)
})

const formatSelectedDate = computed(() => {
  const date = new Date(sessionForm.value.session_date)
  return date.toLocaleDateString('en-US', { 
    weekday: 'short', 
    month: 'short', 
    day: 'numeric' 
  })
})

const totalSessionHours = computed(() => {
  // Group sessions by unique time slots (start_time + duration)
  const uniqueTimeSlots = new Map<string, number>()
  
  dateSessionsList.value.forEach(session => {
    const timeSlotKey = `${session.start_time}-${session.duration_minutes}`
    if (!uniqueTimeSlots.has(timeSlotKey)) {
      uniqueTimeSlots.set(timeSlotKey, session.duration_minutes)
    }
  })
  
  // Sum only the unique time slot durations
  const totalMinutes = Array.from(uniqueTimeSlots.values()).reduce((total, minutes) => {
    return total + minutes
  }, 0)
  
  return (totalMinutes / 60).toFixed(1)
})

const isFormValid = computed(() => {
  return (
    sessionForm.value.session_date >= minDate.value &&
    selectedParticipant.value !== null &&
    selectedTimeRanges.value.ranges.length > 0 &&
    selectedTimeRanges.value.totalDuration >= 30 &&
    [30, 60, 90, 120].includes(selectedTimeRanges.value.totalDuration)
  )
})

// Methods
const formatTime = (time: string) => sessionsStore.formatTime(time)

const getTimeOnly = (time: string): string => {
  const formatted = sessionsStore.formatTime(time)
  return formatted.split(' ')[0]
}

const getAmPm = (time: string): string => {
  const formatted = sessionsStore.formatTime(time)
  return formatted.split(' ')[1]
}

const loadSessionsForDate = async (date: string) => {
  const branchId = authStore.currentBranch?.id
  if (!branchId) return

  try {
    const response = await apiService.sessions.getByDate(branchId, date)
    if (response.success) {
      dateSessionsList.value = response.sessions || []
    }
  } catch (error) {
    console.error('Error loading sessions for date:', error)
  }
}


const deleteSession = async (session: Session) => {
  if (!confirm(`Delete session for ${session.participant_name}?`)) return

  try {
    const success = await sessionsStore.deleteSession(session.id)
    if (success) {
      // Remove from local list
      dateSessionsList.value = dateSessionsList.value.filter(s => s.id !== session.id)
      appStore.showSuccess('Session deleted successfully')
    } else {
      appStore.showError('Failed to delete session')
    }
  } catch (error) {
    console.error('Error deleting session:', error)
    appStore.showError('Failed to delete session')
  }
}

const handleParticipantSelected = async (participant: Participant | null) => {
  selectedParticipant.value = participant
  
  // Clear time selection and last session info when participant is cleared
  if (!participant) {
    selectedTimeRanges.value = { ranges: [], totalDuration: 0 }
    lastSessionInfo.value = ''
    return
  }
  
  // TEMPORARILY DISABLED: Pre-fill functionality
  // For existing participants with an ID, fetch last session and pre-populate time
  /* const branchId = authStore.currentBranch?.id
  if (branchId && participant.id) {
    try {
      const response = await apiService.participants.getLastSession(participant.id, branchId)
      if (response.success && response.last_session) {
        const lastSession = response.last_session
        // Pre-populate the time selection with the last session time
        const startTime = lastSession.start_time
        const duration = lastSession.duration_minutes
        
        // Calculate end time
        const startMinutes = sessionsStore.timeToMinutes(startTime)
        const endMinutes = startMinutes + duration
        const endTime = sessionsStore.minutesToTime(endMinutes)
        
        // Set the time range
        selectedTimeRanges.value = {
          ranges: [{
            start: startTime,
            end: endTime
          }],
          totalDuration: duration
        }
        
        // Show user-friendly message
        const timeFormatted = formatTime(startTime)
        lastSessionInfo.value = `⏱️ Pre-filled with last session time: ${timeFormatted} (${duration}min)`
        
        console.log(`Pre-populated time from last session: ${startTime} for ${duration} minutes`)
      } else {
        // No previous session for existing participant, clear selection
        selectedTimeRanges.value = { ranges: [], totalDuration: 0 }
        lastSessionInfo.value = ''
      }
    } catch (error) {
      console.error('Error fetching last session:', error)
      // Clear selection on error
      selectedTimeRanges.value = { ranges: [], totalDuration: 0 }
      lastSessionInfo.value = ''
    }
  } else {
    // For new participants (no ID yet), clear time selection and last session info
    selectedTimeRanges.value = { ranges: [], totalDuration: 0 }
    lastSessionInfo.value = ''
  } */
  
  // Always clear time selection when participant is selected
  selectedTimeRanges.value = { ranges: [], totalDuration: 0 }
  lastSessionInfo.value = ''
}

const handleDurationChanged = (duration: number) => {
  currentDuration.value = duration
}

const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  
  // Validate date range
  if (sessionForm.value.session_date < minDate.value) {
    error.value = 'Session date cannot be earlier than August 25, 2025'
    return
  }
  
  // Validate participant selection
  if (!selectedParticipant.value) {
    error.value = 'Please select an existing participant from the list'
    return
  }
  
  if (selectedTimeRanges.value.ranges.length === 0 || selectedTimeRanges.value.totalDuration < 30) {
    error.value = 'Please select valid time ranges (minimum 30 minutes total)'
    return
  }
  
  if (![30, 60, 90, 120].includes(selectedTimeRanges.value.totalDuration)) {
    error.value = 'Total duration must be exactly 30, 60, 90, or 120 minutes'
    return
  }
  
  const branchId = authStore.currentBranch?.id
  const userId = authStore.user?.id
  
  if (!branchId || !userId) {
    error.value = 'Missing branch or user information'
    return
  }
  
  isLoading.value = true
  appStore.setLoading(true)
  
  try {
    // Use the selected participant (no need to find or create)
    const participant = selectedParticipant.value
    
    if (!participant) {
      error.value = 'Please select a valid participant'
      return
    }
    
    // Create multiple sessions for multiple ranges
    const createdSessions: Session[] = []
    const skippedSessions: string[] = []
    
    for (const range of selectedTimeRanges.value.ranges) {
      if (!range.start || !range.end) continue
      
      const startMinutes = sessionsStore.timeToMinutes(range.start)
      const endMinutes = sessionsStore.timeToMinutes(range.end)
      const duration = endMinutes - startMinutes
      
      const sessionData = {
        participant_id: participant.id,
        participant_name: participant.name,
        branch_id: branchId,
        volunteer_id: userId,
        session_date: sessionForm.value.session_date,
        start_time: range.start,
        duration_minutes: duration
      }
      
      const result = await sessionsStore.createSession(sessionData)
      
      if (result.success) {
        // Create a mock session object for immediate display
        const newSession: Session = {
          id: Date.now() + Math.random(), // Temporary unique ID
          participant_id: sessionData.participant_id,
          participant_name: sessionData.participant_name,
          branch_id: sessionData.branch_id,
          volunteer_id: sessionData.volunteer_id,
          session_date: sessionData.session_date,
          start_time: sessionData.start_time,
          duration_minutes: sessionData.duration_minutes,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        }
        createdSessions.push(newSession)
      } else {
        if (result.errorType === 'duplicate_session') {
          // Handle duplicate session - skip and continue with other sessions
          const timeFormatted = formatTime(range.start)
          skippedSessions.push(`${timeFormatted} (${duration}min)`)
        } else {
          // Other errors - stop processing
          error.value = result.error || 'Failed to create session'
          return
        }
      }
    }
    
    let success_msg = ''
    if (createdSessions.length > 0) {
      const rangeCount = createdSessions.length
      success_msg = `${rangeCount > 1 ? rangeCount + ' sessions' : 'Session'} recorded successfully!`
      
      // Add new sessions to local list
      dateSessionsList.value.unshift(...createdSessions)
    }
    
    // Handle mixed results (some successful, some duplicates)
    if (skippedSessions.length > 0) {
      if (createdSessions.length > 0) {
        // Some sessions created, some skipped
        success_msg += `\n⚠️ Skipped ${skippedSessions.length} duplicate session${skippedSessions.length > 1 ? 's' : ''}: ${skippedSessions.join(', ')}`
        appStore.showSuccess(success_msg)
      } else {
        // All sessions were duplicates
        error.value = `All selected time slots already have sessions for ${participant.name} on this date. Skipped: ${skippedSessions.join(', ')}`
        return
      }
    } else if (createdSessions.length > 0) {
      // All sessions created successfully
      sessionsStore.clearForm(true) // Preserve the date
      participantsStore.clearSearch()
    } else {
      // No sessions created and no duplicates (shouldn't happen)
      error.value = 'No sessions were created'
      return
    }
    
    success.value = success_msg
    appStore.showSuccess(success_msg)
    
    // Load updated sessions for the current date
    loadSessionsForDate(sessionForm.value.session_date)
    
    // Clear success message after delay
    setTimeout(() => {
      success.value = ''
    }, 3000)
    
  } catch (err) {
    console.error('Session submission error:', err)
    error.value = 'An error occurred while saving the session'
    appStore.showError('An error occurred while saving the session')
  } finally {
    isLoading.value = false
    appStore.setLoading(false)
  }
}


// Watch for date changes to load sessions
watch(() => sessionForm.value.session_date, (newDate) => {
  if (newDate) {
    loadSessionsForDate(newDate)
  }
})


// Load initial data
const loadData = async () => {
  // Load dashboard data for metrics
  await sessionsStore.loadDashboardData()
  
  // Load sessions for current date
  loadSessionsForDate(sessionForm.value.session_date)
  
  // Set initial duration from ranges
  currentDuration.value = selectedTimeRanges.value.totalDuration
}

// Lifecycle
onMounted(async () => {
  await loadData()
})

// Watch for branch changes
watch(() => authStore.currentBranch, (newBranch, oldBranch) => {
  if (newBranch && newBranch.id !== oldBranch?.id) {
    // Clear form and reload data when branch changes
    sessionsStore.clearForm()
    selectedParticipant.value = null
    selectedTimeRanges.value = { ranges: [], totalDuration: 0 }
    lastSessionInfo.value = ''
    loadData()
  }
}, { immediate: false })
</script>