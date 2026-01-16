<template>
  <section id="join-us" class="section">
    <div class="container mx-auto px-4">
      <h2 class="section-title">{{ $t('whatsapp.title') }}</h2>
      <p class="section-subtitle">
        {{ $t('whatsapp.subtitle') }}
      </p>

      <div class="max-w-md mx-auto content-card">
        <!-- Thank You Message -->
        <Transition name="fade">
          <div v-if="showSuccess" class="text-center py-8">
            <div class="mb-6">
              <svg class="w-20 h-20 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Thank You!</h3>
            <p class="text-gray-600 mb-6">{{ $t('whatsapp.successMessage') }}</p>
            <button
              @click="resetForm"
              class="btn btn-secondary"
            >
              Register Another Person
            </button>
          </div>
        </Transition>

        <!-- Registration Form -->
        <form v-show="!showSuccess" @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <input
              v-model="form.name"
              type="text"
              class="form-input"
              :placeholder="$t('whatsapp.namePlaceholder')"
              required
              :disabled="isSubmitting"
            />
          </div>

          <div>
            <input
              v-model="form.mobile"
              type="tel"
              class="form-input"
              placeholder="WhatsApp Number (10 digits)"
              pattern="[0-9]{10}"
              maxlength="10"
              required
              :disabled="isSubmitting"
            />
            <p class="text-xs text-gray-500 mt-1">Enter your 10-digit WhatsApp number</p>
          </div>

          <div>
            <select v-model="form.centre_id" class="form-select" required :disabled="isSubmitting">
              <option value="">{{ $t('whatsapp.selectCentre') }}</option>
              <option v-for="centre in centres" :key="centre.id" :value="centre.id">
                {{ centre.name }}
              </option>
            </select>
          </div>

          <button
            type="submit"
            class="btn btn-primary btn-block"
            :disabled="isSubmitting"
            :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
          >
            <span v-if="!isSubmitting">{{ $t('whatsapp.submitButton') }}</span>
            <span v-else class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ $t('whatsapp.submitting') }}
            </span>
          </button>

          <!-- Error Message -->
          <Transition name="fade">
            <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-red-700 text-center">{{ error }}</p>
            </div>
          </Transition>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
import type { WhatsAppJoinForm, Centre } from '@/types'

// Declare window.APP_CONFIG type
declare global {
  interface Window {
    APP_CONFIG?: {
      API_BASE_URL: string
    }
  }
}

// Props
interface Props {
  selectedCentreId?: number
}

const props = withDefaults(defineProps<Props>(), {
  selectedCentreId: 0
})

// API Configuration - Use from window.APP_CONFIG or fallback to default
const API_BASE_URL = window.APP_CONFIG?.API_BASE_URL || 'http://192.168.1.13/sn-progress-app/backend/api.php'

const form = reactive<WhatsAppJoinForm>({
  name: '',
  mobile: '',
  centre_id: 0
})

const isSubmitting = ref(false)
const showSuccess = ref(false)
const error = ref('')
const centres = ref<Centre[]>([])
const isLoadingCentres = ref(false)

// Fetch centres from API
const fetchCentres = async () => {
  isLoadingCentres.value = true
  try {
    const response = await fetch(`${API_BASE_URL}/branches`)
    const data = await response.json()

    if (data.success && data.branches) {
      centres.value = data.branches
    } else {
      error.value = 'Failed to load centres. Please refresh the page.'
    }
  } catch (err) {
    console.error('Error fetching centres:', err)
    error.value = 'Failed to load centres. Please refresh the page.'
  } finally {
    isLoadingCentres.value = false
  }
}

const handleSubmit = async () => {
  error.value = ''
  isSubmitting.value = true

  try {
    // Validate mobile number (WhatsApp)
    if (!/^[0-9]{10}$/.test(form.mobile)) {
      error.value = 'Please enter a valid 10-digit WhatsApp number (numbers only)'
      isSubmitting.value = false
      return
    }

    // Validate mobile number starts with 6-9 (Indian mobile numbers)
    if (!/^[6-9]/.test(form.mobile)) {
      error.value = 'WhatsApp number must start with 6, 7, 8, or 9'
      isSubmitting.value = false
      return
    }

    // Validate name contains only letters and spaces
    if (!/^[A-Za-z\s]+$/.test(form.name.trim())) {
      error.value = 'Name can only contain letters and spaces'
      isSubmitting.value = false
      return
    }

    // Call API
    const response = await fetch(`${API_BASE_URL}/event-register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    })

    const data = await response.json()

    if (data.success) {
      // Show success message
      showSuccess.value = true

      // Reset form
      form.name = ''
      form.mobile = ''
      form.centre_id = 0
    } else {
      error.value = data.message || 'Registration failed. Please try again.'
    }

  } catch (err) {
    console.error('Registration error:', err)
    error.value = 'An error occurred. Please check your connection and try again.'
  } finally {
    isSubmitting.value = false
  }
}

const resetForm = () => {
  showSuccess.value = false
  error.value = ''
  form.name = ''
  form.mobile = ''
  form.centre_id = 0
}

// Watch for selectedCentreId changes to pre-fill the form
watch(() => props.selectedCentreId, (newId) => {
  if (newId && newId > 0) {
    form.centre_id = newId
    // Reset success state to show form again
    showSuccess.value = false
    error.value = ''
  }
})

// Fetch centres when component mounts
onMounted(() => {
  fetchCentres()
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
