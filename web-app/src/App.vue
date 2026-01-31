<template>
  <div id="app">
    <!-- Version Update Notification -->
    <UpdateNotification
      :show="showUpdateNotification"
      @dismiss="handleDismissUpdate"
      @refresh="handleRefresh"
    />

    <!-- Campaign Registration Modal -->
    <CampaignModal
      :is-open="showCampaignModal"
      :campaign-source="campaignSource || 'unknown'"
      @close="handleCampaignClose"
      @success="handleCampaignSuccess"
    />

    <RouterView />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterView } from 'vue-router'
import UpdateNotification from '@/components/UpdateNotification.vue'
import CampaignModal from '@/components/CampaignModal.vue'
import { useVersionCheck } from '@/composables/useVersionCheck'
import { useCampaign } from '@/composables/useCampaign'

// Version checking
const { showUpdateNotification, refreshPage } = useVersionCheck(5 * 60 * 1000)

// Campaign tracking
const showCampaignModal = ref(false)
const campaignSource = ref<string | null>(null)
const { getCampaignSource, trackCampaignEvent } = useCampaign()

// Handle dismiss notification
const handleDismissUpdate = () => {
  console.log('📢 Update notification dismissed by user')
}

// Handle refresh
const handleRefresh = () => {
  console.log('🔄 User initiated refresh for new version')
  refreshPage()
}

// Handle campaign modal close
const handleCampaignClose = () => {
  showCampaignModal.value = false
}

// Handle campaign registration success
const handleCampaignSuccess = () => {
  showCampaignModal.value = false
}

// Detect campaign source on app load
onMounted(() => {
  // Only check URL query parameter for auto-opening modal
  // Don't check sessionStorage here - that should only be used for form submission
  const params = new URLSearchParams(window.location.search)
  const source = params.get('s')

  if (source) {
    campaignSource.value = source
    showCampaignModal.value = true

    // Track campaign entry with Google Analytics
    trackCampaignEvent('campaign_entry', {
      page_location: window.location.href
    })

    console.log('📊 Campaign detected:', source)
  }
})
</script>

<style>
/* App-specific styles */
</style>
