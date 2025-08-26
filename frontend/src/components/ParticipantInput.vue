<template>
  <div class="space-y-3">
    <!-- Participant Name Input -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Participant Name
      </label>
      <div class="relative">
        <input
          v-model="localParticipantName"
          @input="handleInput"
          @focus="handleFocus"
          @blur="handleBlur"
          type="text"
          required
          placeholder="Enter participant name"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200"
          :class="{ 
            'border-green-300': selectedParticipant,
            'border-red-300': localParticipantName.length > 1 && !selectedParticipant
          }"
        >
        
        <!-- Participant status indicator -->
        <div v-if="localParticipantName.length > 1" class="absolute right-3 top-1/2 transform -translate-y-1/2">
          <div v-if="selectedParticipant" class="text-green-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div v-else class="text-red-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        
        <!-- Autocomplete dropdown -->
        <div
          v-if="showSuggestions && participantSuggestions.length"
          class="absolute z-10 w-full bg-white border border-gray-300 rounded-xl mt-2 max-h-48 overflow-y-auto shadow-lg"
        >
          <button
            v-for="participant in participantSuggestions"
            :key="participant.id"
            type="button"
            @click="selectParticipant(participant)"
            class="w-full text-left px-4 py-3 hover:bg-gray-100 focus:bg-gray-100 touch-target border-b last:border-b-0"
          >
            <div class="font-medium">{{ participant.name }}</div>
            <div v-if="participant.age || participant.gender" class="text-sm text-gray-500">
              <span v-if="participant.age"> {{ participant.age }} years</span>
              <!-- <span v-if="participant.age && participant.gender"> • </span>
              <span v-if="participant.gender">{{ participant.gender }}</span> -->
            </div>
          </button>
        </div>
      </div>
      
      <!-- Status message -->
      <div v-if="localParticipantName.length > 1" class="mt-1 text-sm">
        <div v-if="selectedParticipant" class="text-green-700 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
          Participant selected: {{ selectedParticipant.name }}
          <span v-if="selectedParticipant.age" class="ml-1">({{ selectedParticipant.age }} years)</span>
        </div>
        <div v-else class="text-red-700 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
          Please select an existing participant from the list
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useParticipantsStore } from '@/stores/participants'
import { useAuthStore } from '@/stores/auth'
import type { Participant } from '@/types'

// Props and emits
interface Props {
  modelValue: string
}

interface Emits {
  (e: 'update:modelValue', value: string): void
  (e: 'participant-selected', participant: Participant | null): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const participantsStore = useParticipantsStore()
const authStore = useAuthStore()

// Local reactive state
const localParticipantName = ref(props.modelValue)
const showSuggestions = ref(false)
const inputFocused = ref(false)

// Computed properties
const participantSuggestions = computed(() => participantsStore.participantSuggestions)
const selectedParticipant = computed(() => participantsStore.selectedParticipant)

// Methods
const handleInput = async () => {
  emit('update:modelValue', localParticipantName.value)
  
  const branchId = authStore.currentBranch?.id
  if (!branchId) return
  
  await participantsStore.searchParticipants(localParticipantName.value, branchId)
  showSuggestions.value = inputFocused.value && participantSuggestions.value.length > 0
  
  // Only emit participant if there's an exact match
  if (selectedParticipant.value) {
    emit('participant-selected', selectedParticipant.value)
  } else {
    emit('participant-selected', null)
  }
}

const handleFocus = () => {
  inputFocused.value = true
  if (participantSuggestions.value.length > 0) {
    showSuggestions.value = true
  }
}

const handleBlur = () => {
  inputFocused.value = false
  // Delay hiding suggestions to allow for clicks
  setTimeout(() => {
    showSuggestions.value = false
  }, 200)
}

const selectParticipant = (participant: Participant) => {
  participantsStore.selectParticipant(participant)
  localParticipantName.value = participant.name
  showSuggestions.value = false
  
  emit('update:modelValue', participant.name)
  emit('participant-selected', participant)
}


const handleClickOutside = (event: Event) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) {
    showSuggestions.value = false
  }
}

// Watch for prop changes
watch(() => props.modelValue, (newValue) => {
  if (newValue !== localParticipantName.value) {
    localParticipantName.value = newValue
  }
})

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  
  // Load participants for the current branch
  const branchId = authStore.currentBranch?.id
  if (branchId) {
    participantsStore.loadParticipants(branchId)
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>