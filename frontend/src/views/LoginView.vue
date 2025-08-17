<template>
  <div class="min-h-screen gradient-bg flex items-center justify-center p-4 safe-top safe-bottom">
    <div class="w-full max-w-sm">
      <!-- Logo/Header Section -->
      <div class="text-center mb-12">
        <div class="w-24 h-24 bg-blue-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg p-4">
          <img src="/sn-logo.png" alt="SN Logo" class="w-full h-full object-contain">
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">Meditation Tracker</h1>
        <p class="text-blue-100 text-lg">BK Branch wise Collective Yoga Tracker </p>
        <div class="w-20 h-1 bg-white bg-opacity-30 rounded-full mx-auto mt-4"></div>
      </div>

      <!-- Login Form -->
      <div class="card p-6 backdrop-blur-sm bg-white bg-opacity-95">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Mobile Number -->
          <div>
            <label for="mobile" class="block text-sm font-medium text-gray-700 mb-2">
              Mobile Number
            </label>
            <input
              id="mobile"
              v-model="form.mobile"
              type="tel"
              maxlength="10"
              required
              placeholder="Enter your mobile number"
              class="input-field"
              :class="{ 'border-red-300': errors.mobile }"
            >
            <p v-if="errors.mobile" class="mt-1 text-sm text-red-600">{{ errors.mobile }}</p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Password
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="Enter your password"
                class="input-field pr-12"
                :class="{ 'border-red-300': errors.password }"
              >
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.05 6.05M9.878 9.878a3 3 0 00-.007 4.243m4.242-4.242L15.95 6.05M13.121 13.121a3 3 0 01-4.243-.007m4.243.007l1.414 1.414M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <!-- Error Message -->
          <div v-if="errors.general" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            {{ errors.general }}
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading || !isFormValid"
            class="btn-primary w-full"
          >
            <span v-if="isLoading" class="flex items-center justify-center">
              <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
              Signing in...
            </span>
            <span v-else>Sign In</span>
          </button>
        </form>

        <!-- Demo Credentials -->
        <!-- <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
          <h3 class="text-sm font-medium text-blue-800 mb-2">Demo Credentials</h3>
          <div class="text-xs text-blue-700 space-y-1">
            <p><strong>Mobile:</strong> 9283181228</p>
            <p><strong>Password:</strong> meditation123</p>
          </div>
          <button
            @click="fillDemoCredentials"
            class="mt-2 text-xs text-blue-600 hover:text-blue-800 underline"
          >
            Use demo credentials
          </button>
        </div> -->
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'

const router = useRouter()
const authStore = useAuthStore()
const appStore = useAppStore()

// Form state
const form = ref({
  mobile: '',
  password: ''
})

const showPassword = ref(false)
const isLoading = ref(false)

// Validation
const errors = ref({
  mobile: '',
  password: '',
  general: ''
})

const isFormValid = computed(() => {
  return form.value.mobile.length >= 10 && 
         form.value.password.length >= 6 &&
         !errors.value.mobile &&
         !errors.value.password
})

// Methods
const validateForm = () => {
  errors.value = { mobile: '', password: '', general: '' }
  
  if (form.value.mobile.length < 10) {
    errors.value.mobile = 'Mobile number must be 10 digits'
  } else if (!/^\d+$/.test(form.value.mobile)) {
    errors.value.mobile = 'Mobile number must contain only digits'
  }
  
  if (form.value.password.length < 6) {
    errors.value.password = 'Password must be at least 6 characters'
  }
  
  return !errors.value.mobile && !errors.value.password
}

const handleLogin = async () => {
  if (!validateForm()) return
  
  isLoading.value = true
  errors.value.general = ''
  
  try {
    appStore.setLoading(true)
    
    const success = await authStore.login(form.value.mobile, form.value.password)
    
    if (success) {
      appStore.showSuccess('Login successful!')
      
      // Navigate based on branch selection
      if (authStore.hasSelectedBranch) {
        router.push('/data-entry')
      } else {
        router.push('/branches')
      }
    } else {
      errors.value.general = 'Invalid mobile number or password'
    }
  } catch (error) {
    console.error('Login error:', error)
    errors.value.general = 'An error occurred during login. Please try again.'
  } finally {
    isLoading.value = false
    appStore.setLoading(false)
  }
}

const fillDemoCredentials = () => {
  form.value.mobile = '9283181228'
  form.value.password = 'meditation123'
  errors.value = { mobile: '', password: '', general: '' }
}

// Lifecycle
onMounted(() => {
  // Clear any existing session
  if (authStore.isLoggedIn) {
    authStore.logout()
  }
  
  // Auto-fill demo credentials for development
  if (import.meta.env.DEV) {
    setTimeout(() => {
      fillDemoCredentials()
    }, 500)
  }
})
</script>