<template>
  <BaseLayout>
    <div class="p-4 space-y-4">
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-primary flex items-center">
            <span class="mr-2">🧘‍♂️</span> Yogis
          </h2>
          <button
            @click="addParticipant"
            class="bg-primary text-white px-3 py-2 rounded-lg font-medium hover:bg-primary-dark transition-colors flex items-center text-sm"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Yogi
          </button>
        </div>
        
        <!-- Search and Filter -->
        <div class="mb-6">
          <div class="relative">
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Search yogis..."
              class="w-full px-3 py-2 pl-9 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            >
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Participants List -->
        <div v-if="isLoading" class="space-y-3">
          <!-- Loading skeleton -->
          <div v-for="i in 5" :key="i" class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl animate-pulse">
            <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
            <div class="flex-1">
              <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <div v-else-if="displayedParticipants.length" class="space-y-3">
          <div
            v-for="participant in displayedParticipants"
            :key="participant.id"
            class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors"
          >
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-primary/10 to-indigo-100 rounded-full flex items-center justify-center">
                <span class="text-primary font-semibold text-lg">
                  {{ participant.name.charAt(0).toUpperCase() }}
                </span>
              </div>
              <div>
                <div class="font-medium text-gray-900">{{ participant.name }}</div>
                <div class="text-sm text-gray-600">
                  <span v-if="participant.age">{{ participant.age }} years</span>
                  <span v-if="participant.age && participant.gender"> • </span>
                  <span v-if="participant.gender">{{ participant.gender }}</span>
                  <span v-if="!participant.age && !participant.gender" class="text-gray-400">
                    No additional details
                  </span>
                </div>
                <div class="text-xs text-primary font-medium mt-1">
                  {{ getParticipantStats(participant).sessionCount }} sessions • 
                  {{ getParticipantStats(participant).totalHours }}h total
                </div>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="editParticipant(participant)"
                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg touch-target"
                title="Edit participant"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
              </button>
              <button
                @click="confirmDeleteParticipant(participant)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-lg touch-target"
                title="Delete participant"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Load more button -->
          <div v-if="hasMoreParticipants" class="text-center pt-4">
            <button
              @click="loadMore"
              class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors text-sm"
            >
              Load More
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="text-6xl mb-4">🧘‍♂️</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ searchQuery ? 'No yogis found' : 'No yogis yet' }}
          </h3>
          <p class="text-gray-500 mb-6">
            {{ searchQuery 
              ? `No yogis match "${searchQuery}"` 
              : 'Yogis will appear here as you record sessions.' 
            }}
          </p>
          <div class="space-x-3">
            <button v-if="searchQuery" @click="clearSearch" class="btn-secondary">
              Clear Search
            </button>
            <router-link to="/data-entry" class="btn-primary inline-flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Record Session
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Participant Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="closeModal"
    >
      <div
        class="bg-white rounded-2xl p-6 w-full max-w-md"
        @click.stop
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ isEditing ? 'Edit Yogi' : 'Add New Yogi' }}
          </h3>
          <button
            @click="closeModal"
            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveParticipant" class="space-y-4">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
            <input
              v-model="participantForm.name"
              @input="handleNameInput"
              type="text"
              placeholder="Enter yogi name"
              pattern="[A-Za-z\s]+"
              title="Please enter only letters and spaces"
              :class="[
                'w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary',
                !isValidParticipantName && participantForm.name ? 'border-red-300' : ''
              ]"
              required
            >
            <div v-if="!isValidParticipantName && participantForm.name" class="mt-1 text-sm text-red-600">
              Name can only contain letters and spaces
            </div>
          </div>

          <!-- Age -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
            <input
              v-model.number="participantForm.age"
              type="number"
              min="1"
              max="120"
              placeholder="Enter age (optional)"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            >
          </div>

          <!-- Gender -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
            <select 
              v-model="participantForm.gender" 
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            >
              <option value="">Select gender (optional)</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <!-- Buttons -->
          <div class="flex space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 btn-secondary"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSaving || !participantForm.name.trim() || !isValidParticipantName"
              class="flex-1 btn-primary"
            >
              <span v-if="isSaving">{{ isEditing ? 'Updating...' : 'Creating...' }}</span>
              <span v-else>{{ isEditing ? 'Save Changes' : 'Add Yogi' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="deletingParticipant"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="closeDeleteModal"
    >
      <div
        class="bg-white rounded-2xl p-6 w-full max-w-md"
        @click.stop
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-red-600">Delete Participant</h3>
          <button
            @click="closeDeleteModal"
            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="mb-6">
          <div class="flex items-center space-x-3 mb-3">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ deletingParticipant.name }}</p>
              <p class="text-sm text-gray-600">
                <span v-if="deletingParticipant.age">{{ deletingParticipant.age }} years</span>
                <span v-if="deletingParticipant.age && deletingParticipant.gender"> • </span>
                <span v-if="deletingParticipant.gender">{{ deletingParticipant.gender }}</span>
              </p>
            </div>
          </div>
          <p class="text-sm text-gray-700">
            Are you sure you want to delete this participant? This action cannot be undone.
          </p>
          <p class="text-xs text-red-600 mt-2">
            <strong>Warning:</strong> All meditation sessions for this participant will also be deleted.
          </p>
        </div>

        <div class="flex space-x-3">
          <button
            type="button"
            @click="closeDeleteModal"
            class="flex-1 btn-secondary"
          >
            Cancel
          </button>
          <button
            @click="deleteParticipant"
            :disabled="isDeleting"
            class="flex-1 bg-red-600 text-white py-2 px-4 rounded-xl font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="isDeleting">Deleting...</span>
            <span v-else>Delete Participant</span>
          </button>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import BaseLayout from '@/components/BaseLayout.vue'
