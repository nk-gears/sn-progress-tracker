<template>
  <div class="content-card hover:shadow-xl transition-shadow cursor-pointer" @click="handleClick">
    <div class="flex justify-between items-start mb-3">
      <h3 class="font-bold text-lg text-purple-700">{{ centre.name }}</h3>
      <span v-if="centre.distance" class="text-sm text-blue-600 font-semibold whitespace-nowrap ml-2">
        📍 {{ formatDistance(centre.distance) }} {{ $t('centreCard.distance') }}
      </span>
    </div>

    <p class="text-gray-600 mb-2 text-sm">{{ centre.address }}</p>

    <p class="text-sm text-gray-500 mb-3">
      <strong>{{ $t('hero.eventDates') }}</strong><br />
      {{ $t('hero.eventTimings') }}
    </p>

    <div class="flex items-center justify-between">
      <a :href="`tel:${centre.phone}`" class="text-purple-600 font-semibold text-sm hover:text-purple-700" @click.stop>
        📞 {{ $t('centreCard.call') }}
      </a>

      <button
        v-if="centre.latitude && centre.longitude"
        @click.stop="openDirections"
        class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
        </svg>
        {{ $t('centreCard.getDirections') }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Centre } from '@/types'

interface Props {
  centre: Centre
}

const props = defineProps<Props>()

const formatDistance = (distance: number): string => {
  if (distance < 1) {
    return `${Math.round(distance * 1000)} m`
  }
  return `${distance.toFixed(1)} km`
}

const openDirections = () => {
  if (props.centre.latitude && props.centre.longitude) {
    const url = `https://www.google.com/maps/dir/?api=1&destination=${props.centre.latitude},${props.centre.longitude}`
    window.open(url, '_blank')
  }
}

const handleClick = () => {
  // Could emit event or navigate to centre details
  console.log('Centre clicked:', props.centre)
}
</script>
