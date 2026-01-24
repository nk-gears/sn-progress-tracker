/**
 * Composable for checking app version and notifying users of updates
 * Periodically checks version.json and alerts user if a new version is available
 */

import { ref, onMounted, onUnmounted } from 'vue'

interface VersionInfo {
  version: string
  timestamp: number
  deployed: string
}

const currentVersion = ref<string>('')
const newVersionAvailable = ref(false)
const showUpdateNotification = ref(false)
const isCheckingVersion = ref(false)

let checkInterval: ReturnType<typeof setInterval> | null = null

/**
 * Fetch version information from the server
 */
async function fetchLatestVersion(): Promise<VersionInfo | null> {
  try {
    isCheckingVersion.value = true

    // Add cache busting parameter to force fresh fetch
    const response = await fetch(`/version.json?t=${Date.now()}`, {
      cache: 'no-store',
      headers: {
        'Pragma': 'no-cache',
        'Cache-Control': 'no-cache, no-store, must-revalidate'
      }
    })

    if (!response.ok) {
      console.warn('Failed to fetch version.json:', response.status)
      return null
    }

    const versionInfo: VersionInfo = await response.json()
    console.log('📦 Latest version:', versionInfo)
    return versionInfo
  } catch (error) {
    console.error('Error fetching version:', error)
    return null
  } finally {
    isCheckingVersion.value = false
  }
}

/**
 * Check if a new version is available
 */
async function checkForUpdates(): Promise<void> {
  const latestVersion = await fetchLatestVersion()

  if (!latestVersion) {
    return
  }

  // Get stored version from localStorage
  const storedVersion = localStorage.getItem('appVersion')

  if (storedVersion && storedVersion !== latestVersion.version) {
    console.log(`🆕 New version available! Current: ${storedVersion}, Latest: ${latestVersion.version}`)
    currentVersion.value = latestVersion.version
    newVersionAvailable.value = true
    showUpdateNotification.value = true
  } else if (!storedVersion) {
    // First time, store the version
    localStorage.setItem('appVersion', latestVersion.version)
    currentVersion.value = latestVersion.version
    console.log('📦 App version stored:', latestVersion.version)
  }
}

/**
 * Refresh the page to load the new version
 */
function refreshPage(): void {
  console.log('🔄 Refreshing page to load new version...')
  // Use hard refresh to bypass cache
  window.location.href = window.location.href
}

/**
 * Initialize version checking
 * @param checkInterval - Interval in milliseconds to check for updates (default: 5 minutes)
 */
export function useVersionCheck(checkIntervalMs: number = 5 * 60 * 1000) {
  onMounted(async () => {
    // Check immediately on mount
    await checkForUpdates()

    // Then check periodically
    checkInterval = setInterval(() => {
      checkForUpdates()
    }, checkIntervalMs)

    console.log('📡 Version check initialized (interval:', checkIntervalMs / 1000, 'seconds)')
  })

  onUnmounted(() => {
    if (checkInterval) {
      clearInterval(checkInterval)
      console.log('📡 Version check stopped')
    }
  })

  return {
    currentVersion,
    newVersionAvailable,
    showUpdateNotification,
    isCheckingVersion,
    checkForUpdates,
    refreshPage
  }
}
