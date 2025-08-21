<template>
  <BaseLayout>
    <div class="container mx-auto max-w-md p-4 space-y-6">
      <!-- Profile Header -->
      <div class="bg-white rounded-xl p-6 shadow-sm">
        <div class="text-center">
          <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
          <h2 class="text-xl font-semibold text-gray-900">{{ user?.name || 'User' }}</h2>
          <p class="text-gray-600">{{ user?.mobile || 'No phone number' }}</p>
        </div>
      </div>

      <!-- Change Phone Number Section -->
      <div class="bg-white rounded-xl p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Phone Number</h3>
        
        <form @submit.prevent="submitPhoneChange" class="space-y-4">
          <div>
            <label for="currentPhone" class="block text-sm font-medium text-gray-700 mb-2">
              Current Phone Number
            </label>
            <input
              id="currentPhone"
              type="tel"
              :value="user?.mobile"
              disabled
              class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-500"
            />
          </div>

          <div>
            <label for="newPhone" class="block text-sm font-medium text-gray-700 mb-2">
              New Phone Number
            </label>
            <input
              id="newPhone"
              v-model="phoneForm.newPhone"
              type="tel"
              pattern="[0-9]{10}"
              maxlength="10"
              placeholder="Enter 10-digit phone number"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent touch-target"
              :class="{ 'border-red-300': phoneForm.errors.newPhone }"
            />
            <p v-if="phoneForm.errors.newPhone" class="mt-1 text-sm text-red-600">
              {{ phoneForm.errors.newPhone }}
            </p>
          </div>

          <div>
            <label for="phonePassword" class="block text-sm font-medium text-gray-700 mb-2">
              Current Password
            </label>
            <input
              id="phonePassword"
              v-model="phoneForm.password"
              type="password"
              placeholder="Enter current password to confirm"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent touch-target"
              :class="{ 'border-red-300': phoneForm.errors.password }"
            />
            <p v-if="phoneForm.errors.password" class="mt-1 text-sm text-red-600">
              {{ phoneForm.errors.password }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="phoneForm.isSubmitting || !isPhoneFormValid"
            class="w-full bg-primary text-white py-3 px-4 rounded-lg font-medium touch-target transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary-dark"
          >
            <span v-if="!phoneForm.isSubmitting">Update Phone Number</span>
            <span v-else class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Updating...
            </span>
          </button>
        </form>
      </div>

      <!-- Change Password Section -->
      <div class="bg-white rounded-xl p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
        
        <form @submit.prevent="submitPasswordChange" class="space-y-4">
          <div>
            <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-2">
              Current Password
            </label>
            <input
              id="currentPassword"
              v-model="passwordForm.currentPassword"
              type="password"
              placeholder="Enter current password"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent touch-target"
              :class="{ 'border-red-300': passwordForm.errors.currentPassword }"
            />
            <p v-if="passwordForm.errors.currentPassword" class="mt-1 text-sm text-red-600">
              {{ passwordForm.errors.currentPassword }}
            </p>
          </div>

          <div>
            <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">
              New Password
            </label>
            <input
              id="newPassword"
              v-model="passwordForm.newPassword"
              type="password"
              placeholder="Enter new password"
              required
              minlength="6"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent touch-target"
              :class="{ 'border-red-300': passwordForm.errors.newPassword }"
            />
            <p v-if="passwordForm.errors.newPassword" class="mt-1 text-sm text-red-600">
              {{ passwordForm.errors.newPassword }}
            </p>
            <p class="mt-1 text-xs text-gray-500">Password must be at least 6 characters long</p>
          </div>

          <div>
            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">
              Confirm New Password
            </label>
            <input
              id="confirmPassword"
              v-model="passwordForm.confirmPassword"
              type="password"
              placeholder="Confirm new password"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent touch-target"
              :class="{ 'border-red-300': passwordForm.errors.confirmPassword }"
            />
            <p v-if="passwordForm.errors.confirmPassword" class="mt-1 text-sm text-red-600">
              {{ passwordForm.errors.confirmPassword }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="passwordForm.isSubmitting || !isPasswordFormValid"
            class="w-full bg-primary text-white py-3 px-4 rounded-lg font-medium touch-target transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary-dark"
          >
            <span v-if="!passwordForm.isSubmitting">Update Password</span>
            <span v-else class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Updating...
            </span>
          </button>
        </form>
      </div>

      <!-- Logout Section -->
      <div class="bg-white rounded-xl p-6 shadow-sm">
        <button
          @click="handleLogout"
          class="w-full bg-red-600 text-white py-3 px-4 rounded-lg font-medium touch-target transition-colors hover:bg-red-700"
        >
          Logout
        </button>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import { apiService } from '@/services/apiService'
import BaseLayout from '@/components/BaseLayout.vue'

const router = useRouter()
const authStore = useAuthStore()
const appStore = useAppStore()

// User data
const user = computed(() => authStore.user)

// Phone change form
const phoneForm = ref({
  newPhone: '',
  password: '',
  isSubmitting: false,
  errors: {
    newPhone: '',
    password: ''
  }
})

// Password change form
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
  isSubmitting: false,
  errors: {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
})

// Form validation
const isPhoneFormValid = computed(() => {
  return phoneForm.value.newPhone.length === 10 && 
         phoneForm.value.password.length > 0 &&
         !Object.values(phoneForm.value.errors).some(error => error)
})

const isPasswordFormValid = computed(() => {
  return passwordForm.value.currentPassword.length > 0 &&
         passwordForm.value.newPassword.length >= 6 &&
         passwordForm.value.confirmPassword === passwordForm.value.newPassword &&
         !Object.values(passwordForm.value.errors).some(error => error)
})

// Validate phone number
const validatePhone = (phone: string): string => {
  if (!phone) return 'Phone number is required'
  if (phone.length !== 10) return 'Phone number must be 10 digits'
  if (!/^\d{10}$/.test(phone)) return 'Phone number must contain only digits'
  if (phone === user.value?.mobile) return 'New phone number must be different from current'
  return ''
}

// Validate password
const validatePassword = (password: string): string => {
  if (!password) return 'Password is required'
  if (password.length < 6) return 'Password must be at least 6 characters'
  return ''
}

// Clear form errors
const clearPhoneErrors = () => {
  phoneForm.value.errors = { newPhone: '', password: '' }
}

const clearPasswordErrors = () => {
  passwordForm.value.errors = { currentPassword: '', newPassword: '', confirmPassword: '' }
}

// Submit phone change
const submitPhoneChange = async () => {
  clearPhoneErrors()
  
  // Validate form
  const phoneError = validatePhone(phoneForm.value.newPhone)
  if (phoneError) {
    phoneForm.value.errors.newPhone = phoneError
    return
  }

  phoneForm.value.isSubmitting = true

  try {
    const response = await apiService.profile.updatePhone({
      userId: user.value!.id,
      newPhone: phoneForm.value.newPhone,
      currentPassword: phoneForm.value.password
    })

    if (response.success) {
      // Update user data in store
      if (response.user) {
        authStore.updateUser(response.user)
      }
      appStore.showSuccess('Phone number updated successfully')
      phoneForm.value.newPhone = ''
      phoneForm.value.password = ''
    } else {
      phoneForm.value.errors.password = response.message || 'Failed to update phone number'
    }
  } catch (error) {
    console.error('Phone update error:', error)
    appStore.showError('Failed to update phone number')
  } finally {
    phoneForm.value.isSubmitting = false
  }
}

// Submit password change
const submitPasswordChange = async () => {
  clearPasswordErrors()
  
  // Validate form
  const currentPasswordError = validatePassword(passwordForm.value.currentPassword)
  if (currentPasswordError) {
    passwordForm.value.errors.currentPassword = currentPasswordError
    return
  }

  const newPasswordError = validatePassword(passwordForm.value.newPassword)
  if (newPasswordError) {
    passwordForm.value.errors.newPassword = newPasswordError
    return
  }

  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    passwordForm.value.errors.confirmPassword = 'Passwords do not match'
    return
  }

  if (passwordForm.value.currentPassword === passwordForm.value.newPassword) {
    passwordForm.value.errors.newPassword = 'New password must be different from current password'
    return
  }

  passwordForm.value.isSubmitting = true

  try {
    const response = await apiService.profile.updatePassword({
      userId: user.value!.id,
      currentPassword: passwordForm.value.currentPassword,
      newPassword: passwordForm.value.newPassword
    })

    if (response.success) {
      appStore.showSuccess('Password updated successfully')
      passwordForm.value.currentPassword = ''
      passwordForm.value.newPassword = ''
      passwordForm.value.confirmPassword = ''
    } else {
      passwordForm.value.errors.currentPassword = response.message || 'Failed to update password'
    }
  } catch (error) {
    console.error('Password update error:', error)
    appStore.showError('Failed to update password')
  } finally {
    passwordForm.value.isSubmitting = false
  }
}

// Handle logout
const handleLogout = () => {
  authStore.logout()
  appStore.showInfo('Logged out successfully')
  router.push('/login')
}
</script>