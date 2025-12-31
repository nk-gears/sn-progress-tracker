// Centre and Location Types
export interface Centre {
  id: number
  name: string
  address: string
  district: string
  state: string
  latitude: number
  longitude: number
  phone: string
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

// API Response Types
export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  error?: string
}
