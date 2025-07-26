<template>
  <div class="space-y-4">
    <!-- Participant Name Input -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
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
          class="input-field"
          :class="{ 'border-green-300': selectedParticipant && !isNewParticipant }"
        >
        
        <!-- Participant status indicator -->
        <div v-if="localParticipantName.length > 1" class="absolute right-3 top-1/2 transform -translate-y-1/2">
          <div v-if="selectedParticipant && !isNewParticipant" class="text-green-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div v-else-if="isNewParticipant" class="text-blue-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
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
              <span v-if="participant.age">{{ participant.age }} years</span>
              <span v-if="participant.age && participant.gender"> • </span>
              <span v-if="participant.gender">{{ participant.gender }}</span>
            </div>
          </button>
        </div>
      </div>
      
      <!-- Status message -->
      <div v-if="localParticipantName.length > 1" class="mt-2 text-sm">
        <div v-if="selectedParticipant && !isNewParticipant" class="text-green-700 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
          Existing participant selected
        </div>
        <div v-else-if="isNewParticipant" class="text-blue-700 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
          </svg>
          New participant - please add details below
        </div>
      </div>
    </div>

    <!-- Age and Gender - Show conditionally -->
    <div v-if="showAgeGenderFields" class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Age
          <span v-if="isNewParticipant" class="text-green-600 text-xs">(New participant)</span>
          <span v-else-if="selectedParticipant" class="text-blue-600 text-xs">(Can edit)</span>
        </label>
        <input
          v-model.number="localAge"
          @input="handleAgeChange"
          type="number"
          min="1"
          max="120"
          placeholder="Age"
          :disabled="selectedParticipant && selectedParticipant.age"
          class="input-field"
          :class="{
            'bg-gray-100 cursor-not-allowed': selectedParticipant && selectedParticipant.age
          }"
        >
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Gender
          <span v-if="isNewParticipant" class="text-green-600 text-xs">(New participant)</span>
          <span v-else-if="selectedParticipant" class="text-blue-600 text-xs">(Can edit)</span>
        </label>
        <select
          v-model="localGender"
          @change="handleGenderChange"
          :disabled="selectedParticipant && selectedParticipant.gender"
          class="input-field"
          :class="{
            'bg-gray-100 cursor-not-allowed': selectedParticipant && selectedParticipant.gender
          }"
        >
          <option value="">Select</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
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
  age: number | null
  gender: string | null
}

interface Emits {
  (e: 'update:modelValue', value: string): void
  (e: 'update:age', value: number | null): void
  (e: 'update:gender', value: string | null): void
  (e: 'participant-selected', participant: Participant | null): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const participantsStore = useParticipantsStore()
const authStore = useAuthStore()

// Local reactive state
const localParticipantName = ref(props.modelValue)
const localAge = ref(props.age)
const localGender = ref(props.gender)
const showSuggestions = ref(false)
const inputFocused = ref(false)

// Computed properties
const participantSuggestions = computed(() => participantsStore.participantSuggestions)
const selectedParticipant = computed(() => participantsStore.selectedParticipant)
const isNewParticipant = computed(() => participantsStore.isNewParticipant)

const showAgeGenderFields = computed(() => {
  return localParticipantName.value.length > 1 && (isNewParticipant.value || selectedParticipant.value)
})

// Methods
const handleInput = async () => {
  emit('update:modelValue', localParticipantName.value)
  
  const branchId = authStore.currentBranch?.id
  if (!branchId) return
  
  await participantsStore.searchParticipants(localParticipantName.value, branchId)
  showSuggestions.value = inputFocused.value && participantSuggestions.value.length > 0
  
  // Update form data based on search results
  if (selectedParticipant.value) {
    localAge.value = selectedParticipant.value.age
    localGender.value = selectedParticipant.value.gender
    emit('update:age', selectedParticipant.value.age)
    emit('update:gender', selectedParticipant.value.gender)
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
  localAge.value = participant.age
  localGender.value = participant.gender
  showSuggestions.value = false
  
  emit('update:modelValue', participant.name)
  emit('update:age', participant.age)
  emit('update:gender', participant.gender)
  emit('participant-selected', participant)
}

const handleAgeChange = () => {
  emit('update:age', localAge.value)
}

const handleGenderChange = () => {
  emit('update:gender', localGender.value)
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

watch(() => props.age, (newValue) => {
  if (newValue !== localAge.value) {
    localAge.value = newValue
  }
})

watch(() => props.gender, (newValue) => {
  if (newValue !== localGender.value) {
    localGender.value = newValue
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