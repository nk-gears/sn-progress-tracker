<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-center">🧘‍♂️ Admin Meditation Report</h1>
        <p class="text-center text-blue-100 mt-2">Consolidated report of participants, sessions, and meditation hours</p>
        <p class="text-center text-blue-200 mt-1 text-sm">📍 Direct access: /admin-report (No login required)</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Filters Section -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">📊 Report Filters</h2>
        
        <!-- Filter Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Branch Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Branches</label>
            <div class="space-y-2">
              <div class="flex items-center">
                <input
                  id="all-branches"
                  v-model="selectAllBranches"
                  @change="handleSelectAllChange"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="all-branches" class="ml-2 text-sm text-gray-700">All Branches</label>
              </div>
              <div class="max-h-32 overflow-y-auto border border-gray-200 rounded p-2 space-y-1">
                <div v-if="availableBranches.length > 0">
                  <div 
                    v-for="branch in availableBranches"
                    :key="branch.id"
                    class="flex items-center"
                  >
                    <input
                      :id="`branch-${branch.id}`"
                      v-model="selectedBranches"
                      :value="branch.id"
                      @change="handleBranchChange"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label :for="`branch-${branch.id}`" class="ml-2 text-sm text-gray-700">{{ branch.name }}</label>
                  </div>
                </div>
                <div v-else class="text-center py-4 text-gray-500 text-sm">
                  <div class="text-2xl mb-2">🏢</div>
                  <p>No branches available</p>
                  <p class="text-xs mt-1">Check API connection</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Date Range -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <div class="space-y-2">
              <input
                v-model="startDate"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
              <input
                v-model="endDate"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col justify-end space-y-2">
            <button
              @click="generateReport"
              :disabled="isLoading || selectedBranches.length === 0"
              class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center"
            >
              <svg v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <span>{{ isLoading ? 'Generating...' : 'Generate Report' }}</span>
            </button>
            
            <button
              v-if="reportData.length > 0"
              @click="exportReport"
              :disabled="isExporting"
              class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center"
            >
              <svg v-if="isExporting" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m-6 4V6a2 2 0 112 0v10.586a2 2 0 01-2-2z"></path>
              </svg>
              <span>{{ isExporting ? 'Exporting...' : 'Export CSV' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Generating report...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <h3 class="text-lg font-medium text-red-800">Error generating report</h3>
            <p class="text-red-600">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Report Results -->
      <div v-else-if="reportData.length > 0" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-blue-50 rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-blue-600">Total Participants</p>
                <p class="text-2xl font-bold text-blue-900">{{ totalSummary.participants }}</p>
              </div>
              <div class="text-3xl">👥</div>
            </div>
          </div>
          <div class="bg-green-50 rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-green-600">Total Sessions</p>
                <p class="text-2xl font-bold text-green-900">{{ totalSummary.sessions }}</p>
              </div>
              <div class="text-3xl">🎯</div>
            </div>
          </div>
          <div class="bg-purple-50 rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-purple-600">Total Hours</p>
                <p class="text-2xl font-bold text-purple-900">{{ totalSummary.hours }}</p>
              </div>
              <div class="text-3xl">⏰</div>
            </div>
          </div>
        </div>

        <!-- Branch Reports -->
        <div class="space-y-6">
          <div 
            v-for="branchReport in reportData"
            :key="branchReport.branch_id"
            class="bg-white rounded-xl shadow-lg overflow-hidden"
          >
            <!-- Branch Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-4">
              <h3 class="text-xl font-semibold">{{ branchReport.branch_name }}</h3>
              <div class="grid grid-cols-3 gap-4 mt-3 text-sm">
                <div>
                  <span class="text-blue-100">Participants:</span>
                  <span class="font-semibold ml-1">{{ branchReport.summary.participants }}</span>
                </div>
                <div>
                  <span class="text-blue-100">Sessions:</span>
                  <span class="font-semibold ml-1">{{ branchReport.summary.sessions }}</span>
                </div>
                <div>
                  <span class="text-blue-100">Hours:</span>
                  <span class="font-semibold ml-1">{{ branchReport.summary.hours }}</span>
                </div>
              </div>
            </div>

            <!-- Daily Breakdown -->
            <div class="p-6">
              <h4 class="text-lg font-medium text-gray-800 mb-4">📅 Daily Breakdown</h4>
              <div v-if="branchReport.daily_stats.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="border-b border-gray-200">
                      <th class="text-left py-2 px-3 font-medium text-gray-700">Date</th>
                      <th class="text-center py-2 px-3 font-medium text-gray-700">Participants</th>
                      <th class="text-center py-2 px-3 font-medium text-gray-700">Sessions</th>
                      <th class="text-center py-2 px-3 font-medium text-gray-700">Hours</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr 
                      v-for="daily in branchReport.daily_stats"
                      :key="daily.date"
                      class="border-b border-gray-100 hover:bg-gray-50"
                    >
                      <td class="py-2 px-3 font-medium">{{ formatDate(daily.date) }}</td>
                      <td class="py-2 px-3 text-center">{{ daily.unique_participants }}</td>
                      <td class="py-2 px-3 text-center">{{ daily.sessions_count }}</td>
                      <td class="py-2 px-3 text-center">{{ Math.round(daily.total_minutes / 60 * 100) / 100 }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No session data found for the selected date range
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!isLoading && !error" class="bg-white rounded-xl shadow-lg p-12 text-center">
        <div class="text-6xl mb-4">📊</div>
        <h3 class="text-xl font-medium text-gray-800 mb-2">No Report Generated</h3>
        <p class="text-gray-600 mb-6">Select branches and date range, then click "Generate Report" to view data.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { apiService } from '@/services/apiService'
import type { Branch } from '@/types'

// Types
interface BranchReport {
  branch_id: number
  branch_name: string
  summary: {
    participants: number
    sessions: number
    hours: number
  }
  daily_stats: Array<{
    date: string
    unique_participants: number
    sessions_count: number
    total_minutes: number
  }>
}

// Reactive state
const availableBranches = ref<Branch[]>([])
const selectedBranches = ref<number[]>([])
const selectAllBranches = ref(false)
const startDate = ref('')
const endDate = ref('')
const isLoading = ref(false)
const isExporting = ref(false)
const error = ref('')
const reportData = ref<BranchReport[]>([])

// Computed
const totalSummary = computed(() => {
  return reportData.value.reduce(
    (total, branch) => ({
      participants: total.participants + branch.summary.participants,
      sessions: total.sessions + branch.summary.sessions,
      hours: total.hours + branch.summary.hours
    }),
    { participants: 0, sessions: 0, hours: 0 }
  )
})

// Methods
const loadBranches = async () => {
  try {
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/backend/api.php'
    const response = await fetch(`${apiBaseUrl}/branches`)
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    
    if (data.success && data.branches && data.branches.length > 0) {
      availableBranches.value = data.branches
      selectedBranches.value = data.branches.map((b: Branch) => b.id)
      selectAllBranches.value = true
    } else {
      error.value = data.message || 'No branches found in the system'
      availableBranches.value = []
      selectedBranches.value = []
      selectAllBranches.value = false
    }
  } catch (err) {
    console.error('Error loading branches:', err)
    error.value = 'Failed to load branches. Please check your connection and try again.'
    availableBranches.value = []
    selectedBranches.value = []
    selectAllBranches.value = false
  }
}

const handleSelectAllChange = () => {
  if (selectAllBranches.value) {
    selectedBranches.value = availableBranches.value.map(b => b.id)
  } else {
    selectedBranches.value = []
  }
}

const handleBranchChange = () => {
  selectAllBranches.value = selectedBranches.value.length === availableBranches.value.length
}

const generateReport = async () => {
  if (selectedBranches.value.length === 0) {
    error.value = 'Please select at least one branch'
    return
  }

  isLoading.value = true
  error.value = ''
  reportData.value = []

  try {
    // Call API to get admin report data
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/backend/api.php'
    console.log('Calling admin report API:', `${apiBaseUrl}/admin-report`)
    console.log('Request payload:', {
      branch_ids: selectedBranches.value,
      start_date: startDate.value,
      end_date: endDate.value
    })
    
    const response = await fetch(`${apiBaseUrl}/admin-report`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        branch_ids: selectedBranches.value,
        start_date: startDate.value,
        end_date: endDate.value
      })
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`)
    }

    const data = await response.json()
    console.log('API Response:', data)

    if (data.success) {
      if (data.data && data.data.length > 0) {
        reportData.value = data.data
      } else {
        reportData.value = []
        error.value = 'No data found for the selected branches and date range'
      }
    } else {
      error.value = data.message || 'Failed to generate report'
    }
  } catch (err) {
    console.error('Error generating report:', err)
    if (err instanceof TypeError) {
      error.value = 'Network error. Please check your connection and API server.'
    } else if (err.message.includes('HTTP error')) {
      error.value = `Server error: ${err.message}. Please check the API endpoint.`
    } else {
      error.value = `Unexpected error: ${err.message}`
    }
  } finally {
    isLoading.value = false
  }
}

const exportReport = async () => {
  isExporting.value = true
  
  try {
    // Prepare CSV data
    const csvRows = []
    csvRows.push(['Branch', 'Date', 'Participants', 'Sessions', 'Hours'])

    reportData.value.forEach(branch => {
      branch.daily_stats.forEach(daily => {
        csvRows.push([
          branch.branch_name,
          daily.date,
          daily.unique_participants.toString(),
          daily.sessions_count.toString(),
          (Math.round(daily.total_minutes / 60 * 100) / 100).toString()
        ])
      })
    })

    const csvContent = csvRows
      .map(row => row.map(field => `"${field}"`).join(','))
      .join('\n')

    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `admin-report-${new Date().toISOString().slice(0, 10)}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Export error:', err)
    error.value = 'Failed to export report'
  } finally {
    isExporting.value = false
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Initialize
onMounted(() => {
  // Set default date range (last 30 days)
  const endDateValue = new Date()
  const startDateValue = new Date(endDateValue.getTime() - 30 * 24 * 60 * 60 * 1000)
  
  endDate.value = endDateValue.toISOString().slice(0, 10)
  startDate.value = startDateValue.toISOString().slice(0, 10)
  
  loadBranches()
})
</script>