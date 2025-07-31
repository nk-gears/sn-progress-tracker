<template>
  <BaseLayout>
    <div class="p-4 space-y-4">
      <!-- Month Header -->
      <div class="card p-4">
        <h2 class="text-xl font-bold text-primary text-center">{{ formattedMonth }}</h2>
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

      <!-- Top Participants -->
      <div v-if="dashboardData?.top_participants?.length" class="card p-6">
        <h3 class="text-lg font-semibold text-primary mb-4 flex items-center">
          <span class="mr-2">🏆</span> Top Participants
        </h3>
        <div class="space-y-3">
          <div
            v-for="(participant, index) in dashboardData.top_participants.slice(0, 5)"
            :key="participant.name"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl"
          >
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-gradient-to-br from-primary/10 to-indigo-100 rounded-full flex items-center justify-center text-sm font-bold text-primary">
                {{ index + 1 }}
              </div>
              <div>
                <div class="font-medium">{{ participant.name }}</div>
                <div class="text-sm text-gray-600">
                  {{ participant.session_count }} sessions • {{ Math.round(participant.total_minutes / 60) }}h
                </div>
              </div>
            </div>
            <div class="text-2xl">
              {{ index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : '⭐' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Time Distribution -->
      <div v-if="dashboardData?.time_distribution?.length" class="card p-6">
        <h3 class="text-lg font-semibold text-primary mb-4 flex items-center">
          <span class="mr-2">⏰</span> Time Distribution
        </h3>
        <div class="space-y-3">
          <div
            v-for="period in dashboardData.time_distribution"
            :key="period.time_period"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl"
          >
            <div class="flex items-center space-x-3">
              <div class="text-2xl">
                {{ period.time_period === 'Morning' ? '🌅' : 
                   period.time_period === 'Afternoon' ? '☀️' : '🌆' }}
              </div>
              <div>
                <div class="font-medium">{{ period.time_period }}</div>
                <div class="text-sm text-gray-600">
                  {{ period.session_count }} sessions
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-semibold">{{ Math.round(period.total_minutes / 60) }}h</div>
              <div class="text-xs text-gray-500">{{ period.total_minutes }}min</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Today's Sessions -->
      <div v-if="todaySessions.length" class="card p-6">
        <h3 class="text-lg font-semibold text-primary mb-4 flex items-center">
          <span class="mr-2">📅</span> Today's Sessions
        </h3>
        <div class="space-y-2">
          <div
            v-for="session in todaySessions"
            :key="session.id"
            class="flex justify-between items-center p-3 bg-gray-50 rounded-xl"
          >
            <div class="flex-1">
              <div class="font-medium">{{ session.participant_name }}</div>
              <div class="text-sm text-gray-600 flex items-center space-x-1">
                <span class="flex items-baseline space-x-1">
                  <span class="font-medium">{{ getTimeOnly(session.start_time) }}</span>
                  <span class="text-xs">{{ getAmPm(session.start_time) }}</span>
                </span>
                <span>•</span>
                <span>{{ session.duration_minutes }}min</span>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="text-xs text-gray-500">
                {{ new Date(session.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }}
              </div>
              <button
                @click="editSession(session)"
                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg touch-target"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
              </button>
              <button
                @click="deleteSession(session.id)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-lg touch-target"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
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
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BaseLayout from '@/components/BaseLayout.vue'
import { useSessionsStore } from '@/stores/sessions'
import { useAppStore } from '@/stores/app'
import type { Session } from '@/types'

const router = useRouter()
const sessionsStore = useSessionsStore()
const appStore = useAppStore()

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
</script>