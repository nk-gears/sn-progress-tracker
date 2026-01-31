/**
 * Campaign tracking composable
 * Handles detection and management of campaign source from URL query parameters
 */

export function useCampaign() {
  const CAMPAIGN_KEY = 'campaign_source'

  /**
   * Get campaign source from URL parameter or sessionStorage
   * Priority: URL parameter > sessionStorage
   */
  const getCampaignSource = (): string | null => {
    // Check URL ?s= parameter first
    const params = new URLSearchParams(window.location.search)
    const urlSource = params.get('s')

    if (urlSource) {
      // Store in sessionStorage for persistence during session
      sessionStorage.setItem(CAMPAIGN_KEY, urlSource)
      return urlSource
    }

    // Check sessionStorage for persistence
    return sessionStorage.getItem(CAMPAIGN_KEY)
  }

  /**
   * Track campaign-related events with Google Analytics
   */
  const trackCampaignEvent = (eventName: string, params?: any) => {
    if (window.gtag) {
      window.gtag('event', eventName, {
        campaign_source: getCampaignSource(),
        ...params
      })
    }
  }

  /**
   * Clear campaign source from session
   */
  const clearCampaign = () => {
    sessionStorage.removeItem(CAMPAIGN_KEY)
  }

  return {
    getCampaignSource,
    trackCampaignEvent,
    clearCampaign
  }
}
