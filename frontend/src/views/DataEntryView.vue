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
          
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
              <span class="text-xl">🎯</span>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-600">Hours to Catchup</p>
              <p class="text-xl font-bold text-orange-600">{{ catchupHours }} Hours</p>
              <p class="text-xs text-gray-500">in next {{ daysRemaining }} days</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card p-6">
        <h2 class="text-xl font-bold text-primary mb-6 flex items-center">
          <span class="mr-2">📝</span>
          <span v-if="editingSession">Edit Session</span>
          <span v-else>Record Session</span>
        </h2>
        
        <!-- Edit mode indicator -->
        <div v-if="editingSession" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
          <div class="flex items-center text-blue-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span class="font-medium flex items-center">
              Editing session from 
              <span class="ml-1 flex flex-col text-center">
                <span class="font-semibold">{{ getTimeOnly(editingSession.start_time) }}</span>
                <span class="text-xs">{{ getAmPm(editingSession.start_time) }}</span>
              </span>
            </span>
          </div>
          <button @click="cancelEdit" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
            Cancel and create new session instead
          </button>
        </div>

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
              :max="today"
            >
          </div>

          <!-- Participant Input Component -->
          <ParticipantInput
            v-model="sessionForm.participant_name"
            v-model:age="sessionForm.participant_age"
            v-model:gender="sessionForm.participant_gender"
            @participant-selected="handleParticipantSelected"
          />

          <!-- Time Slot Selector Component -->
          <TimeSlotSelector
            v-model="selectedTimeRange"
            @duration-changed="handleDurationChanged"
          />

          <!-- Error Display -->
          <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            {{ error }}
          </div>

          <!-- Success Display -->
          <div v-if="success" class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ success }}
          </div>

          <!-- Submit Buttons -->
          <div class="flex space-x-3">
            <button
              v-if="editingSession"
              @click="cancelEdit"
              type="button"
              class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-4 px-4 rounded-xl font-medium text-lg touch-target transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isLoading || !isFormValid"
              class="flex-1 btn-primary"
            >
              <span v-if="isLoading" class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                <span v-if="editingSession">Updating...</span>
                <span v-else>Recording...</span>
              </span>
              <span v-else>
                <span v-if="editingSession">Update Session</span>
                <span v-else>Record Session</span>
              </span>
            </button>
          </div>
        </form>
      </div>

      <!-- Sessions for Selected Date -->
      <div v-if="dateSessionsList.length > 0" class="card p-4 mt-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
          <span class="mr-2">📋</span>
          Sessions for {{ formatSelectedDate }}
          <span class="ml-2 text-sm font-normal text-gray-600">({{ dateSessionsList.length }})</span>
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
                @click="editSession(session)"
                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                title="Edit session"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
              </button>
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

// Computed properties
const sessionForm = computed(() => sessionsStore.sessionForm)
const selectedTimeRange = computed({
  get: () => sessionsStore.selectedTimeRange,
  set: (value) => {
    sessionsStore.selectedTimeRange = value
  }
})
const editingSession = computed(() => sessionsStore.editingSession)
const today = computed(() => new Date().toISOString().slice(0, 10))

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

const isFormValid = computed(() => {
  return (
    sessionForm.value.participant_name.trim().length > 0 &&
    selectedTimeRange.value.start &&
    selectedTimeRange.value.end &&
    currentDuration.value >= 30 &&
    [30, 60, 90, 120].includes(currentDuration.value)
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

const editSession = (session: Session) => {
  sessionsStore.startEdit(session)
  // Scroll to top to show edit form
  window.scrollTo({ top: 0, behavior: 'smooth' })
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

const handleParticipantSelected = (participant: Participant | null) => {
  selectedParticipant.value = participant
}

const handleDurationChanged = (duration: number) => {
  currentDuration.value = duration
}

const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  
  if (!selectedTimeRange.value.start || currentDuration.value < 30) {
    error.value = 'Please select a valid time range (minimum 30 minutes)'
    return
  }
  
  if (![30, 60, 90, 120].includes(currentDuration.value)) {
    error.value = 'Duration must be exactly 30, 60, 90, or 120 minutes'
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
    // Find or create participant
    const participant = await participantsStore.findOrCreateParticipant(
      sessionForm.value.participant_name,
      sessionForm.value.participant_age || undefined,
      sessionForm.value.participant_gender || undefined
    )
    
    if (!participant) {
      error.value = 'Failed to create or find participant'
      return
    }
    
    const sessionData = {
      participant_id: participant.id,
      participant_name: participant.name,
      branch_id: branchId,
      volunteer_id: userId,
      session_date: sessionForm.value.session_date,
      start_time: selectedTimeRange.value.start!,
      duration_minutes: currentDuration.value
    }
    
    let success_msg = ''
    
    if (editingSession.value) {
      // Update existing session
      const updateSuccess = await sessionsStore.updateSession(editingSession.value.id, sessionData)
      
      if (updateSuccess) {
        success_msg = 'Session updated successfully!'
        sessionsStore.cancelEdit()
      } else {
        error.value = 'Failed to update session'
        return
      }
    } else {
      // Create new session
      const createSuccess = await sessionsStore.createSession(sessionData)
      
      if (createSuccess) {
        success_msg = 'Session recorded successfully!'
        sessionsStore.clearForm()
        participantsStore.clearSearch()
        
        // Add new session to local list if it's for the same date
        if (sessionData.session_date === sessionForm.value.session_date) {
          // Create a mock session object for immediate display
          const newSession: Session = {
            id: Date.now(), // Temporary ID
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
          dateSessionsList.value.unshift(newSession)
        }
      } else {
        error.value = 'Failed to record session'
        return
      }
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

const cancelEdit = () => {
  sessionsStore.cancelEdit()
  participantsStore.clearSearch()
  success.value = 'Edit cancelled - ready for new session'
  setTimeout(() => {
    success.value = ''
  }, 3000)
}

// Watch for date changes to load sessions
watch(() => sessionForm.value.session_date, (newDate) => {
  if (newDate) {
    loadSessionsForDate(newDate)
  }
})

// Lifecycle
onMounted(async () => {
  // Load dashboard data for metrics
  await sessionsStore.loadDashboardData()
  
  // Load sessions for current date
  loadSessionsForDate(sessionForm.value.session_date)
  
  // If editing, populate participant data
  if (editingSession.value) {
    const participant = participantsStore.participants.find(
      p => p.name === editingSession.value?.participant_name
    )
    
    if (participant) {
      participantsStore.selectParticipant(participant)
      selectedParticipant.value = participant
    }
  }
  
  // Set initial duration
  if (selectedTimeRange.value.start && selectedTimeRange.value.end) {
    const start = sessionsStore.timeToMinutes(selectedTimeRange.value.start)
    const end = sessionsStore.timeToMinutes(selectedTimeRange.value.end)
    currentDuration.value = end - start
  }
})
</script>