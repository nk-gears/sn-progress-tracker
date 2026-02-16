// Centre and Location Types
export interface Centre {
  id: number
  center_code?: string
  name: string
  address: string
  district: string
  state: string
  latitude?: number
  longitude?: number
  latitude_longitude?: string // Combined lat,lng from API (e.g., "12.34,56.78")
  phone?: string
  contact_no?: string
  campaign_details?: string
  distance?: number
}

export interface State {
  id: number
  name: string
  districts: District[]
}

export interface District {
  id: number
  name: string
  state_id: number
  centres: Centre[]
}

// WhatsApp Join Form Types
export interface WhatsAppJoinForm {
  name: string
  mobile: string
  centre_id: number
}

// Event Information Types
export interface EventInfo {
  title: string
  dates: string
  timings: string
  description: string
}

// FAQ Types
export interface FAQ {
  question: string
  answer: string
  isOpen: boolean
}

// User Location Types
export interface UserLocation {
  latitude: number
  longitude: number
  address: string
}

// Campaign Registration Types
export interface CampaignRegistration {
  name: string
  mobile: string
  center_code: string
  number_of_people: number
  campaign_source: string
}

// API Response Types
export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  error?: string
}

// Event Report Types
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
