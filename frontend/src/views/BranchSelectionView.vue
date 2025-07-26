<template>
  <div class="min-h-screen bg-gray-50 safe-top safe-bottom">
    <!-- Header -->
    <div class="gradient-bg text-white p-6 pb-8">
      <div class="flex items-center justify-between mb-6">
        <button @click="logout" class="p-2 text-white hover:bg-white hover:bg-opacity-20 rounded-lg touch-target">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
        </button>
        <h1 class="text-xl font-semibold">Welcome, {{ user?.name }}</h1>
        <div class="w-10 h-10"></div> <!-- Spacer -->
      </div>

      <!-- Header content -->
      <div class="text-center">
        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <span class="text-white text-2xl">🏢</span>
        </div>
        <h2 class="text-2xl font-bold mb-2">Select Your Branch</h2>
        <p class="text-blue-100 text-sm">Choose your meditation center</p>
        <div class="w-16 h-1 bg-white bg-opacity-30 rounded-full mx-auto mt-3"></div>
      </div>
    </div>

    <!-- Branch List -->
    <div class="p-4 -mt-4">
      <div v-if="isLoading" class="space-y-4">
        <!-- Loading skeleton -->
        <div v-for="i in 3" :key="i" class="card p-6 animate-pulse">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gray-200 rounded-xl"></div>
            <div class="flex-1">
              <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="space-y-4">
        <button
          v-for="(branch, index) in userBranches"
          :key="branch.id"
          @click="selectBranch(branch)"
          class="w-full p-6 text-left card hover:shadow-xl border-2 border-transparent hover:border-primary/20 transition-all duration-300 touch-target transform hover:scale-[1.02] active:scale-[0.98] animate-fade-in"
          :style="{ animationDelay: index * 100 + 'ms' }"
        >
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-br from-primary/10 to-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
              <span class="text-primary text-xl">🏛️</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-lg text-gray-900 truncate">{{ branch.name }}</div>
              <div class="text-sm text-gray-500 mt-1 truncate">{{ branch.location }}</div>
            </div>
            <div class="text-primary">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
        </button>
      </div>

      <!-- Empty state -->
      <div v-if="!isLoading && userBranches.length === 0" class="text-center py-12">
        <div class="text-6xl mb-4">🏢</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Branches Assigned</h3>
        <p class="text-gray-500 mb-6">Contact your administrator to get access to branches.</p>
        <button @click="logout" class="btn-secondary">
          Logout and try different account
        </button>
      </div>
    </div>

    <!-- Footer info -->
    <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-bottom">
      <div class="text-center text-sm text-gray-500">
        <p>You have access to {{ userBranches.length }} {{ userBranches.length === 1 ? 'branch' : 'branches' }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import type { Branch } from '@/types'

const router = useRouter()
const authStore = useAuthStore()
const appStore = useAppStore()

// Computed properties
const user = computed(() => authStore.user)
const userBranches = computed(() => authStore.userBranches)
const isLoading = computed(() => appStore.isLoading)

// Methods
const selectBranch = async (branch: Branch) => {
  try {
    appStore.setLoading(true)
    
    // Simulate selection delay for better UX
    await appStore.simulateApiDelay(300, 500)
    
    authStore.selectBranch(branch)
    appStore.showSuccess(`Selected ${branch.name}`)
    
    // Navigate to dashboard
    router.push('/dashboard')
  } catch (error) {
    console.error('Branch selection error:', error)
    appStore.showError('Failed to select branch. Please try again.')
  } finally {
    appStore.setLoading(false)
  }
}

const logout = () => {
  authStore.logout()
  appStore.showInfo('Logged out successfully')
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  // If user somehow doesn't have branches, redirect to login
  if (!authStore.isLoggedIn) {
    router.push('/login')
    return
  }
  
  // If already has selected branch, redirect to dashboard
  if (authStore.hasSelectedBranch) {
    router.push('/dashboard')
    return
  }
  
  // If only one branch, auto-select it
  if (userBranches.value.length === 1) {
    setTimeout(() => {
      selectBranch(userBranches.value[0])
    }, 1000)
  }
})
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
  opacity: 0;
}
</style>