import { useParticipantsStore } from '@/stores/participants'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import type { Participant } from '@/types'

const participantsStore = useParticipantsStore()
const authStore = useAuthStore()
const appStore = useAppStore()

// Local state
const searchQuery = ref('')
const showModal = ref(false)
const isEditing = ref(false)
const editingParticipantId = ref<number | null>(null)
const participantForm = ref({
  name: '',
  age: null as number | null,
  gender: '' as string
})
const deletingParticipant = ref<Participant | null>(null)
const isSaving = ref(false)
const isDeleting = ref(false)
const displayLimit = ref(2000)

// Computed properties
const participants = computed(() => participantsStore.participants)
const isLoading = computed(() => participantsStore.isLoading)
const isValidParticipantName = computed(() => /^[A-Za-z\s]*$/.test(participantForm.value.name))

const filteredParticipants = computed(() => {
  if (!searchQuery.value.trim()) {
    return participants.value
  }
  
  const query = searchQuery.value.toLowerCase()
  return participants.value.filter(participant =>
    participant.name.toLowerCase().includes(query)
  )
})

const displayedParticipants = computed(() => {
  return filteredParticipants.value.slice(0, displayLimit.value)
})

const hasMoreParticipants = computed(() => {
  return filteredParticipants.value.length > displayLimit.value
})

const getParticipantStats = (participant: any) => {
  return {
    sessionCount: participant.session_count || 0,
    totalHours: participant.total_hours?.toFixed(1) || '0.0'
  }
}

