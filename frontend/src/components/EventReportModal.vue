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

        <!-- Success Screen -->
        <div v-if="showSuccess" class="text-center py-12 px-6">
          <div class="mb-6">
            <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-800 mb-2">Event Report Submitted!</h2>
          <p class="text-gray-600 mb-8">Your event report has been successfully submitted and saved to our system.</p>
          <button
            @click="closeAndSuccess"
            class="btn-primary"
          >
            Done
          </button>
        </div>

        <!-- Form -->
        <div v-else>
          <!-- Header -->
          <div class="bg-gradient-to-r from-primary to-blue-600 text-white p-6 rounded-t-lg">
            <h2 class="text-2xl font-bold">Submit Event Report</h2>
            <p class="text-white/90 text-sm">Share details about events happening in your branch</p>
          </div>

          <!-- Form Content -->
          <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
            <!-- Branch Name (Read-only) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Branch Name *
              </label>
              <input
                type="text"
                :value="form.branch"
                disabled
                class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
              />
              <p class="text-xs text-gray-500 mt-1">Auto-filled from your current branch</p>
            </div>

            <!-- Event Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Event Title *
              </label>
              <input
                v-model="form.eventTitle"
                type="text"
                required
                maxlength="100"
                :class="[
                  'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent',
                  errors.eventTitle ? 'border-red-500 bg-red-50' : 'border-gray-300'
                ]"
                placeholder="e.g., Meditation Workshop"
              />
              <p v-if="errors.eventTitle" class="text-xs text-red-600 mt-1">{{ errors.eventTitle }}</p>
            </div>

            <!-- Event Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Event Description *
              </label>
              <textarea
                v-model="form.eventDescription"
                required
                maxlength="500"
                rows="4"
                :class="[
                  'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent',
                  errors.eventDescription ? 'border-red-500 bg-red-50' : 'border-gray-300'
                ]"
                placeholder="Describe what happened at the event..."
              ></textarea>
              <p class="text-xs text-gray-500 mt-1">{{ form.eventDescription.length }}/500</p>
              <p v-if="errors.eventDescription" class="text-xs text-red-600 mt-1">{{ errors.eventDescription }}</p>
            </div>

            <!-- Event Date and Time -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Event Date *
                </label>
                <input
                  v-model="form.eventDate"
                  type="date"
                  required
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent',
                    errors.eventDate ? 'border-red-500 bg-red-50' : 'border-gray-300'
                  ]"
                />
                <p v-if="errors.eventDate" class="text-xs text-red-600 mt-1">{{ errors.eventDate }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Event Time *
                </label>
                <input
                  v-model="form.eventTime"
                  type="time"
                  required
                  :class="[
                    'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent',
                    errors.eventTime ? 'border-red-500 bg-red-50' : 'border-gray-300'
                  ]"
                />
                <p v-if="errors.eventTime" class="text-xs text-red-600 mt-1">{{ errors.eventTime }}</p>
              </div>
            </div>

            <!-- Participant Details -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Participant Details *
              </label>
              <textarea
                v-model="form.participants"
                required
                maxlength="500"
                rows="3"
                :class="[
                  'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent',
                  errors.participants ? 'border-red-500 bg-red-50' : 'border-gray-300'
                ]"
                placeholder="e.g., 50 participants from different organizations, names and details..."
              ></textarea>
              <p class="text-xs text-gray-500 mt-1">{{ form.participants.length }}/500</p>
              <p v-if="errors.participants" class="text-xs text-red-600 mt-1">{{ errors.participants }}</p>
            </div>

            <!-- Photo Upload -->
            <FileUploadArea
              ref="photoUploadRef"
              label="Photos"
              :max-files="5"
              :max-size-mb="3"
              :accepted-types="['image/jpeg', 'image/png', 'image/jpg']"
              @files-changed="handlePhotosChanged"
            />

            <!-- Video Upload -->
            <FileUploadArea
              ref="videoUploadRef"
              label="Video"
              :max-files="1"
              :max-size-mb="10"
              :accepted-types="['video/mp4', 'video/quicktime']"
              @files-changed="handleVideoChanged"
            />

            <!-- Error Message -->
            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
              {{ errorMessage }}
            </div>

            <!-- Submit and Cancel Buttons -->
            <div class="grid grid-cols-2 gap-3">
              <button
                type="button"
                @click="closeModal"
                :disabled="isSubmitting"
                class="py-3 bg-gray-200 text-gray-800 font-bold rounded-lg hover:bg-gray-300 disabled:opacity-50 transition-colors"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="py-3 bg-gradient-to-r from-primary to-blue-600 text-white font-bold rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity flex items-center justify-center gap-2"
              >
                <svg v-if="isSubmitting" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ isSubmitting ? 'Submitting...' : 'Submit Report' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import { useEventReportStore } from '@/stores/eventReport'
