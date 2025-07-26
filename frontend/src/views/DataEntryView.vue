<template>
  <BaseLayout>
    <div class="p-4">
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
            <span class="font-medium">
              Editing session from {{ formatTime(editingSession.start_time) }}
            </span>
          </div>
          <button @click="cancelEdit" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
            Cancel and create new session instead
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Participant Input Component -->
          <ParticipantInput
            v-model="sessionForm.participant_name"
            v-model:age="sessionForm.participant_age"
            v-model:gender="sessionForm.participant_gender"
            @participant-selected="handleParticipantSelected"
          />

          <!-- Session Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Session Date
            </label>
            <input
              v-model="sessionForm.session_date"
              type="date"
              required
              class="input-field"
              :max="today"
            >
          </div>

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
    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BaseLayout from '@/components/BaseLayout.vue'
import ParticipantInput from '@/components/ParticipantInput.vue'
import TimeSlotSelector from '@/components/TimeSlotSelector.vue'
import { useSessionsStore } from '@/stores/sessions'
import { useParticipantsStore } from '@/stores/participants'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import type { Participant } from '@/types'

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
      } else {
        error.value = 'Failed to record session'
        return
      }
    }
    
    success.value = success_msg
    appStore.showSuccess(success_msg)
    
    // Clear form and redirect to dashboard after a delay
    setTimeout(() => {
      router.push('/dashboard')
    }, 1500)
    
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

// Lifecycle
onMounted(() => {
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