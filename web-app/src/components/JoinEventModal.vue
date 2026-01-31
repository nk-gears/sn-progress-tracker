<template>
  <!-- Modal Backdrop -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4">
        <!-- Modal Content -->
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full z-50 animate-scale-in relative">
          <!-- Close Button (X) - Top Right Corner -->
          <button
            v-if="!showSuccess"
            @click="closeModal"
            class="absolute top-3 right-3 z-10 p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>

          <!-- Header -->
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-6 rounded-t-lg">
            <!-- Logo -->
            <div class="flex justify-center mb-3">
              <img src="/images/sn-logo.png" alt="Shivanum Naanum Logo" class="h-12 w-auto">
            </div>
            <h2 class="text-2xl font-bold text-center">{{ $t('joinEvent.title') || 'Join the Event' }}</h2>
            <p class="text-purple-100 text-sm mt-1 text-center">{{ centre?.name || 'Event Registration' }}</p>
          </div>

          <!-- Content -->
          <div class="p-6 space-y-4">
            <!-- Success Message -->
            <Transition name="fade">
              <div v-if="showSuccess" class="text-center py-8">
                <div class="mb-4">
                  <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $t('joinEvent.success') || 'Thank You!' }}</h3>
                <p class="text-gray-600 text-sm mb-6">{{ $t('joinEvent.successMessage') || 'You have been registered for the event.' }}</p>

                <!-- Join WhatsApp Group Button -->
                <a
                  v-if="whatsappLink"
                  :href="whatsappLink"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn w-full mb-3 flex items-center justify-center gap-2 text-white font-semibold rounded-lg py-2 px-4 transition hover:opacity-90"
                  style="background-color: #25BD4B;"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                  </svg>
                  {{ $t('joinEvent.joinGroup') || 'Join Our WhatsApp Group' }}
                </a>

                <button
                  @click="closeModal"
                  class="btn btn-secondary w-full"
                >
                  {{ $t('joinEvent.close') || 'Close' }}
                </button>
              </div>
            </Transition>

            <!-- Registration Form -->
            <form v-show="!showSuccess" @submit.prevent="handleSubmit" class="space-y-4">
              <!-- Name Field -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('joinEvent.nameLabel') || 'Name' }}</label>
                <div class="relative">
                  <input
                    v-model="form.name"
                    type="text"
                    class="form-input w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent transition"
                    :class="{
                      'border-gray-300': !form.name || errors.name,
                      'border-green-500 bg-green-50': form.name && !errors.name,
                      'border-red-500 bg-red-50': errors.name
                    }"
                    :placeholder="$t('joinEvent.namePlaceholder') || 'Enter your name'"
                    required
                    :disabled="isSubmitting"
                  />
                  <!-- Valid indicator -->
                  <div v-if="form.name && !errors.name" class="absolute right-3 top-2.5">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <!-- Invalid indicator -->
                  <div v-else-if="errors.name" class="absolute right-3 top-2.5">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
                <p v-if="errors.name" class="text-red-500 text-xs mt-1.5">{{ errors.name }}</p>
                <p v-else-if="form.name && !errors.name" class="text-green-600 text-xs mt-1.5">✓ Name looks good</p>
              </div>

              <!-- Mobile Number Field -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('joinEvent.mobileLabel') || 'WhatsApp Number' }}</label>
                <div class="relative">
                  <input
                    v-model="form.mobile"
                    type="tel"
                    class="form-input w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent transition"
                    :class="{
                      'border-gray-300': !form.mobile || errors.mobile,
                      'border-green-500 bg-green-50': form.mobile && !errors.mobile,
                      'border-red-500 bg-red-50': errors.mobile
                    }"
                    :placeholder="$t('joinEvent.mobilePlaceholder') || '10-digit number'"
                    pattern="[0-9]*"
                    maxlength="10"
                    inputmode="numeric"
                    required
                    :disabled="isSubmitting"
                  />
                  <!-- Valid indicator -->
                  <div v-if="form.mobile && !errors.mobile" class="absolute right-3 top-2.5">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <!-- Invalid indicator -->
                  <div v-else-if="errors.mobile" class="absolute right-3 top-2.5">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
                <p class="text-xs text-gray-500 mt-1.5">{{ $t('joinEvent.mobileHelp') || '10-digit WhatsApp number only' }}</p>
                <p v-if="errors.mobile" class="text-red-500 text-xs mt-1">{{ errors.mobile }}</p>
                <p v-else-if="form.mobile && !errors.mobile" class="text-green-600 text-xs mt-1">✓ Mobile number looks good</p>
              </div>

              <!-- Number of People Joining -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('joinEvent.numberOfPeopleLabel') || 'Number of People Joining' }}</label>
                <div class="flex items-center gap-3">
                  <button
                    type="button"
                    @click="form.numberOfPeople = Math.max(1, form.numberOfPeople - 1)"
                    class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition"
                  >
                    −
                  </button>
                  <input
                    v-model.number="form.numberOfPeople"
                    type="number"
                    min="1"
                    max="50"
                    class="form-input flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent text-center"
                    :disabled="isSubmitting"
                  />
                  <button
                    type="button"
                    @click="form.numberOfPeople = Math.min(50, form.numberOfPeople + 1)"
                    class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition"
                  >
                    +
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-1.5">{{ $t('joinEvent.numberOfPeopleHelp') || 'Select how many people will be joining' }}</p>
              </div>

              <!-- Error Message -->
              <Transition name="fade">
                <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                  <p class="text-red-700 text-sm text-center">{{ error }}</p>
                </div>
              </Transition>

              <!-- Submit Button -->
              <button
                type="submit"
                class="btn btn-primary w-full py-3 font-semibold transition"
                :disabled="isSubmitting"
                :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
              >
                <span v-if="!isSubmitting" class="flex items-center justify-center gap-2">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12a4 4 0 100-8 4 4 0 000 8zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                  </svg>
                  {{ $t('joinEvent.joinButton') || 'Join the Event' }}
                </span>
                <span v-else class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ $t('joinEvent.joining') || 'Joining...' }}
                </span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { getApiUrl } from '@/config'
