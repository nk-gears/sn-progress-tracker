import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Participant } from '@/types'
import { apiService } from '@/services/apiService'
import { useAuthStore } from './auth'

export const useParticipantsStore = defineStore('participants', () => {
  // State
  const participants = ref<Participant[]>([])
  const isLoading = ref(false)
  const searchQuery = ref('')
  const selectedParticipant = ref<Participant | null>(null)
  
  // Getters
  const filteredParticipants = computed(() => {
    if (!searchQuery.value || searchQuery.value.length < 2) {
      return []
    }
    
    const query = searchQuery.value.toLowerCase()
    return participants.value.filter(participant =>
      participant.name.toLowerCase().includes(query)
    )
  })
  
  const isNewParticipant = computed(() => {
    if (!searchQuery.value) return false
    
    const exactMatch = participants.value.find(p =>
      p.name.toLowerCase() === searchQuery.value.toLowerCase()
    )
    
    return !exactMatch
  })
  
  const participantSuggestions = computed(() => {
    return filteredParticipants.value.slice(0, 5) // Limit to 5 suggestions
  })
  
  // Actions
  const loadParticipants = async (branchId: number) => {
    if (isLoading.value) return
    
    isLoading.value = true
    try {
      console.log('Loading participants for branch:', branchId)
      const response = await apiService.participants.getByBranch(branchId)
      console.log('API response:', response)
      if (response.success) {
        participants.value = response.participants || []
        console.log('Loaded participants count:', participants.value.length)
      } else {
        console.error('Failed to load participants:', response.message)
      }
    } catch (error) {
      console.error('Error loading participants:', error)
    } finally {
      isLoading.value = false
    }
  }
  
  const searchParticipants = async (query: string, branchId: number) => {
    searchQuery.value = query
    
    if (query.length < 2) {
      selectedParticipant.value = null
      return
    }
    
    // Check for exact match
    const exactMatch = participants.value.find(p =>
      p.name.toLowerCase() === query.toLowerCase()
    )
    
    if (exactMatch) {
      selectedParticipant.value = exactMatch
    } else {
      selectedParticipant.value = null
    }
    
    // If we don't have enough participants loaded, search via API
    if (participants.value.length < 50) {
      try {
        const response = await apiService.participants.search(query, branchId)
        if (response.success && response.participants) {
          // Merge with existing participants, avoiding duplicates
          const existingIds = participants.value.map(p => p.id)
          const newParticipants = response.participants.filter(p => !existingIds.includes(p.id))
          participants.value = [...participants.value, ...newParticipants]
        }
      } catch (error) {
        console.error('Error searching participants:', error)
      }
    }
  }
  
  const selectParticipant = (participant: Participant) => {
    selectedParticipant.value = participant
    searchQuery.value = participant.name
  }
  
  const createParticipant = async (participantData: any): Promise<any> => {
    try {
      const response = await apiService.participants.create(participantData)
      
      if (response.success && response.participant) {
        // Add to local participants list
        participants.value.unshift(response.participant)
        selectedParticipant.value = response.participant
      }
      
      return response
    } catch (error) {
      console.error('Error creating participant:', error)
      return { success: false, message: 'Failed to create participant' }
    }
  }
  
  const updateParticipant = async (id: number, data: Partial<Participant>): Promise<boolean> => {
    try {
      const response = await apiService.participants.update(id, data)
      
      if (response.success) {
        // Update local participant
        const index = participants.value.findIndex(p => p.id === id)
        if (index >= 0) {
          participants.value[index] = { ...participants.value[index], ...data }
        }
        
        // Update selected participant if it's the same one
        if (selectedParticipant.value?.id === id) {
          selectedParticipant.value = { ...selectedParticipant.value, ...data }
        }
        
        return true
      }
    } catch (error) {
      console.error('Error updating participant:', error)
    }
    
    return false
  }
  
  const deleteParticipant = async (id: number): Promise<boolean> => {
    try {
      const response = await apiService.participants.delete(id)
      
      if (response.success) {
        // Remove from local participants list
        const index = participants.value.findIndex(p => p.id === id)
        if (index >= 0) {
          participants.value.splice(index, 1)
        }
        
        // Clear selected participant if it's the deleted one
        if (selectedParticipant.value?.id === id) {
          selectedParticipant.value = null
        }
        
        return true
      }
    } catch (error) {
      console.error('Error deleting participant:', error)
    }
    
    return false
  }
  
  const findOrCreateParticipant = async (name: string, age?: number, gender?: string): Promise<Participant | null> => {
    // First check if participant already exists
    const existing = participants.value.find(p => 
      p.name.toLowerCase() === name.toLowerCase()
    )
    
    if (existing) {
      // Update with new info if provided and not already set
      if ((age && !existing.age) || (gender && !existing.gender)) {
        const updateData: Partial<Participant> = {}
        if (age && !existing.age) updateData.age = age
        if (gender && !existing.gender) updateData.gender = gender
        
        await updateParticipant(existing.id, updateData)
      }
      
      return existing
    }
    
    // Create new participant
    const authStore = useAuthStore()
    const branchId = authStore.currentBranch?.id
    
    if (!branchId) return null
    
    const response = await createParticipant({
      name,
      age: age || null,
      gender: gender || null,
      branch_id: branchId
    })
    
    return response.success ? response.participant : null
  }
  
  const clearSearch = () => {
    searchQuery.value = ''
    selectedParticipant.value = null
  }
  
  const resetStore = () => {
    participants.value = []
    isLoading.value = false
    searchQuery.value = ''
    selectedParticipant.value = null
  }
  
  return {
    // State
    participants,
    isLoading,
    searchQuery,
    selectedParticipant,
    
    // Getters
    filteredParticipants,
    isNewParticipant,
    participantSuggestions,
    
    // Actions
    loadParticipants,
    searchParticipants,
    selectParticipant,
    createParticipant,
    updateParticipant,
    deleteParticipant,
    findOrCreateParticipant,
    clearSearch,
    resetStore
  }
})