// User and Authentication Types
export interface User {
  id: number
  mobile: string
  name: string
  created_at?: string
}

export interface Branch {
  id: number
  name: string
  location: string
}

export interface LoginCredentials {
  mobile: string
  password: string
}

export interface AuthResponse {
  success: boolean
  user?: User
  branches?: Branch[]
  token?: string
  message?: string
}

// Participant Types
export interface Participant {
  id: number
  name: string
  age: number | null
  gender: 'Male' | 'Female' | 'Other' | null
  branch_id: number
  created_at?: string
  session_count?: number
  total_hours?: number
}

export interface ParticipantForm {
  name: string
  age?: number | null
  gender?: 'Male' | 'Female' | 'Other' | null
  branch_id: number
}

export interface ParticipantResponse {
  success: boolean
  participant?: Participant
  participants?: Participant[]
  message?: string
}

// Session Types
export interface Session {
  id: number
  participant_id: number
  participant_name: string
  branch_id: number
  volunteer_id: number
  session_date: string
  start_time: string
  duration_minutes: number
  created_at: string
  updated_at?: string
}

export interface SessionForm {
  participant_name: string
  participant_age: number | null
  participant_gender: 'Male' | 'Female' | 'Other' | null
  session_date: string
}

export interface SessionCreateData {
  participant_id: number
  branch_id: number
  volunteer_id: number
  session_date: string
  start_time: string
  duration_minutes: number
}

export interface SessionResponse {
  success: boolean
  session?: Session
  sessions?: Session[]
  message?: string
}

// Dashboard Types
export interface DashboardSummary {
  total_participants: number
  total_hours: number
  total_sessions: number
  month: string
}

export interface TopParticipant {
  name: string
  age?: number | null
  gender?: string | null
  session_count: number
  total_minutes: number
}

export interface TimeDistribution {
  time_period: 'All' | 'Morning' | 'Afternoon' | 'Evening'
  session_count: number
  total_minutes: number
}

export interface DailyStat {
  session_date: string
  sessions_count: number
  unique_participants: number
  total_minutes: number
}

export interface DashboardData {
  summary: DashboardSummary
  top_participants: TopParticipant[]
  time_distribution: TimeDistribution[]
  daily_stats: DailyStat[]
}

export interface DashboardResponse {
  success: boolean
  data?: DashboardData
  message?: string
}

// Individual Hours Types
export type IndividualLocation = 'GP' |'Home' | 'Office'   | 'Other'

export interface IndividualHourEntry {
  id?: number
  participant_id: number
  branch_id: number
  entry_date: string
  total_minutes: number // 30,60,90,120,150
  location: IndividualLocation
}

export interface IndividualHoursResponse {
  success: boolean
  entries?: IndividualHourEntry[]
  message?: string
}

// Time Management Types
export interface TimePeriod {
  name: 'All' | 'Morning' | 'Afternoon' | 'Evening'
  start: string
  end: string
}

export interface TimeRange {
  start: string | null
  end: string | null
}

export interface MultipleTimeRanges {
  ranges: TimeRange[]
  totalDuration: number
}

// API Response Types
export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  error?: string
}

// Form Validation Types
export interface ValidationError {
  field: string
  message: string
}

export interface FormState {
  isValid: boolean
  errors: ValidationError[]
  isSubmitting: boolean
}

// App State Types
export interface AppConfig {
  apiMode: 'mock' | 'real' | 'auto'
  mockApi: {
    simulateNetworkDelay: boolean
    minDelay: number
    maxDelay: number
    enableLogging: boolean
  }
  realApi: {
    baseUrl: string
    timeout: number
    retryCount: number
  }
  ui: {
    themeColor: string
    enableAnimations: boolean
    toastDuration: number
    autoRefreshInterval: number
  }
}

// Route Types
export interface RouteGuard {
  requiresAuth?: boolean
  requiresBranch?: boolean
  redirectTo?: string
}

// Component Props Types
export interface BaseComponentProps {
  loading?: boolean
  disabled?: boolean
  class?: string
}

export interface ButtonProps extends BaseComponentProps {
  type?: 'button' | 'submit' | 'reset'
  variant?: 'primary' | 'secondary' | 'success' | 'danger' | 'warning' | 'info'
  size?: 'sm' | 'md' | 'lg'
}

export interface InputProps extends BaseComponentProps {
  modelValue?: string | number
  type?: string
  placeholder?: string
  required?: boolean
  readonly?: boolean
}

// Event Report Types
export interface EventReportForm {
  branch: string
  eventTitle: string
  eventDescription: string
  eventDate: string
  eventTime: string
  participants: string
  photos: File[]
  video: File | null
}

export interface EventReport {
  branch: string
  title: string
  description: string
  event_date: string
  event_time: string
  participants: string
  folder_url: string
  photo_urls: string
  video_url: string
  submitted_at: Date
}

// Touch and Selection Event Types
export interface TouchEventData {
  slot: string
  element: HTMLElement
  startTime: number
}

export interface SelectionEvent {
  start: string
  end: string
  duration: number
}
