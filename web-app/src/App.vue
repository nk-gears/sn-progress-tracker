<template>
  <div id="app">
    <!-- Version Update Notification -->
    <UpdateNotification
      :show="showUpdateNotification"
      @dismiss="handleDismissUpdate"
      @refresh="handleRefresh"
    />

    <RouterView />
  </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import UpdateNotification from '@/components/UpdateNotification.vue'
import { useVersionCheck } from '@/composables/useVersionCheck'

// Initialize version checking (checks every 5 minutes)
const { showUpdateNotification, refreshPage } = useVersionCheck(5 * 60 * 1000)

// Handle dismiss notification
const handleDismissUpdate = () => {
  console.log('📢 Update notification dismissed by user')
}

// Handle refresh
const handleRefresh = () => {
  console.log('🔄 User initiated refresh for new version')
  refreshPage()
}
</script>

<style>
/* App-specific styles */
</style>
