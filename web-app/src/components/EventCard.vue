<template>
  <div class="event-card content-card hover:shadow-lg transition-shadow cursor-pointer" @click="showDetails = true">
    <!-- Event Header -->
    <div class="flex items-start justify-between mb-4">
      <h3 class="text-xl font-bold text-gray-800 flex-1">{{ event.title }}</h3>
      <div class="flex gap-2 ml-2">
        <span
          v-if="photoCount > 0"
          class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
          </svg>
          {{ photoCount }}
        </span>
        <span
          v-if="hasVideo"
          class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
          </svg>
          1
        </span>
      </div>
    </div>

    <!-- Event Date and Time -->
    <div class="flex gap-4 text-sm text-gray-600 mb-4">
      <div class="flex items-center gap-1">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
          <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z" />
        </svg>
        {{ formatDate(event.event_date) }}
      </div>
      <div class="flex items-center gap-1">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
          <path d="M11.99 5C6.47 5 2 8.13 2 12s4.47 7 9.99 7C17.52 19 22 15.87 22 12s-4.48-7-10.01-7zM12 18c-3.35 0-6-2.57-6-6s2.65-6 6-6 6 2.57 6 6-2.65 6-6 6zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 4 15.5 4 14 4.67 14 5.5 14.67 7 15.5 7z" />
        </svg>
        {{ event.event_time }}
      </div>
    </div>

    <!-- Event Description (Truncated) -->
    <p class="text-gray-700 text-sm mb-4 line-clamp-2">{{ event.description }}</p>

    <!-- Participants Info -->
    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
      <div class="text-sm text-gray-600">
        <span class="font-semibold">{{ $t('event.participants', 'Participants') }}:</span>
        {{ participantCount }}
      </div>
      <span class="text-xs text-gray-500">{{ formatTime(event.submitted_at) }}</span>
    </div>
  </div>

  <!-- Details Modal (Optional - for future enhancement) -->
  <!-- Can be expanded to show full details -->
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import type { EventReport } from '@/types'

interface Props {
  event: EventReport
}

defineProps<Props>()

const showDetails = ref(false)

const photoCount = computed(() => {
  if (!props.event.photo_urls) return 0
  return props.event.photo_urls.split(',').filter(url => url.trim()).length
})

const hasVideo = computed(() => {
  return !!props.event.video_url && props.event.video_url.trim().length > 0
})

const participantCount = computed(() => {
  return props.event.participants || 'N/A'
})

const formatDate = (dateString: string): string => {
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-IN', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch {
    return dateString
  }
}

const formatTime = (timeString: any): string => {
  try {
    const date = new Date(timeString)
    const now = new Date()
    const diffMs = now.getTime() - date.getTime()
    const diffMins = Math.floor(diffMs / 60000)
    const diffHours = Math.floor(diffMins / 60)
    const diffDays = Math.floor(diffHours / 24)

    if (diffMins < 60) return diffMins === 0 ? 'just now' : `${diffMins}m ago`
    if (diffHours < 24) return `${diffHours}h ago`
    if (diffDays < 7) return `${diffDays}d ago`

    return date.toLocaleDateString('en-IN')
  } catch {
    return 'Recently'
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.content-card {
  @apply bg-white rounded-lg p-6 shadow-md;
}

.content-card:hover {
  @apply shadow-lg;
}
</style>
