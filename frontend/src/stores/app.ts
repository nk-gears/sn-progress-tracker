import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface Toast {
  show: boolean
  message: string
  type: 'success' | 'error' | 'info'
}

export const useAppStore = defineStore('app', () => {
  // Global loading state
  const isLoading = ref(false)
  
  // Toast notification state
  const toast = ref<Toast>({
    show: false,
    message: '',
    type: 'info'
  })
  
  // Current view/route state
  const currentView = ref('login')
  
  // Loading methods
  const setLoading = (loading: boolean) => {
    isLoading.value = loading
  }
  
  // Toast methods
  const showToast = (message: string, type: Toast['type'] = 'info', duration = 3000) => {
    toast.value = {
      show: true,
      message,
      type
    }
    
    // Auto-hide toast after duration
    setTimeout(() => {
      hideToast()
    }, duration)
  }
  
  const hideToast = () => {
    toast.value.show = false
  }
  
  const showSuccess = (message: string, duration?: number) => {
    showToast(message, 'success', duration)
  }
  
  const showError = (message: string, duration?: number) => {
    showToast(message, 'error', duration)
  }
  
  const showInfo = (message: string, duration?: number) => {
    showToast(message, 'info', duration)
  }
  
  // API simulation delay
  const simulateApiDelay = async (minMs = 200, maxMs = 500) => {
    const delay = Math.random() * (maxMs - minMs) + minMs
    return new Promise(resolve => setTimeout(resolve, delay))
  }
  
  return {
    // State
    isLoading,
    toast,
    currentView,
    
    // Actions
    setLoading,
    showToast,
    hideToast,
    showSuccess,
    showError,
    showInfo,
    simulateApiDelay
  }
})