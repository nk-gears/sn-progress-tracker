<template>
  <BaseLayout>
    <div class="p-4 space-y-4">
      <div class="card p-6">
        <h2 class="text-xl font-bold text-primary mb-6 flex items-center">
          <span class="mr-2">👥</span> Participants
        </h2>
        
        <!-- Search and Filter -->
        <div class="mb-6">
          <div class="relative">
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Search participants..."
              class="input-field pl-10"
            >
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              </div>
            </div>
            <button
              @click="editParticipant(participant)"
              class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg touch-target"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
          </div>

          <!-- Load more button -->
          <div v-if="hasMoreParticipants" class="text-center pt-4">
            <button
              @click="loadMore"
              class="btn-secondary"
            >
              Load More
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="text-6xl mb-4">👥</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ searchQuery ? 'No participants found' : 'No participants yet' }}
          </h3>
          <p class="text-gray-500 mb-6">
            {{ searchQuery 
              ? `No participants match "${searchQuery}"` 
              : 'Participants will appear here as you record sessions.' 
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

    <!-- Edit Participant Modal -->
    <div
      v-if="editingParticipant"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="closeEditModal"
    >
      <div
        class="bg-white rounded-2xl p-6 w-full max-w-md"
        @click.stop
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Edit Participant</h3>
          <button
            @click="closeEditModal"
            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveParticipant" class="space-y-4">
          <!-- Name (readonly) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
            <input
              :value="editingParticipant.name"
              type="text"
              readonly
              class="input-field bg-gray-100 cursor-not-allowed"
            >
          </div>

          <!-- Age -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
            <input
              v-model.number="editForm.age"
              type="number"
              min="1"
              max="120"
              placeholder="Enter age"
              class="input-field"
            >
          </div>

          <!-- Gender -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
            <select v-model="editForm.gender" class="input-field">
              <option value="">Select gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <!-- Buttons -->
          <div class="flex space-x-3 pt-4">
            <button
              type="button"
              @click="closeEditModal"
              class="flex-1 btn-secondary"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSaving"
              class="flex-1 btn-primary"
            >
              <span v-if="isSaving">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
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
const editingParticipant = ref<Participant | null>(null)
const editForm = ref({
  age: null as number | null,
  gender: null as string | null
})
const isSaving = ref(false)
const displayLimit = ref(20)

// Computed properties
const participants = computed(() => participantsStore.participants)
const isLoading = computed(() => participantsStore.isLoading)

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

const loadMore = () => {
  displayLimit.value += 20
}

const editParticipant = (participant: Participant) => {
  editingParticipant.value = { ...participant }
  editForm.value = {
    age: participant.age,
    gender: participant.gender
  }
}

const closeEditModal = () => {
  editingParticipant.value = null
  editForm.value = {
    age: null,
    gender: null
  }
}

const saveParticipant = async () => {
  if (!editingParticipant.value) return
  
  isSaving.value = true
  
  try {
    const success = await participantsStore.updateParticipant(
      editingParticipant.value.id,
      {
        age: editForm.value.age,
        gender: editForm.value.gender
      }
    )
    
    if (success) {
      appStore.showSuccess('Participant updated successfully!')
      closeEditModal()
    } else {
      appStore.showError('Failed to update participant')
    }
  } catch (error) {
    console.error('Update participant error:', error)
    appStore.showError('An error occurred while updating the participant')
  } finally {
    isSaving.value = false
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
</script>