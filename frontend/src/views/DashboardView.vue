<template>
  <BaseLayout>
    <div class="p-4 space-y-4">
      <!-- Month Header -->
      <div class="card p-4">
        <h2 class="text-xl font-bold text-primary text-center">My Centre - Yoga Hours - Dashboard</h2>
      </div>
      
      <!-- Summary Cards - Mobile Stack -->
      <div class="space-y-3">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-2xl shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-3xl font-bold">{{ dashboardData?.summary?.total_participants || 0 }}</div>
              <div class="text-blue-100 text-sm">Total Participants</div>
            </div>
            <div class="text-4xl opacity-70">👥</div>
          </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-2xl shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-3xl font-bold">{{ dashboardData?.summary?.total_hours || 0 }}</div>
              <div class="text-green-100 text-sm">Total Hours</div>
            </div>
            <div class="text-4xl opacity-70">⏰</div>
          </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-2xl shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-3xl font-bold">{{ dashboardData?.summary?.total_sessions || 0 }}</div>
              <div class="text-purple-100 text-sm">Total Sessions</div>
            </div>
            <div class="text-4xl opacity-70">🎯</div>
          </div>
        </div>
      </div>


      <!-- Empty State -->
      <div v-if="!isLoading && !dashboardData?.summary?.total_sessions" class="text-center py-12">
        <div class="text-6xl mb-4">📊</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No sessions recorded yet</h3>
        <p class="text-gray-500 mb-6">Start recording meditation sessions to see statistics here.</p>
        <router-link to="/data-entry" class="btn-primary inline-flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Record First Session
        </router-link>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="card p-6 animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
          <div class="space-y-3">
            <div v-for="j in 2" :key="j" class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
              <div class="flex-1">
                <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                <div class="h-2 bg-gray-200 rounded w-1/2 mt-1"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import BaseLayout from '@/components/BaseLayout.vue'
import { useSessionsStore } from '@/stores/sessions'
import { useAppStore } from '@/stores/app'
import { useAuthStore } from '@/stores/auth'
import type { Session } from '@/types'

const router = useRouter()
const sessionsStore = useSessionsStore()
const appStore = useAppStore()
const authStore = useAuthStore()

// Computed properties
const dashboardData = computed(() => sessionsStore.dashboardData)
const todaySessions = computed(() => sessionsStore.todaySessions)
const formattedMonth = computed(() => sessionsStore.formattedMonth)
const isLoading = computed(() => sessionsStore.isLoading)
const formatTime = (time: string) => sessionsStore.formatTime(time)

const getTimeOnly = (time: string): string => {
  const formatted = sessionsStore.formatTime(time)
  return formatted.split(' ')[0]
}

const getAmPm = (time: string): string => {
  const formatted = sessionsStore.formatTime(time)
  return formatted.split(' ')[1]
}

// Methods
const editSession = (session: Session) => {
  sessionsStore.startEdit(session)
  appStore.showInfo('Editing session - modify details and submit to update')
  router.push('/data-entry')
}

const deleteSession = async (sessionId: number) => {
  if (!confirm('Are you sure you want to delete this session?')) {
    return
  }

  try {
    appStore.setLoading(true)
    const success = await sessionsStore.deleteSession(sessionId)
    
    if (success) {
      appStore.showSuccess('Session deleted successfully!')
    } else {
      appStore.showError('Failed to delete session')
    }
  } catch (error) {
    console.error('Delete session error:', error)
    appStore.showError('An error occurred while deleting the session')
  } finally {
    appStore.setLoading(false)
  }
}

const loadData = async () => {
  try {
    sessionsStore.isLoading = true
    
    // Load dashboard data and today's sessions concurrently
    await Promise.all([
      sessionsStore.loadDashboardData(),
      sessionsStore.loadTodaySessions()
    ])
  } catch (error) {
    console.error('Dashboard data loading error:', error)
    appStore.showError('Failed to load dashboard data')
  } finally {
    sessionsStore.isLoading = false
  }
}

// Lifecycle
onMounted(() => {
  loadData()
})

// Watch for branch changes
watch(() => authStore.currentBranch, (newBranch, oldBranch) => {
  if (newBranch && newBranch.id !== oldBranch?.id) {
    loadData()
  }
}, { immediate: false })
</script>