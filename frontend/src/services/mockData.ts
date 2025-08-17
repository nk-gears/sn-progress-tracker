import type { User, Branch, Participant, Session } from '@/types'

// Mock Users (volunteers)
export const mockUsers: User[] = [
  { id: 1, mobile: '9283181228', name: 'Ramesh Kumar' },
  { id: 2, mobile: '9876543211', name: 'Priya Devi' },
  { id: 3, mobile: '9876543212', name: 'Suresh Babu' },
  { id: 4, mobile: '9876543213', name: 'Lakshmi Raman' },
  { id: 5, mobile: '9876543214', name: 'Venkat Raja' }
]

// Mock Branches
export const mockBranches: Branch[] = [
  { id: 1, name: 'Chennai Central Branch2', location: 'Chennai, Tamil Nadu' },
  { id: 2, name: 'Coimbatore Branch', location: 'Coimbatore, Tamil Nadu' },
  { id: 3, name: 'Madurai Branch', location: 'Madurai, Tamil Nadu' },
  { id: 4, name: 'Salem Branch', location: 'Salem, Tamil Nadu' },
  { id: 5, name: 'Trichy Branch', location: 'Tiruchirappalli, Tamil Nadu' }
]

// User-Branch mapping
export const mockUserBranches = [
  { user_id: 1, branch_id: 1 },
  { user_id: 1, branch_id: 2 },
  { user_id: 2, branch_id: 2 },
  { user_id: 3, branch_id: 3 },
  { user_id: 4, branch_id: 4 },
  { user_id: 5, branch_id: 5 }
]

// Mock Participants
export const mockParticipants: Participant[] = [
  { id: 1, name: 'Anand Krishna', age: 35, gender: 'Male', branch_id: 1 },
  { id: 2, name: 'Meera Devi', age: 42, gender: 'Female', branch_id: 1 },
  { id: 3, name: 'Rajesh Kumar', age: 28, gender: 'Male', branch_id: 1 },
  { id: 4, name: 'Sita Lakshmi', age: null, gender: 'Female', branch_id: 2 },
  { id: 5, name: 'Govind Das', age: 55, gender: 'Male', branch_id: 2 },
  { id: 6, name: 'Kamala Devi', age: 38, gender: 'Female', branch_id: 3 },
  { id: 7, name: 'Arjun Singh', age: 31, gender: 'Male', branch_id: 3 },
  { id: 8, name: 'Radha Krishna', age: 29, gender: 'Other', branch_id: 4 },
  { id: 9, name: 'Shiva Kumar', age: 45, gender: 'Male', branch_id: 5 },
  { id: 10, name: 'Parvati Devi', age: null, gender: null, branch_id: 5 }
]

// Generate mock sessions for current month
export const generateMockSessions = (): Session[] => {
  const sessions: Session[] = []
  const today = new Date()
  const currentMonth = today.getMonth()
  const currentYear = today.getFullYear()
  
  // Generate sessions for the current month
  for (let day = 1; day <= today.getDate(); day++) {
    const sessionDate = new Date(currentYear, currentMonth, day)
    const dateStr = sessionDate.toISOString().split('T')[0]
    
    // Random number of sessions per day (0-5)
    const numSessions = Math.floor(Math.random() * 6)
    
    for (let i = 0; i < numSessions; i++) {
      const participant = mockParticipants[Math.floor(Math.random() * mockParticipants.length)]
      const startHour = 7 + Math.floor(Math.random() * 15) // 7 AM to 10 PM
      const startMinute = Math.random() > 0.5 ? 0 : 30
      const duration = [30, 60, 90, 120][Math.floor(Math.random() * 4)]
      
      sessions.push({
        id: sessions.length + 1,
        participant_id: participant.id,
        participant_name: participant.name,
        branch_id: participant.branch_id,
        volunteer_id: mockUserBranches.find(ub => ub.branch_id === participant.branch_id)?.user_id || 1,
        session_date: dateStr,
        start_time: `${startHour.toString().padStart(2, '0')}:${startMinute.toString().padStart(2, '0')}`,
        duration_minutes: duration,
        created_at: new Date(sessionDate.getTime() + Math.random() * 24 * 60 * 60 * 1000).toISOString()
      })
    }
  }
  
  return sessions
}

// Initialize mock sessions
export let mockSessions = generateMockSessions()

// Helper function to reset mock data
export const resetMockData = () => {
  mockSessions = generateMockSessions()
}

// Helper functions for data manipulation
export const getNextId = (data: any[]): number => {
  return Math.max(...data.map(item => item.id), 0) + 1
}

export const getUserBranches = (userId: number): Branch[] => {
  const userBranchIds = mockUserBranches
    .filter(ub => ub.user_id === userId)
    .map(ub => ub.branch_id)
  
  return mockBranches.filter(b => userBranchIds.includes(b.id))
}

export const getParticipantsByBranch = (branchId: number, search = ''): Participant[] => {
  let participants = mockParticipants.filter(p => p.branch_id === branchId)
  
  if (search) {
    participants = participants.filter(p => 
      p.name.toLowerCase().includes(search.toLowerCase())
    )
  }
  
  return participants
}

export const findParticipantByName = (name: string, branchId: number): Participant | undefined => {
  return mockParticipants.find(p => 
    p.name.toLowerCase() === name.toLowerCase() && p.branch_id === branchId
  )
}

export const addParticipant = (participant: Omit<Participant, 'id'>): Participant => {
  const newParticipant: Participant = {
    ...participant,
    id: getNextId(mockParticipants)
  }
  
  mockParticipants.push(newParticipant)
  return newParticipant
}

export const updateParticipant = (id: number, data: Partial<Participant>): boolean => {
  const index = mockParticipants.findIndex(p => p.id === id)
  if (index >= 0) {
    mockParticipants[index] = { ...mockParticipants[index], ...data }
    return true
  }
  return false
}

export const addSession = (session: Omit<Session, 'id' | 'created_at'>): Session => {
  const newSession: Session = {
    ...session,
    id: getNextId(mockSessions),
    created_at: new Date().toISOString()
  }
  
  mockSessions.push(newSession)
  return newSession
}

export const updateSession = (id: number, data: Partial<Session>): boolean => {
  const index = mockSessions.findIndex(s => s.id === id)
  if (index >= 0) {
    mockSessions[index] = { ...mockSessions[index], ...data }
    return true
  }
  return false
}

export const deleteSession = (id: number): boolean => {
  const index = mockSessions.findIndex(s => s.id === id)
  if (index >= 0) {
    mockSessions.splice(index, 1)
    return true
  }
  return false
}

export const getSessionsByBranchAndDate = (branchId: number, date: string): Session[] => {
  return mockSessions
    .filter(s => s.branch_id === branchId && s.session_date === date)
    .sort((a, b) => b.start_time.localeCompare(a.start_time))
}