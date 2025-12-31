<template>
  <section id="join-us" class="section">
    <div class="container mx-auto px-4">
      <h2 class="section-title">{{ $t('whatsapp.title') }}</h2>
      <p class="section-subtitle">
        {{ $t('whatsapp.subtitle') }}
      </p>

      <div class="max-w-md mx-auto content-card">
        <form @submit.prevent="handleSubmit" class="space-y-4">
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
              :placeholder="$t('whatsapp.mobilePlaceholder')"
              pattern="[0-9]{10}"
              maxlength="10"
              required
              :disabled="isSubmitting"
            />
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
        </form>

        <!-- Success Message -->
        <Transition name="fade">
          <div v-if="showSuccess" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-700 text-center font-medium">
              ✓ {{ $t('whatsapp.successMessage') }}
            </p>
          </div>
        </Transition>

        <!-- Error Message -->
        <Transition name="fade">
          <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-700 text-center">{{ error }}</p>
          </div>
        </Transition>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import type { WhatsAppJoinForm, Centre } from '@/types'

const form = reactive<WhatsAppJoinForm>({
  name: '',
  mobile: '',
  centre_id: 0
})

const isSubmitting = ref(false)
const showSuccess = ref(false)
const error = ref('')

// Sample centres - in real app, fetch from API
const centres = ref<Centre[]>([
  { id: 1, name: 'West Mambalam, Chennai', address: '203, Murugan illam, Chennai 73', district: 'Chennai', state: 'Tamil Nadu', latitude: 13.0418, longitude: 80.2341, phone: '+91-XXXXXXXXXX' },
  { id: 2, name: 'Ashok Nagar, Chennai', address: '13, Vadivel street, Chennai - 83', district: 'Chennai', state: 'Tamil Nadu', latitude: 13.0358, longitude: 80.2102, phone: '+91-XXXXXXXXXX' },
  { id: 3, name: 'Anna Nagar, Chennai', address: '13, Vadivel street, Chennai - 83', district: 'Chennai', state: 'Tamil Nadu', latitude: 13.0850, longitude: 80.2101, phone: '+91-XXXXXXXXXX' }
])

const handleSubmit = async () => {
  error.value = ''
  showSuccess.value = false
  isSubmitting.value = true

  try {
    // Validate mobile number
    if (!/^[0-9]{10}$/.test(form.mobile)) {
      error.value = 'Please enter a valid 10-digit mobile number'
      isSubmitting.value = false
      return
    }

    // TODO: Replace with actual API call
    // await fetch('/api/whatsapp/join', {
    //   method: 'POST',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify(form)
    // })

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))

    console.log('Form submitted:', form)

    // Show success message
    showSuccess.value = true

    // Reset form
    form.name = ''
    form.mobile = ''
    form.centre_id = 0

    // Hide success message after 5 seconds
    setTimeout(() => {
      showSuccess.value = false
    }, 5000)

  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}
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
