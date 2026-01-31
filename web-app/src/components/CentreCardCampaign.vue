<template>
  <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow bg-white">
    <div class="flex justify-between items-start gap-4">
      <div class="flex-1">
        <!-- Centre Name -->
        <h3 class="font-bold text-purple-700 text-sm md:text-base">{{ centre.name }}</h3>

        <!-- Address -->
        <p class="text-gray-600 text-xs md:text-sm mt-1">{{ centre.address }}</p>

        <!-- District and State -->
        <p class="text-gray-500 text-xs mt-1">{{ centre.district }}, {{ centre.state }}</p>

        <!-- Contact Phone -->
        <div v-if="centre.contact_no" class="flex items-center gap-2 mt-2 text-purple-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          </svg>
          <a :href="`tel:${centre.contact_no}`" class="font-semibold hover:underline text-xs md:text-sm">
            {{ centre.contact_no }}
          </a>
        </div>

        <!-- Distance (if available) -->
        <div v-if="distance" class="mt-2 text-blue-600 text-xs font-medium flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          {{ distance.toFixed(1) }} km away
        </div>
      </div>

      <!-- Select Button -->
      <button
        @click="$emit('select', centre)"
        class="px-4 py-2 bg-purple-600 text-white text-xs md:text-sm font-semibold rounded-lg hover:bg-purple-700 transition-colors whitespace-nowrap"
      >
        Select
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Centre } from '@/types'

interface Props {
  centre: Centre
  distance?: number
}

defineProps<Props>()

defineEmits<{
  select: [centre: Centre]
}>()
</script>

<style scoped>
/* Smooth transitions */
.transition {
  transition: all 0.2s ease;
}
</style>
