import { mockApi } from './mockApi'
import { realApi } from './realApi'
import type { 
  AuthResponse, 
  LoginCredentials,
  ParticipantResponse,
  ParticipantForm,
  SessionResponse,
  SessionCreateData,
  DashboardResponse,
  Session,
  Participant
} from '@/types'

// API Mode configuration
type ApiMode = 'mock' | 'real' | 'auto'

class ApiService {
  private currentMode: ApiMode = 'auto'
  
  constructor() {
    this.detectApiMode()
  }

  // Detect which API mode to use
  private detectApiMode(): void {
    // Check environment variable first
    const envMode = import.meta.env.VITE_API_MODE as ApiMode
    if (envMode && ['mock', 'real'].includes(envMode)) {
      this.currentMode = envMode
      return
    }

    // Check URL parameter
    const urlParams = new URLSearchParams(window.location.search)
    const urlMode = urlParams.get('api') as ApiMode
    if (urlMode && ['mock', 'real'].includes(urlMode)) {
      this.currentMode = urlMode
      localStorage.setItem('api_mode', urlMode)
      return
    }

    // Check localStorage
    const storedMode = localStorage.getItem('api_mode') as ApiMode
    if (storedMode && ['mock', 'real'].includes(storedMode)) {
      this.currentMode = storedMode
      return
    }

    // Auto-detect: try real API first, fallback to mock
    this.currentMode = 'auto'
  }

  // Get current API mode
  getMode(): ApiMode {
    return this.currentMode
  }

  // Set API mode
  setMode(mode: ApiMode): void {
    this.currentMode = mode
    localStorage.setItem('api_mode', mode)
    
    // Add to URL for sharing
    const url = new URL(window.location.href)
    if (mode === 'mock' || mode === 'real') {
      url.searchParams.set('api', mode)
    } else {
      url.searchParams.delete('api')
    }
    window.history.replaceState({}, '', url.toString())
  }

  // Get appropriate API instance
  private async getApi() {
    if (this.currentMode === 'mock') {
      return mockApi
    }
    
    if (this.currentMode === 'real') {
      return realApi
    }

    // Auto mode: try real API, fallback to mock on error
    return realApi
  }

  // Auto-fallback wrapper for API calls
  private async callWithFallback<T>(
    realCall: () => Promise<T>,
    mockCall: () => Promise<T>
  ): Promise<T> {
    if (this.currentMode === 'mock') {
      return mockCall()
    }

    if (this.currentMode === 'real') {
      return realCall()
    }

    // Auto mode: try real first, fallback to mock
    try {
      const result = await realCall()
      
      // Check if the result indicates failure (for auth/network errors)
      if (typeof result === 'object' && result !== null && 'success' in result && !result.success) {
        const errorMessage = 'message' in result ? result.message : ''
        if (errorMessage.includes('Network error') || errorMessage.includes('connection')) {
          console.warn('Real API failed, falling back to mock API')
          return mockCall()
        }
      }
      
      return result
    } catch (error) {
      console.warn('Real API failed, falling back to mock API:', error)
      return mockCall()
    }
  }

  // Authentication API
  auth = {
    login: async (credentials: LoginCredentials): Promise<AuthResponse> => {
      return this.callWithFallback(
        () => realApi.auth.login(credentials),
        () => mockApi.auth.login(credentials)
      )
    }
  }

  // Participants API
  participants = {
    getByBranch: async (branchId: number, search = ''): Promise<ParticipantResponse> => {
      return this.callWithFallback(
        () => realApi.participants.getByBranch(branchId, search),
        () => mockApi.participants.getByBranch(branchId, search)
      )
    },

    search: async (query: string, branchId: number): Promise<ParticipantResponse> => {
      return this.callWithFallback(
        () => realApi.participants.search(query, branchId),
        () => mockApi.participants.search(query, branchId)
      )
    },

    create: async (participantData: ParticipantForm): Promise<ParticipantResponse> => {
      return this.callWithFallback(
        () => realApi.participants.create(participantData),
        () => mockApi.participants.create(participantData)
      )
    },

    update: async (id: number, data: Partial<Participant>): Promise<ParticipantResponse> => {
      return this.callWithFallback(
        () => realApi.participants.update(id, data),
        () => mockApi.participants.update(id, data)
      )
    },

    findOrCreate: async (name: string, branchId: number, age?: number, gender?: string): Promise<ParticipantResponse> => {
      return this.callWithFallback(
        () => realApi.participants.findOrCreate(name, branchId, age, gender),
        () => mockApi.participants.findOrCreate(name, branchId, age, gender)
      )
    }
  }

  // Sessions API
  sessions = {
    getByDate: async (branchId: number, date: string): Promise<SessionResponse> => {
      return this.callWithFallback(
        () => realApi.sessions.getByDate(branchId, date),
        () => mockApi.sessions.getByDate(branchId, date)
      )
    },

    create: async (sessionData: SessionCreateData): Promise<SessionResponse> => {
      return this.callWithFallback(
        () => realApi.sessions.create(sessionData),
        () => mockApi.sessions.create(sessionData)
      )
    },

    update: async (id: number, sessionData: Partial<Session>): Promise<SessionResponse> => {
      return this.callWithFallback(
        () => realApi.sessions.update(id, sessionData),
        () => mockApi.sessions.update(id, sessionData)
      )
    },

    delete: async (id: number): Promise<SessionResponse> => {
      return this.callWithFallback(
        () => realApi.sessions.delete(id),
        () => mockApi.sessions.delete(id)
      )
    }
  }

  // Dashboard API
  dashboard = {
    getStats: async (branchId: number, month: string): Promise<DashboardResponse> => {
      return this.callWithFallback(
        () => realApi.dashboard.getStats(branchId, month),
        () => mockApi.dashboard.getStats(branchId, month)
      )
    }
  }

  // Utility methods
  isMockMode(): boolean {
    return this.currentMode === 'mock'
  }

  isRealMode(): boolean {
    return this.currentMode === 'real'
  }

  isAutoMode(): boolean {
    return this.currentMode === 'auto'
  }

  // Get status for display
  getStatus(): { mode: ApiMode; description: string; color: string } {
    switch (this.currentMode) {
      case 'mock':
        return {
          mode: 'mock',
          description: 'Using Mock API (Development Mode)',
          color: 'blue'
        }
      case 'real':
        return {
          mode: 'real',
          description: 'Using Real Backend API',
          color: 'green'
        }
      case 'auto':
        return {
          mode: 'auto',
          description: 'Auto Mode (Real API with Mock Fallback)',
          color: 'purple'
        }
      default:
        return {
          mode: 'auto',
          description: 'Auto Mode',
          color: 'gray'
        }
    }
  }
}

// Create and export singleton instance
export const apiService = new ApiService()

// Export for backward compatibility
export { apiService as mockApi }