<template>
  <div class="min-h-screen bg-gray-50 safe-top safe-bottom">
    <!-- Top Navigation -->
    <nav class="gradient-bg text-white p-4 sticky top-0 z-30">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center p-2 shadow-sm">
            <img src="/sn-logo.png" alt="SN Logo" class="w-full h-full object-contain">
          </div>
          <div>
            <h1 class="font-semibold text-lg">{{ currentBranch?.name || 'Meditation Tracker' }}</h1>
            <p class="text-xs text-blue-100">{{ user?.name }}</p>
          </div>
        </div>
        
        <div class="flex items-center space-x-2">
          <!-- API Switcher (only in development) -->
          <ApiSwitcher v-if="isDevelopment" />
          
          <!-- Branch switcher -->
          <button
            @click="showBranchSwitcher = !showBranchSwitcher"
            class="p-2 text-white hover:bg-white hover:bg-opacity-20 rounded-lg touch-target relative"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
            
            <!-- Branch switcher dropdown -->
            <div
              v-if="showBranchSwitcher"
              class="absolute right-0 top-full mt-2 w-64 bg-white rounded-xl shadow-lg border py-2 z-40"
              @click.stop
            >
              <div class="px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wide">
                Switch Branch
              </div>
              <button
                v-for="branch in userBranches"
                :key="branch.id"
                @click="switchBranch(branch)"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center justify-between"
                :class="{ 'bg-blue-50 text-blue-700': branch.id === currentBranch?.id }"
              >
                <span>{{ branch.name }}</span>
                <svg v-if="branch.id === currentBranch?.id" class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
              </button>
              <div class="border-t my-1"></div>
              <button
                @click="logout"
                class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50"
              >
                Logout
              </button>
            </div>
          </button>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main :class="hideNavTabs ? 'pb-4' : 'pb-20'">
      <slot />
    </main>

    <!-- Bottom Navigation -->
    <nav v-if="!hideNavTabs" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-30 safe-bottom">
      <div class="grid grid-cols-4 h-16">
        <router-link
          to="/dashboard"
          class="flex flex-col items-center justify-center space-y-1 text-xs font-medium touch-target transition-colors"
          :class="currentRoute === 'Dashboard' ? 'text-primary bg-blue-50' : 'text-gray-500 hover:text-gray-700'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          <span>Dashboard</span>
        </router-link>

        <router-link
          to="/data-entry"
          class="flex flex-col items-center justify-center space-y-1 text-xs font-medium touch-target transition-colors"
          :class="currentRoute === 'DataEntry' ? 'text-primary bg-blue-50' : 'text-gray-500 hover:text-gray-700'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <span>Record</span>
        </router-link>

        <router-link
          to="/participants"
          class="flex flex-col items-center justify-center space-y-1 text-xs font-medium touch-target transition-colors"
          :class="currentRoute === 'Participants' ? 'text-primary bg-blue-50' : 'text-gray-500 hover:text-gray-700'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
          </svg>
          <span>Yogis</span>
        </router-link>

        <router-link
          to="/profile"
          class="flex flex-col items-center justify-center space-y-1 text-xs font-medium touch-target transition-colors"
          :class="currentRoute === 'Profile' ? 'text-primary bg-blue-50' : 'text-gray-500 hover:text-gray-700'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
          <span>Profile</span>
        </router-link>
      </div>
    </nav>

    <!-- Click outside handler for dropdowns -->
    <div
      v-if="showBranchSwitcher"
      class="fixed inset-0 z-20"
      @click="showBranchSwitcher = false"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import { useSessionsStore } from '@/stores/sessions'
import { useParticipantsStore } from '@/stores/participants'
import ApiSwitcher from '@/components/ApiSwitcher.vue'
import type { Branch } from '@/types'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const appStore = useAppStore()
const sessionsStore = useSessionsStore()
const participantsStore = useParticipantsStore()

// Local state
const showBranchSwitcher = ref(false)

// Computed properties
const user = computed(() => authStore.user)
const currentBranch = computed(() => authStore.currentBranch)
const userBranches = computed(() => authStore.userBranches)
const currentRoute = computed(() => route.name)
const isDevelopment = computed(() => import.meta.env.DEV)
const hideNavTabs = false;//computed(() => import.meta.env.VITE_HIDE_NAV_TABS === 'true')

// Methods
const switchBranch = async (branch: Branch) => {
  if (branch.id === currentBranch.value?.id) {
    showBranchSwitcher.value = false
    return
  }

  try {
    appStore.setLoading(true)
    
    // Clear existing data
    sessionsStore.resetStore()
    participantsStore.resetStore()
    
    // Switch branch
    authStore.selectBranch(branch)
    showBranchSwitcher.value = false
    
    appStore.showSuccess(`Switched to ${branch.name}`)
    
    // Stay on current page instead of redirecting to dashboard
    // Data will reload automatically via stores when branch changes
  } catch (error) {
    console.error('Branch switch error:', error)
    appStore.showError('Failed to switch branch')
  } finally {
    appStore.setLoading(false)
  }
}

const logout = () => {
  showBranchSwitcher.value = false
  authStore.logout()
  sessionsStore.resetStore()
  participantsStore.resetStore()
  appStore.showInfo('Logged out successfully')
  router.push('/login')
}

// Close dropdown on escape key
const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    showBranchSwitcher.value = false
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>