import FileUploadArea from './FileUploadArea.vue'
import type { EventReportForm } from '@/types'

interface Props {
  isOpen: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'success'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const authStore = useAuthStore()
const appStore = useAppStore()
const eventReportStore = useEventReportStore()

const photoUploadRef = ref<InstanceType<typeof FileUploadArea>>()
const videoUploadRef = ref<InstanceType<typeof FileUploadArea>>()

const form = reactive({
  branch: '',
  eventTitle: '',
  eventDescription: '',
  eventDate: '',
  eventTime: '',
  participants: '',
  photos: [] as File[],
  video: null as File | null
})

const errors = reactive({
  eventTitle: '',
  eventDescription: '',
  eventDate: '',
  eventTime: '',
  participants: ''
})

const showSuccess = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref<string | null>(null)

// Initialize branch from auth store
watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      form.branch = authStore.currentBranch?.name || ''
      resetForm()
    }
  }
)

const validateForm = (): boolean => {
  Object.keys(errors).forEach(key => {
    errors[key as keyof typeof errors] = ''
  })

  let isValid = true

  if (!form.eventTitle.trim()) {
    errors.eventTitle = 'Event title is required'
    isValid = false
  } else if (form.eventTitle.trim().length < 3) {
    errors.eventTitle = 'Event title must be at least 3 characters'
    isValid = false
  }

  if (!form.eventDescription.trim()) {
    errors.eventDescription = 'Event description is required'
    isValid = false
  } else if (form.eventDescription.trim().length < 10) {
    errors.eventDescription = 'Description must be at least 10 characters'
    isValid = false
  }

  if (!form.eventDate) {
    errors.eventDate = 'Event date is required'
    isValid = false
  }

  if (!form.eventTime) {
    errors.eventTime = 'Event time is required'
    isValid = false
  }

  if (!form.participants.trim()) {
    errors.participants = 'Participant details are required'
    isValid = false
  }

  return isValid
}

const handlePhotosChanged = (files: File[]) => {
  form.photos = files
}

const handleVideoChanged = (files: File[]) => {
  form.video = files.length > 0 ? files[0] : null
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  isSubmitting.value = true
  errorMessage.value = null

  try {
    const eventForm: EventReportForm = {
      branch: form.branch,
      eventTitle: form.eventTitle,
      eventDescription: form.eventDescription,
      eventDate: form.eventDate,
      eventTime: form.eventTime,
      participants: form.participants,
      photos: form.photos,
      video: form.video
    }

    await eventReportStore.submitEventReport(eventForm)
    showSuccess.value = true
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Failed to submit event report. Please try again.'
    console.error('Error submitting event report:', error)
  } finally {
    isSubmitting.value = false
  }
}

const resetForm = () => {
  form.eventTitle = ''
  form.eventDescription = ''
  form.eventDate = ''
  form.eventTime = ''
  form.participants = ''
  form.photos = []
  form.video = null
  showSuccess.value = false
  errorMessage.value = null
  Object.keys(errors).forEach(key => {
    errors[key as keyof typeof errors] = ''
  })
}

const closeModal = () => {
  resetForm()
  emit('close')
}

const closeAndSuccess = () => {
  closeModal()
  emit('success')
}
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
