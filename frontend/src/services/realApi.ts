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

// Real API configuration
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/sn-progress/api'
const API_TIMEOUT = 10000

// Helper function for API requests
const apiRequest = async (endpoint: string, options: RequestInit = {}): Promise<Response> => {
  const url = `${API_BASE_URL}/${endpoint}`
  
  const config: RequestInit = {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  }

  // Add timeout
  const controller = new AbortController()
  const timeoutId = setTimeout(() => controller.abort(), API_TIMEOUT)
  config.signal = controller.signal

  try {
    const response = await fetch(url, config)
    clearTimeout(timeoutId)
    return response
  } catch (error) {
    clearTimeout(timeoutId)
    throw error
  }
}

// Real API implementation
export const realApi = {
  // Authentication endpoints
  auth: {
    async login(credentials: LoginCredentials): Promise<AuthResponse> {
      try {
        const response = await apiRequest('auth', {
          method: 'POST',
          body: JSON.stringify(credentials)
        })

        const data = await response.json()
        
        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Login failed'
          }
        }

        return data
      } catch (error) {
        console.error('Login API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    }
  },

  // Participant endpoints
  participants: {
    async getByBranch(branchId: number, search = ''): Promise<ParticipantResponse> {
      try {
        const params = new URLSearchParams({
          branch_id: branchId.toString(),
          ...(search && { search })
        })

        const response = await apiRequest(`members?${params}`)
        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to fetch participants'
          }
        }

        return data
      } catch (error) {
        console.error('Get participants API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async search(query: string, branchId: number): Promise<ParticipantResponse> {
      try {
        const params = new URLSearchParams({
          branch_id: branchId.toString(),
          search: query,
          action: 'search'
        })

        const response = await apiRequest(`members?${params}`)
        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to search participants'
          }
        }

        return data
      } catch (error) {
        console.error('Search participants API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async create(participantData: ParticipantForm): Promise<ParticipantResponse> {
      try {
        const response = await apiRequest('members', {
          method: 'POST',
          body: JSON.stringify(participantData)
        })

        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to create participant'
          }
        }

        return data
      } catch (error) {
        console.error('Create participant API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async update(id: number, data: Partial<Participant>): Promise<ParticipantResponse> {
      try {
        const response = await apiRequest('members', {
          method: 'PUT',
          body: JSON.stringify({ id, ...data })
        })

        const result = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: result.message || result.error || 'Failed to update participant'
          }
        }

        return result
      } catch (error) {
        console.error('Update participant API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async findOrCreate(name: string, branchId: number, age?: number, gender?: string): Promise<ParticipantResponse> {
      try {
        const response = await apiRequest('members', {
          method: 'POST',
          body: JSON.stringify({
            action: 'findOrCreate',
            name,
            branch_id: branchId,
            age: age || null,
            gender: gender || null
          })
        })

        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to find or create participant'
          }
        }

        return data
      } catch (error) {
        console.error('Find or create participant API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async getLastSession(participantId: number, branchId: number): Promise<any> {
      try {
        const params = new URLSearchParams({
          action: 'last_session',
          participant_id: participantId.toString(),
          branch_id: branchId.toString()
        })

        const response = await apiRequest(`members?${params}`)
        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to fetch last session'
          }
        }

        return data
      } catch (error) {
        console.error('Get last session API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async delete(id: number): Promise<ParticipantResponse> {
      try {
        const params = new URLSearchParams({
          id: id.toString()
        })

        const response = await apiRequest(`members?${params}`, {
          method: 'DELETE'
        })

        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to delete participant'
          }
        }

        return data
      } catch (error) {
        console.error('Delete participant API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    }
  },

  // Session endpoints
  sessions: {
    async getByDate(branchId: number, date: string): Promise<SessionResponse> {
      try {
        const params = new URLSearchParams({
          branch_id: branchId.toString(),
          date
        })

        const response = await apiRequest(`sessions?${params}`)
        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to fetch sessions'
          }
        }

        return data
      } catch (error) {
        console.error('Get sessions API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async create(sessionData: SessionCreateData): Promise<SessionResponse> {
      try {
        const response = await apiRequest('sessions', {
          method: 'POST',
          body: JSON.stringify(sessionData)
        })

        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to create session'
          }
        }

        return data
      } catch (error) {
        console.error('Create session API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async update(id: number, sessionData: Partial<Session>): Promise<SessionResponse> {
      try {
        const response = await apiRequest('sessions', {
          method: 'PUT',
          body: JSON.stringify({ id, ...sessionData })
        })

        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to update session'
          }
        }

        return data
      } catch (error) {
        console.error('Update session API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async delete(id: number): Promise<SessionResponse> {
      try {
        const params = new URLSearchParams({ id: id.toString() })
        
        const response = await apiRequest(`sessions?${params}`, {
          method: 'DELETE'
        })

        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to delete session'
          }
        }

        return data
      } catch (error) {
        console.error('Delete session API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    }
  },

  // Dashboard endpoints
  dashboard: {
    async getStats(branchId: number, month: string): Promise<DashboardResponse> {
      try {
        const params = new URLSearchParams({
          branch_id: branchId.toString(),
          month
        })

        // Use stats endpoint to avoid WordPress dashboard conflicts
        const response = await apiRequest(`stats?${params}`)
        const data = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: data.message || data.error || 'Failed to fetch dashboard data'
          }
        }

        return data
      } catch (error) {
        console.error('Dashboard API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    }
  },

  // Profile endpoints
  profile: {
    async updatePhone(data: { userId: number; newPhone: string; currentPassword: string }): Promise<AuthResponse> {
      try {
        const response = await apiRequest('profile/phone', {
          method: 'PUT',
          body: JSON.stringify(data)
        })

        const responseData = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: responseData.message || responseData.error || 'Failed to update phone number'
          }
        }

        return responseData
      } catch (error) {
        console.error('Update phone API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    },

    async updatePassword(data: { userId: number; currentPassword: string; newPassword: string }): Promise<AuthResponse> {
      try {
        const response = await apiRequest('profile/password', {
          method: 'PUT',
          body: JSON.stringify(data)
        })

        const responseData = await response.json()

        if (!response.ok) {
          return {
            success: false,
            message: responseData.message || responseData.error || 'Failed to update password'
          }
        }

        return responseData
      } catch (error) {
        console.error('Update password API error:', error)
        return {
          success: false,
          message: 'Network error. Please check your connection.'
        }
      }
    }
  }
}