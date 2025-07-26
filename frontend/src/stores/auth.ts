import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User, Branch } from '@/types'
import { apiService } from '@/services/apiService'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const userBranches = ref<Branch[]>([])
  const currentBranch = ref<Branch | null>(null)
  const isAuthenticated = ref(false)
  
  // Getters
  const isLoggedIn = computed(() => isAuthenticated.value && !!user.value)
  const hasSelectedBranch = computed(() => !!currentBranch.value)
  const canAccessApp = computed(() => isLoggedIn.value && hasSelectedBranch.value)
  
  // Actions
  const login = async (mobile: string, password: string): Promise<boolean> => {
    try {
      const response = await apiService.auth.login({ mobile, password })
      
      if (response.success && response.user) {
        user.value = response.user
        userBranches.value = response.branches || []
        isAuthenticated.value = true
        
        // Store in localStorage for persistence
        localStorage.setItem('auth_user', JSON.stringify(response.user))
        localStorage.setItem('auth_branches', JSON.stringify(response.branches))
        localStorage.setItem('auth_token', response.token || 'mock_token')
        
        return true
      }
      
      return false
    } catch (error) {
      console.error('Login error:', error)
      return false
    }
  }
  
  const logout = () => {
    user.value = null
    userBranches.value = []
    currentBranch.value = null
    isAuthenticated.value = false
    
    // Clear localStorage
    localStorage.removeItem('auth_user')
    localStorage.removeItem('auth_branches')
    localStorage.removeItem('auth_token')
    localStorage.removeItem('selected_branch')
  }
  
  const selectBranch = (branch: Branch) => {
    currentBranch.value = branch
    
    // Store selected branch
    localStorage.setItem('selected_branch', JSON.stringify(branch))
  }
  
  const restoreSession = () => {
    try {
      const storedUser = localStorage.getItem('auth_user')
      const storedBranches = localStorage.getItem('auth_branches')
      const storedBranch = localStorage.getItem('selected_branch')
      const token = localStorage.getItem('auth_token')
      
      if (storedUser && storedBranches && token) {
        user.value = JSON.parse(storedUser)
        userBranches.value = JSON.parse(storedBranches)
        isAuthenticated.value = true
        
        if (storedBranch) {
          currentBranch.value = JSON.parse(storedBranch)
        }
        
        return true
      }
    } catch (error) {
      console.error('Session restore error:', error)
      logout()
    }
    
    return false
  }
  
  const clearBranchSelection = () => {
    currentBranch.value = null
    localStorage.removeItem('selected_branch')
  }
  
  return {
    // State
    user,
    userBranches,
    currentBranch,
    isAuthenticated,
    
    // Getters
    isLoggedIn,
    hasSelectedBranch,
    canAccessApp,
    
    // Actions
    login,
    logout,
    selectBranch,
    restoreSession,
    clearBranchSelection
  }
})