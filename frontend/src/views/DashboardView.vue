<template>
  <div>
    <BaseLayout>
      <div class="p-4 space-y-4">
      <!-- Dashboard Header -->
      <div class="card p-4">
        <h2 class="text-xl font-bold text-primary text-center">My Centre - All-Time Statistics</h2>
        <p class="text-sm text-gray-600 text-center mt-1">Total meditation hours and sessions across all time</p>
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

      <!-- Event Report Section -->
      <div class="card p-4">
        <h3 class="text-lg font-bold text-primary mb-4">Event Reports</h3>
        <p class="text-sm text-gray-600 mb-4">Manage and view branch event submissions</p>

        <div class="grid grid-cols-2 gap-3">
          <button
            @click="openEventReportModal"
            class="btn-primary flex items-center justify-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Submit Report</span>
          </button>

          <button
            @click="openEventReportsModal"
            class="btn-primary flex items-center justify-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>View Reports</span>
          </button>
        </div>
      </div>

      <!-- Export Button -->
      <div v-if="!isLoading && dashboardData?.summary?.total_sessions" class="card p-4">
        <button
          @click="exportSessionDetails"
          :disabled="isExporting"
          class="w-full btn-primary flex items-center justify-center space-x-2"
        >
          <svg v-if="!isExporting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m-6 4V6a2 2 0 112 0v10.586a2 2 0 01-2-2z"></path>
          </svg>
          <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          <span>{{ isExporting ? 'Exporting...' : 'Export Session Details' }}</span>
        </button>
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

    <!-- Event Report Modal -->
    <EventReportModal
      :is-open="showEventReportModal"
      @close="closeEventReportModal"
      @success="handleEventReportSuccess"
    />

    <!-- Event Reports List Modal -->
    <EventReportsModal
      :is-open="showEventReportsModal"
      @close="closeEventReportsModal"
      @addReport="handleAddReportFromList"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, watch, ref } from 'vue'
import { useRouter } from 'vue-router'
import BaseLayout from '@/components/BaseLayout.vue'
import EventReportModal from '@/components/EventReportModal.vue'
import EventReportsModal from '@/components/EventReportsModal.vue'
import { useSessionsStore } from '@/stores/sessions'
import { useAppStore } from '@/stores/app'
import { useAuthStore } from '@/stores/auth'
import type { Session } from '@/types'
import { apiService } from '@/services/apiService'

const router = useRouter()
const sessionsStore = useSessionsStore()
const appStore = useAppStore()
const authStore = useAuthStore()

// Export and Event Report state
const isExporting = ref(false)
const showEventReportModal = ref(false)
const showEventReportsModal = ref(false)

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
const openEventReportModal = () => {
  showEventReportModal.value = true
}

const closeEventReportModal = () => {
  showEventReportModal.value = false
}

const handleEventReportSuccess = () => {
  showEventReportModal.value = false
  appStore.showSuccess('Event report submitted successfully!')
}

const openEventReportsModal = () => {
  showEventReportsModal.value = true
}

const closeEventReportsModal = () => {
  showEventReportsModal.value = false
}

const handleAddReportFromList = () => {
  closeEventReportsModal()
  showEventReportModal.value = true
}

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
    console.log('Loading dashboard data...')
    sessionsStore.isLoading = true

    // Load dashboard data and today's sessions concurrently
    const results = await Promise.all([
      sessionsStore.loadDashboardData(),
      sessionsStore.loadTodaySessions()
    ])
    console.log('Dashboard data loaded successfully:', results)
  } catch (error) {
    console.error('Dashboard data loading error:', error)
    appStore.showError('Failed to load dashboard data')
    // Still show content even if data fails to load
  } finally {
    sessionsStore.isLoading = false
  }
}

const exportSessionDetails = async () => {
  const branchId = authStore.currentBranch?.id
  if (!branchId) {
    appStore.showError('No branch selected')
    return
  }

  isExporting.value = true
  try {
    console.log('Starting export for branch:', branchId)
    
    // Fetch all sessions for the branch
    const response = await apiService.sessions.getAll(branchId)
    console.log('API response:', response)
    
    if (response.success && response.sessions) {
      const sessions = response.sessions
      console.log('Sessions found:', sessions.length)
      
      if (sessions.length === 0) {
        appStore.showError('No sessions found to export')
        return
      }
      
      // Prepare CSV data
      const csvHeaders = ['Participant Name', 'Date', 'Start Time', 'End Time', 'Duration (minutes)']
      const csvRows = sessions.map(session => {
        const startTime = session.start_time
        const durationMinutes = session.duration_minutes
        const [hours, minutes] = startTime.split(':').map(Number)
        const endMinutes = hours * 60 + minutes + durationMinutes
        const endHours = Math.floor(endMinutes / 60) % 24 // Handle overflow past midnight
        const endMins = endMinutes % 60
        const endTime = `${endHours.toString().padStart(2, '0')}:${endMins.toString().padStart(2, '0')}`
        
        return [
          session.participant_name || 'Unknown',
          session.session_date,
          startTime,
          endTime,
          durationMinutes.toString()
        ]
      })
      
      // Create CSV content
      const csvContent = [csvHeaders, ...csvRows]
        .map(row => row.map(field => `"${field}"`).join(','))
        .join('\n')
      
      console.log('CSV content preview:', csvContent.substring(0, 200))
      
      // Download CSV file
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      
      if (link.download !== undefined) {
        const url = URL.createObjectURL(blob)
        link.setAttribute('href', url)
        const branchName = (authStore.currentBranch?.name || 'branch').replace(/[^a-zA-Z0-9]/g, '_')
        const today = new Date().toISOString().slice(0, 10)
        link.setAttribute('download', `${branchName}-session-details-${today}.csv`)
        link.style.visibility = 'hidden'
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        URL.revokeObjectURL(url)
        
        appStore.showSuccess(`Exported ${sessions.length} sessions successfully!`)
      } else {
        appStore.showError('Download not supported in this browser')
      }
    } else {
      console.error('Export failed - API response:', response)
      appStore.showError(response.message || 'No sessions found to export')
    }
  } catch (error) {
    console.error('Export error:', error)
    appStore.showError('Failed to export session details')
  } finally {
    isExporting.value = false
  }
}

// Lifecycle
onMounted(() => {
  console.log('DashboardView mounted, loading data...')
  loadData()
})

// Watch for branch changes
watch(() => authStore.currentBranch, (newBranch, oldBranch) => {
  if (newBranch && newBranch.id !== oldBranch?.id) {
    loadData()
  }
}, { immediate: false })
</script>