import type { Centre } from '@/types'
import { useCampaign } from '@/composables/useCampaign'

interface Props {
  isOpen: boolean
  centre?: Centre
}

interface Emits {
  (e: 'close'): void
  (e: 'success'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const form = reactive({
  name: '',
  mobile: '',
  numberOfPeople: 1
})

const errors = reactive({
  name: '',
  mobile: ''
})

const isSubmitting = ref(false)
const error = ref('')
const showSuccess = ref(false)
const whatsappLink = ref<string>('')

// Reset form when modal opens
const resetForm = () => {
  form.name = ''
  form.mobile = ''
  form.numberOfPeople = 1
  error.value = ''
  errors.name = ''
  errors.mobile = ''
  showSuccess.value = false
  isSubmitting.value = false
}

// Real-time validation for name
const validateName = (name: string): string => {
  const trimmed = name.trim()

  if (!trimmed) {
    return ''
  }

  if (trimmed.length < 2) {
    return 'Name must be at least 2 characters'
  }

  if (!/^[A-Za-z\s]+$/.test(trimmed)) {
    return 'Name can only contain letters and spaces'
  }

  return ''
}

// Real-time validation for mobile
const validateMobile = (mobile: string): string => {
  const trimmed = mobile.trim()

  if (!trimmed) {
    return ''
  }

  if (!/^[0-9]+$/.test(trimmed)) {
    return 'Mobile number can only contain digits'
  }

  if (trimmed.length < 10) {
    return `${10 - trimmed.length} more digits needed`
  }

  if (trimmed.length > 10) {
    return 'Mobile number must be exactly 10 digits'
  }

  if (!/^[6-9]/.test(trimmed)) {
    return 'Number must start with 6, 7, 8, or 9'
  }

  return ''
}

// Validation function for form submission
const validateForm = (): boolean => {
  errors.name = validateName(form.name)
  errors.mobile = validateMobile(form.mobile)

  // Name is required for submission
  if (!form.name.trim()) {
    errors.name = 'Name is required'
  }

  // Mobile is required for submission
  if (!form.mobile.trim()) {
    errors.mobile = 'Mobile number is required'
  }

  return !errors.name && !errors.mobile
}

const handleSubmit = async () => {
  error.value = ''

  if (!validateForm()) {
    return
  }

  // Check if centre_code is available
  if (!props.centre?.center_code) {
    error.value = 'Centre information is missing. Please try again.'
    return
  }

  isSubmitting.value = true

  try {
    const url = getApiUrl('eventRegister')
    const { getCampaignSource } = useCampaign()
    const campaignSource = getCampaignSource()

    const payload: any = {
      name: form.name.trim(),
      mobile: form.mobile.trim(),
      center_code: props.centre.center_code,
      number_of_people: form.numberOfPeople
    }

    // Add campaign_source if it exists
    if (campaignSource) {
      payload.campaign_source = campaignSource
    }

    console.log('Submitting registration with payload:', payload)

    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    const data = await response.json()
    console.log('Registration response:', data)

    if (data.success) {
      showSuccess.value = true
      emit('success')
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

// Fetch WhatsApp link from API
const fetchWhatsAppLink = async () => {
  try {
    const url = getApiUrl('eventRegister').replace('/event-register', '/whatsapp-link')
    const response = await fetch(url)
    const data = await response.json()

    if (data.success && data.data?.link) {
      whatsappLink.value = data.data.link
    } else {
      console.warn('Failed to fetch WhatsApp link:', data.message)
    }
  } catch (err) {
    console.error('Error fetching WhatsApp link:', err)
  }
}

const closeModal = () => {
  resetForm()
  emit('close')
}

// Watch for modal open/close to reset form
import { watch } from 'vue'
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    resetForm()
  }
})

// Watch for centre prop changes and log for debugging
watch(() => props.centre, (newVal) => {
  if (newVal) {
    console.log('Modal opened with centre:', newVal)
    if (!newVal.center_code) {
      console.warn('Warning: centre object missing center_code field', newVal)
    }
  }
})

// Real-time validation as user types
watch(() => form.name, (newVal) => {
  if (newVal) {
    errors.name = validateName(newVal)
  } else {
    errors.name = ''
  }
})

watch(() => form.mobile, (newVal) => {
  if (newVal) {
    errors.mobile = validateMobile(newVal)
  } else {
    errors.mobile = ''
  }
})

// Fetch WhatsApp link on component mount
onMounted(() => {
  fetchWhatsAppLink()
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active > div,
.modal-leave-active > div {
  transition: all 0.3s ease;
}

.modal-enter-from > div,
.modal-leave-to > div {
  transform: scale(0.95);
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.animate-scale-in {
  animation: scaleIn 0.3s ease-out;
}

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

.form-input {
  @apply border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-600 focus:border-transparent transition;
}
</style>