// Methods
const handleSearch = async () => {
  const branchId = authStore.currentBranch?.id
  if (!branchId) return
  
  if (searchQuery.value.length >= 2) {
    await participantsStore.searchParticipants(searchQuery.value, branchId)
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  participantsStore.clearSearch()
}

const handleNameInput = () => {
  // Filter out numbers and special characters, keep only letters and spaces
  const filteredValue = participantForm.value.name.replace(/[^A-Za-z\s]/g, '')
  if (filteredValue !== participantForm.value.name) {
    participantForm.value.name = filteredValue
  }
}

const loadMore = () => {
  displayLimit.value += 300
}

const addParticipant = () => {
  isEditing.value = false
  editingParticipantId.value = null
  participantForm.value = {
    name: '',
    age: null,
    gender: ''
  }
  showModal.value = true
}

const editParticipant = (participant: Participant) => {
  isEditing.value = true
  editingParticipantId.value = participant.id
  participantForm.value = {
    name: participant.name,
    age: participant.age,
    gender: participant.gender || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  isEditing.value = false
  editingParticipantId.value = null
  participantForm.value = {
    name: '',
    age: null,
    gender: ''
  }
}

const confirmDeleteParticipant = (participant: Participant) => {
  deletingParticipant.value = { ...participant }
}

const closeDeleteModal = () => {
  deletingParticipant.value = null
}

const saveParticipant = async () => {
  const branchId = authStore.currentBranch?.id
  if (!branchId) {
    appStore.showError('No branch selected')
    return
  }

  if (!participantForm.value.name.trim()) {
    appStore.showError('Participant name is required')
    return
  }

  if (!isValidParticipantName.value) {
    appStore.showError('Name can only contain letters and spaces')
    return
  }
  
  isSaving.value = true
  
  try {
    let success = false
    
    if (isEditing.value && editingParticipantId.value) {
      // Update existing participant
      const updateData = {
        name: participantForm.value.name.trim(),
        age: participantForm.value.age,
        gender: participantForm.value.gender || null
      }
      console.log('Updating participant with data:', updateData)
      
      success = await participantsStore.updateParticipant(
        editingParticipantId.value,
        updateData
      )
      
      if (success) {
        appStore.showSuccess('Participant updated successfully!')
      } else {
        appStore.showError('Failed to update participant')
      }
    } else {
      // Create new participant  
      console.log('participantForm.value before processing:', participantForm.value)
      
      const formData = {
        name: participantForm.value.name.trim(),
        age: participantForm.value.age || null,
        gender: participantForm.value.gender || null,
        branch_id: branchId
      }
      
      // Debug logging
      console.log('Sending participant data:', formData)
      console.log('Form data name field type:', typeof formData.name)
      console.log('Form data name field value:', formData.name)
      
      const response = await participantsStore.createParticipant(formData)
      
      if (response.success) {
        appStore.showSuccess('Participant added successfully!')
        success = true
      } else {
        appStore.showError(response.message || 'Failed to add participant')
      }
    }
    
    if (success) {
      closeModal()
      console.log('Participants before refresh:', participantsStore.participants.length)
      await loadData() // Refresh the list
      console.log('Participants after refresh:', participantsStore.participants.length)
    }
  } catch (error) {
    console.error('Save participant error:', error)
    appStore.showError('An error occurred while saving the participant')
  } finally {
    isSaving.value = false
  }
}

const deleteParticipant = async () => {
  if (!deletingParticipant.value) return
  
  isDeleting.value = true
  
  try {
    const success = await participantsStore.deleteParticipant(deletingParticipant.value.id)
    if (success) {
      appStore.showSuccess('Participant deleted successfully!')
      closeDeleteModal()
      await loadData()
    } else {
      appStore.showError('Failed to delete participant')
    }
  } catch (error) {
    console.error('Delete participant error:', error)
    appStore.showError('An error occurred while deleting the participant')
  } finally {
    isDeleting.value = false
  }
}

const loadData = async () => {
  const branchId = authStore.currentBranch?.id
  if (!branchId) return
  
  try {
    await participantsStore.loadParticipants(branchId)
  } catch (error) {
    console.error('Load participants error:', error)
    appStore.showError('Failed to load participants')
  }
}

// Lifecycle
onMounted(() => {
  loadData()
})

// Watch for branch changes
watch(() => authStore.currentBranch, (newBranch, oldBranch) => {
  if (newBranch && newBranch.id !== oldBranch?.id) {
    loadData()
  }
}, { immediate: false })
</script>