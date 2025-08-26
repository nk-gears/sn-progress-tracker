import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Session, SessionForm, DashboardData, MultipleTimeRanges } from '@/types'
import { apiService } from '@/services/apiService'
import { useAuthStore } from './auth'

export const useSessionsStore = defineStore('sessions', () => {
  // State
  const sessions = ref<Session[]>([])
  const todaySessions = ref<Session[]>([])
  const dashboardData = ref<DashboardData | null>(null)
  const isLoading = ref(false)
  const editingSession = ref<Session | null>(null)
  
  // Form state
  const sessionForm = ref<SessionForm>({
    participant_name: '',
    participant_age: null,
    participant_gender: null,
    session_date: new Date().toISOString().slice(0, 10)
  })
  
  // Time selection state
  const selectedTimePeriod = ref<'All' | 'Morning' | 'Afternoon' | 'Evening'>('Morning')
  const selectedTimeRanges = ref<MultipleTimeRanges>({
    ranges: [],
    totalDuration: 0
  })
  const isSelecting = ref(false)
  
  const timePeriods = computed(() => [
    { name: 'Morning', start: '06:00', end: '12:00' },
    { name: 'Afternoon', start: '12:00', end: '17:00' },
    { name: 'Evening', start: '17:00', end: '22:00' },
    { name: 'All', start: '06:00', end: '22:00' }
  ])
  
  const filteredTimeSlots = computed(() => {
    const period = timePeriods.value.find(p => p.name === selectedTimePeriod.value)
    if (!period) return []
    
    const slots: string[] = []
    const startMinutes = timeToMinutes(period.start)
    const endMinutes = timeToMinutes(period.end)
    
    for (let minutes = startMinutes; minutes < endMinutes; minutes += 30) {
      slots.push(minutesToTime(minutes))
    }
    
    return slots
  })
  
  const formattedMonth = computed(() => {
    const today = new Date()
    return today.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
  })
  
  // Helper functions
  const timeToMinutes = (time: string): number => {
    const [hours, minutes] = time.split(':').map(Number)
    return hours * 60 + minutes
  }
  
  const minutesToTime = (minutes: number): string => {
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`
  }
  
  const formatTime = (time: string): string => {
    const [hours, minutes] = time.split(':')
    const hour = parseInt(hours)
    const ampm = hour >= 12 ? 'PM' : 'AM'
    const displayHour = hour % 12 || 12
    return `${displayHour}:${minutes} ${ampm}`
  }
  
  const formatTimestamp = (timestamp: string): string => {
    return new Date(timestamp).toLocaleTimeString('en-US', { 
      hour: '2-digit', 
      minute: '2-digit',
      timeZone: 'Asia/Kolkata'
    })
  }
  
  // Actions
  const loadTodaySessions = async () => {
    const authStore = useAuthStore()
    const branchId = authStore.currentBranch?.id
    
    if (!branchId) return
    
    isLoading.value = true
    try {
      const today = new Date().toISOString().slice(0, 10)
      const response = await apiService.sessions.getByDate(branchId, today)
      
      if (response.success) {
        todaySessions.value = response.sessions || []
      }
    } catch (error) {
      console.error('Error loading today sessions:', error)
    } finally {
      isLoading.value = false
    }
  }
  
  const loadDashboardData = async (month?: string) => {
    const authStore = useAuthStore()
    const branchId = authStore.currentBranch?.id
    
    if (!branchId) return
    
    const targetMonth = month || new Date().toISOString().slice(0, 7)
    
    isLoading.value = true
    try {
      const response = await apiService.dashboard.getStats(branchId, targetMonth)
      
      if (response.success) {
        dashboardData.value = response.data || null
      }
    } catch (error) {
      console.error('Error loading dashboard data:', error)
    } finally {
      isLoading.value = false
    }
  }
  
  const createSession = async (sessionData: Omit<Session, 'id' | 'created_at'>): Promise<{ success: boolean; error?: string; errorType?: string }> => {
    try {
      const response = await apiService.sessions.create(sessionData)
      
      if (response.success && response.session) {
        // Add to today's sessions if it's today
        const today = new Date().toISOString().slice(0, 10)
        if (sessionData.session_date === today) {
          todaySessions.value.unshift(response.session)
        }
        
        // Refresh dashboard data to update total hours
        await loadDashboardData()
        
        return { success: true }
      } else {
        return { 
          success: false, 
          error: response.message || 'Failed to create session',
          errorType: response.error_type
        }
      }
    } catch (error) {
      console.error('Error creating session:', error)
      return { 
        success: false, 
        error: 'Network error occurred while creating session'
      }
    }
  }
  
  const updateSession = async (id: number, sessionData: Partial<Session>): Promise<boolean> => {
    try {
      const response = await apiService.sessions.update(id, sessionData)
      
      if (response.success) {
        // Update in today's sessions
        const index = todaySessions.value.findIndex(s => s.id === id)
        if (index >= 0) {
          todaySessions.value[index] = { ...todaySessions.value[index], ...sessionData }
        }
        
        // Refresh dashboard data to update total hours
        await loadDashboardData()
        
        return true
      }
    } catch (error) {
      console.error('Error updating session:', error)
    }
    
    return false
  }
  
  const deleteSession = async (id: number): Promise<boolean> => {
    try {
      const response = await apiService.sessions.delete(id)
      
      if (response.success) {
        // Remove from today's sessions
        const index = todaySessions.value.findIndex(s => s.id === id)
        if (index >= 0) {
          todaySessions.value.splice(index, 1)
        }
        
        // Refresh dashboard data to update total hours
        await loadDashboardData()
        
        return true
      }
    } catch (error) {
      console.error('Error deleting session:', error)
    }
    
    return false
  }
  
  const startEdit = (session: Session) => {
    editingSession.value = { ...session }
    sessionForm.value = {
      participant_name: session.participant_name,
      participant_age: null, // Will be set by participant store
      participant_gender: null, // Will be set by participant store
      session_date: session.session_date
    }
    
    // Set time range as single range for editing
    const startMinutes = timeToMinutes(session.start_time)
    const endMinutes = startMinutes + session.duration_minutes
    const endTime = minutesToTime(endMinutes)
    
    selectedTimeRanges.value = {
      ranges: [{ start: session.start_time, end: endTime }],
      totalDuration: session.duration_minutes
    }
    
    // Set time period based on start time (default to All for wider range)
    selectedTimePeriod.value = 'All'
  }
  
  const cancelEdit = () => {
    editingSession.value = null
    clearForm()
  }
  
  const clearForm = (preserveDate = false) => {
    const currentDate = sessionForm.value.session_date
    sessionForm.value = {
      participant_name: '',
      participant_age: null,
      participant_gender: null,
      session_date: preserveDate ? currentDate : new Date().toISOString().slice(0, 10)
    }
    selectedTimeRanges.value = { ranges: [], totalDuration: 0 }
    isSelecting.value = false
    editingSession.value = null
  }
  
  const setTimePeriod = (period: 'All' | 'Morning' | 'Afternoon' | 'Evening') => {
    selectedTimePeriod.value = period
    // Keep existing selections when changing time period
  }
  
  const selectTimeRange = (start: string, end: string) => {
    const startMinutes = timeToMinutes(start)
    const endMinutes = timeToMinutes(end)
    const duration = endMinutes - startMinutes
    
    selectedTimeRanges.value = {
      ranges: [{ start, end }],
      totalDuration: duration
    }
  }
  
  const resetStore = () => {
    sessions.value = []
    todaySessions.value = []
    dashboardData.value = null
    isLoading.value = false
    editingSession.value = null
    clearForm()
  }
  
  return {
    // State
    sessions,
    todaySessions,
    dashboardData,
    isLoading,
    editingSession,
    sessionForm,
    selectedTimePeriod,
    selectedTimeRanges,
    isSelecting,
    
    // Getters
    timePeriods,
    filteredTimeSlots,
    formattedMonth,
    
    // Helper functions
    formatTime,
    formatTimestamp,
    timeToMinutes,
    minutesToTime,
    
    // Actions
    loadTodaySessions,
    loadDashboardData,
    createSession,
    updateSession,
    deleteSession,
    startEdit,
    cancelEdit,
    clearForm,
    setTimePeriod,
    selectTimeRange,
    resetStore
  }
})