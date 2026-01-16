/**
 * Application Configuration
 * Configurable API endpoints and settings
 */

export const config = {
  // Backend API Configuration
  api: {
    // Base URL for the backend API
    //baseUrl: 'https://happy-village.org/sn-progress',
    baseUrl: import.meta.env.VITE_API_BASE_URL,

    // Endpoints
    endpoints: {
      eventRegister: '/api/event-register',
      centerAddresses: '/api/center-addresses'
    }
  },

  // Google Maps Configuration
  maps: {
    apiKey: 'AIzaSyB80mIsZ2S4llsDA3rzssAqjvDGvZgFLv8'
  },

  // App Configuration
  app: {
    name: 'Shivanum Naanum - Meditation Tracker',
    version: '1.0.0'
  }
}

/**
 * Helper function to get full API URL
 * @param endpoint - The endpoint path (e.g., 'centerAddresses')
 * @returns Full URL for the API endpoint
 */
export function getApiUrl(endpoint: keyof typeof config.api.endpoints): string {
  const baseUrl = config.api.baseUrl.replace(/\/$/, '') // Remove trailing slash
  const endpointPath = config.api.endpoints[endpoint]
  return `${baseUrl}${endpointPath}`
}

/**
 * Example usage in components:
 *
 * import { config, getApiUrl } from '@/config'
 *
 * // Direct access to base URL
 * const baseUrl = config.api.baseUrl
 *
 * // Get full endpoint URL
 * const centerAddressesUrl = getApiUrl('centerAddresses')
 *
 * // Use in fetch
 * const response = await fetch(getApiUrl('centerAddresses'))
 */
