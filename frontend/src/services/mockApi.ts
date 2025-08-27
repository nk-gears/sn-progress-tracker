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

import {
  mockUsers,
  mockSessions,
  getUserBranches,
  getParticipantsByBranch,
  findParticipantByName,
  addParticipant,
  updateParticipant,
  addSession,
  updateSession,
  deleteSession,
  getSessionsByBranchAndDate
} from './mockData'

// Simulate network delay
const simulateDelay = async (minMs = 200, maxMs = 500): Promise<void> => {
  const delay = Math.random() * (maxMs - minMs) + minMs
  return new Promise(resolve => setTimeout(resolve, delay))
}

// Mock API implementation
export const mockApi = {
  // Authentication endpoints
  auth: {
    async login(credentials: LoginCredentials): Promise<AuthResponse> {
      await simulateDelay()
      
      // Validate credentials (for demo, password is always 'meditation123')
      const user = mockUsers.find(u => u.mobile === credentials.mobile)
      
      if (!user || credentials.password !== 'meditation123') {
        return {
          success: false,
          message: 'Invalid mobile number or password'
        }
      }
      
      const branches = getUserBranches(user.id)
      
      return {
        success: true,
        user,
        branches,
        token: `mock_token_${user.id}_${Date.now()}`
      }
    },
    
    async verify(token: string): Promise<AuthResponse> {
      await simulateDelay(100, 200)
      
      // Extract user ID from token (simplified for demo)
      const userId = token.includes('mock_token_') ? 
        parseInt(token.split('_')[2]) : null
      
      if (!userId) {
        return { success: false, message: 'Invalid token' }
      }
      
      const user = mockUsers.find(u => u.id === userId)
      if (!user) {
        return { success: false, message: 'User not found' }
      }
      
      const branches = getUserBranches(user.id)
      
      return {
        success: true,
        user,
        branches,
        token
      }
    }
  },
  
  // Participant endpoints
  participants: {
    async getByBranch(branchId: number, search = ''): Promise<ParticipantResponse> {
      await simulateDelay()
      
      const participants = getParticipantsByBranch(branchId, search)
      
      return {
        success: true,
        participants
      }
    },
    
    async search(query: string, branchId: number): Promise<ParticipantResponse> {
      await simulateDelay(100, 300)
      
      const participants = getParticipantsByBranch(branchId, query)
      
      return {
        success: true,
        participants
      }
    },
    
    async create(participantData: ParticipantForm): Promise<ParticipantResponse> {
      await simulateDelay()
      
      // Check if participant already exists
      const existing = findParticipantByName(participantData.name, participantData.branch_id)
      if (existing) {
        return {
          success: false,
          message: 'Participant with this name already exists in this branch'
        }
      }
      
      const participant = addParticipant(participantData)
      
      return {
        success: true,
        participant,
        message: 'Participant created successfully'
      }
    },
    
    async update(id: number, data: Partial<Participant>): Promise<ParticipantResponse> {
      await simulateDelay()
      
      const success = updateParticipant(id, data)
      
      if (!success) {
        return {
          success: false,
          message: 'Participant not found'
        }
      }
      
      return {
        success: true,
        message: 'Participant updated successfully'
      }
    },
    
    async findOrCreate(name: string, branchId: number, age?: number, gender?: string): Promise<ParticipantResponse> {
      await simulateDelay()
      
      // First try to find existing participant
      let participant = findParticipantByName(name, branchId)
      
      if (participant) {
        // Update with new info if provided and not already set
        if ((age && !participant.age) || (gender && !participant.gender)) {
          const updateData: Partial<Participant> = {}
          if (age && !participant.age) updateData.age = age
          if (gender && !participant.gender) updateData.gender = gender
          
          await this.update(participant.id, updateData)
          
          // Get updated participant
          participant = { ...participant, ...updateData }
        }
        
        return {
          success: true,
          participant
        }
      }
      
      // Create new participant
      return await this.create({
        name,
        age: age || null,
        gender: gender || null,
        branch_id: branchId
      })
    },

    async getLastSession(participantId: number, branchId: number): Promise<any> {
      await simulateDelay(100, 300)
      
      // Find the last session for this participant
      const participantSessions = mockSessions
        .filter(s => s.participant_id === participantId && s.branch_id === branchId)
        .sort((a, b) => {
          // Sort by date desc, then by time desc
          const dateCompare = b.session_date.localeCompare(a.session_date)
          if (dateCompare !== 0) return dateCompare
          return b.start_time.localeCompare(a.start_time)
        })
      
      const lastSession = participantSessions[0] || null
      
      return {
        success: true,
        last_session: lastSession ? {
          start_time: lastSession.start_time,
          duration_minutes: lastSession.duration_minutes,
          session_date: lastSession.session_date
        } : null
      }
    },

    async delete(id: number): Promise<ParticipantResponse> {
      await simulateDelay()
      
      // Import the deleteParticipant function from mockData
      const { deleteParticipant: deleteParticipantFromMock } = await import('./mockData')
      
      try {
        const success = deleteParticipantFromMock(id)
        
        if (success) {
          return {
            success: true,
            message: 'Participant and all related sessions deleted successfully'
          }
        } else {
          return {
            success: false,
            message: 'Participant not found'
          }
        }
      } catch (error) {
        return {
          success: false,
          message: 'Failed to delete participant'
        }
      }
    }
  },
  
  // Session endpoints
  sessions: {
    async getByDate(branchId: number, date: string): Promise<SessionResponse> {
      await simulateDelay()
      
      const sessions = getSessionsByBranchAndDate(branchId, date)
      
      return {
        success: true,
        sessions
      }
    },

    async getAll(branchId: number): Promise<SessionResponse> {
      await simulateDelay()
      
      const sessions = getSessionsByBranch(branchId)
      
      return {
        success: true,
        sessions
      }
    },
    
    async create(sessionData: SessionCreateData): Promise<SessionResponse> {
      await simulateDelay()
      
      // Find participant name
      const participants = getParticipantsByBranch(sessionData.branch_id)
      const participant = participants.find(p => p.id === sessionData.participant_id)
      
      if (!participant) {
        return {
          success: false,
          message: 'Participant not found'
        }
      }
      
      // Check for duplicate session (same participant, date, and start time)
      const existingSession = mockSessions.find(s => 
        s.participant_id === sessionData.participant_id &&
        s.session_date === sessionData.session_date &&
        s.start_time === sessionData.start_time
      )
      
      if (existingSession) {
        return {
          success: false,
          message: `Session already exists for ${participant.name} on ${sessionData.session_date} at ${sessionData.start_time}`,
          error_type: 'duplicate_session'
        }
      }
      
      const session = addSession({
        ...sessionData,
        participant_name: participant.name
      })
      
      return {
        success: true,
        session,
        message: 'Session recorded successfully'
      }
    },
    
    async update(id: number, sessionData: Partial<Session>): Promise<SessionResponse> {
      await simulateDelay()
      
      const success = updateSession(id, sessionData)
      
      if (!success) {
        return {
          success: false,
          message: 'Session not found'
        }
      }
      
      return {
        success: true,
        message: 'Session updated successfully'
      }
    },
    
    async delete(id: number): Promise<SessionResponse> {
      await simulateDelay()
      
      const success = deleteSession(id)
      
      if (!success) {
        return {
          success: false,
          message: 'Session not found'
        }
      }
      
      return {
        success: true,
        message: 'Session deleted successfully'
      }
    }
  },
  
  // Dashboard endpoints
  dashboard: {
    async getStats(branchId: number, month: string): Promise<DashboardResponse> {
      await simulateDelay()
      
      const [year, monthNum] = month.split('-').map(Number)
      const sessionsInMonth = mockSessions.filter(s => {
        const sessionDate = new Date(s.session_date)
        return s.branch_id === branchId && 
               sessionDate.getFullYear() === year && 
               sessionDate.getMonth() === monthNum - 1
      })
      
      // Summary statistics
      const uniqueParticipants = new Set(sessionsInMonth.map(s => s.participant_id))
      const totalMinutes = sessionsInMonth.reduce((sum, s) => sum + s.duration_minutes, 0)
      const totalHours = Math.round(totalMinutes / 60 * 100) / 100
      
      // Top participants
      const participantStats: { [key: number]: any } = {}
      sessionsInMonth.forEach(s => {
        if (!participantStats[s.participant_id]) {
          participantStats[s.participant_id] = {
            name: s.participant_name,
            session_count: 0,
            total_minutes: 0
          }
        }
        participantStats[s.participant_id].session_count++
        participantStats[s.participant_id].total_minutes += s.duration_minutes
      })
      
      const topParticipants = Object.values(participantStats)
        .sort((a: any, b: any) => b.session_count - a.session_count)
        .slice(0, 10)
      
      // Time distribution
      const timeDistribution = [
        { time_period: 'Morning' as const, session_count: 0, total_minutes: 0 },
        { time_period: 'Afternoon' as const, session_count: 0, total_minutes: 0 },
        { time_period: 'Evening' as const, session_count: 0, total_minutes: 0 }
      ]
      
      sessionsInMonth.forEach(s => {
        const hour = parseInt(s.start_time.split(':')[0])
        let period: 'Morning' | 'Afternoon' | 'Evening'
        
        if (hour < 12) period = 'Morning'
        else if (hour < 17) period = 'Afternoon'
        else period = 'Evening'
        
        const dist = timeDistribution.find(d => d.time_period === period)!
        dist.session_count++
        dist.total_minutes += s.duration_minutes
      })
      
      // Daily stats
      const dailyStatsMap: { [key: string]: any } = {}
      sessionsInMonth.forEach(s => {
        if (!dailyStatsMap[s.session_date]) {
          dailyStatsMap[s.session_date] = {
            session_date: s.session_date,
            sessions_count: 0,
            unique_participants: new Set(),
            total_minutes: 0
          }
        }
        dailyStatsMap[s.session_date].sessions_count++
        dailyStatsMap[s.session_date].unique_participants.add(s.participant_id)
        dailyStatsMap[s.session_date].total_minutes += s.duration_minutes
      })
      
      const dailyStats = Object.values(dailyStatsMap)
        .map((d: any) => ({
          ...d,
          unique_participants: d.unique_participants.size
        }))
        .sort((a: any, b: any) => b.session_date.localeCompare(a.session_date))
      
      return {
        success: true,
        data: {
          summary: {
            total_participants: uniqueParticipants.size,
            total_hours: totalHours,
            total_sessions: sessionsInMonth.length,
            month
          },
          top_participants: topParticipants,
          time_distribution: timeDistribution.filter(d => d.session_count > 0),
          daily_stats: dailyStats
        }
      }
    }
  },
  
  // Profile endpoints
  profile: {
    async updatePhone(data: { userId: number; newPhone: string; currentPassword: string }): Promise<AuthResponse> {
      await simulateDelay()
      
      const user = mockUsers.find(u => u.id === data.userId)
      
      if (!user) {
        return {
          success: false,
          message: 'User not found'
        }
      }
      
      // Check if phone is already in use
      const existingUser = mockUsers.find(u => u.mobile === data.newPhone && u.id !== data.userId)
      if (existingUser) {
        return {
          success: false,
          message: 'Phone number already in use'
        }
      }
      
      // Validate phone format
      if (!/^\d{10}$/.test(data.newPhone)) {
        return {
          success: false,
          message: 'Phone number must be 10 digits'
        }
      }
      
      // Update user phone
      user.mobile = data.newPhone
      
      return {
        success: true,
        user,
        message: 'Phone number updated successfully'
      }
    },
    
    async updatePassword(data: { userId: number; currentPassword: string; newPassword: string }): Promise<AuthResponse> {
      await simulateDelay()
      
      const user = mockUsers.find(u => u.id === data.userId)
      
      if (!user) {
        return {
          success: false,
          message: 'User not found'
        }
      }
      
      // Validate new password
      if (data.newPassword.length < 6) {
        return {
          success: false,
          message: 'New password must be at least 6 characters long'
        }
      }
      
      // For mock API, we don't actually store or verify passwords
      // In real implementation, you would verify currentPassword and hash newPassword
      
      return {
        success: true,
        message: 'Password updated successfully'
      }
    }
  }
}