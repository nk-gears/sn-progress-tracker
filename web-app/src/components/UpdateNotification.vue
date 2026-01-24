<template>
  <Teleport to="body">
    <!-- Update notification banner -->
    <Transition name="slide-down">
      <div
        v-if="showNotification"
        class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg"
      >
        <div class="container mx-auto px-4 py-4">
          <div class="flex items-center justify-between gap-4">
            <!-- Message -->
            <div class="flex items-center gap-3 flex-1">
              <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="font-semibold">{{ $t('updateNotification.title') || '✨ New version available!' }}</p>
                <p class="text-sm text-blue-100">
                  {{ $t('updateNotification.message') || 'A new version of the app has been released.' }}
                </p>
              </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2 flex-shrink-0">
              <button
                @click="dismissNotification"
                class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"
              >
                {{ $t('updateNotification.later') || 'Later' }}
              </button>
              <button
                @click="handleRefresh"
                :disabled="isRefreshing"
                class="px-4 py-2 rounded bg-white hover:bg-gray-100 text-blue-600 text-sm font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <svg v-if="!isRefreshing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ $t('updateNotification.refresh') || 'Refresh' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  show: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{
  dismiss: []
  refresh: []
}>()

const isRefreshing = ref(false)

const showNotification = ref(props.show)

// Watch for prop changes
import { watch } from 'vue'
watch(() => props.show, (newVal) => {
  showNotification.value = newVal
})

const dismissNotification = () => {
  showNotification.value = false
  emit('dismiss')
}

const handleRefresh = async () => {
  isRefreshing.value = true
  emit('refresh')
  // Give a moment for animation before reload
  await new Promise(resolve => setTimeout(resolve, 500))
}
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: transform 0.3s ease;
}

.slide-down-enter-from {
  transform: translateY(-100%);
}

.slide-down-leave-to {
  transform: translateY(-100%);
}
</style>
