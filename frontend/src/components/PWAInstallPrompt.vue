<template>
  <div
    v-if="showInstallPrompt"
    class="fixed bottom-4 left-4 right-4 z-50 bg-white rounded-lg shadow-lg border border-gray-200 p-4"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <img src="/sn-logo.png" alt="App Icon" class="w-8 h-8 rounded">
        <div>
          <p class="text-sm font-medium text-gray-900">Install App</p>
          <p class="text-xs text-gray-500">Add to home screen for better experience</p>
        </div>
      </div>
      <div class="flex space-x-2">
        <button
          @click="dismissPrompt"
          class="px-3 py-1 text-xs text-gray-500 hover:text-gray-700"
        >
          Later
        </button>
        <button
          @click="installApp"
          class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Install
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface BeforeInstallPromptEvent extends Event {
  prompt(): Promise<void>
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>
}

const showInstallPrompt = ref(false)
let deferredPrompt: BeforeInstallPromptEvent | null = null

onMounted(() => {
  // Check if app is already installed
  if (window.matchMedia('(display-mode: standalone)').matches) {
    return
  }

  // Check if prompt was previously dismissed
  const dismissed = localStorage.getItem('pwa-install-dismissed')
  if (dismissed) {
    const dismissedTime = parseInt(dismissed)
    const dayInMs = 24 * 60 * 60 * 1000
    if (Date.now() - dismissedTime < dayInMs) {
      return
    }
  }

  // Listen for beforeinstallprompt event
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    deferredPrompt = e as BeforeInstallPromptEvent
    showInstallPrompt.value = true
  })
})

const installApp = async () => {
  if (!deferredPrompt) return

  try {
    await deferredPrompt.prompt()
    const choiceResult = await deferredPrompt.userChoice
    
    if (choiceResult.outcome === 'accepted') {
      console.log('User accepted the install prompt')
    }
    
    deferredPrompt = null
    showInstallPrompt.value = false
  } catch (error) {
    console.error('Error during app installation:', error)
  }
}

const dismissPrompt = () => {
  showInstallPrompt.value = false
  localStorage.setItem('pwa-install-dismissed', Date.now().toString())
}
</script>