<template>
  <div class="content-card hover:shadow-xl transition-shadow">
    <div class="flex justify-between items-start mb-3">
      <h3 class="font-bold text-lg text-purple-700">{{ centre.name }}</h3>
      <span v-if="centre.distance" class="text-sm text-blue-600 font-semibold whitespace-nowrap ml-2">
        📍 {{ formatDistance(centre.distance) }} {{ $t('centreCard.distance') }}
      </span>
    </div>

    <p class="text-gray-600 mb-2 text-sm">{{ centre.address }}</p>

    <p v-if="centre.campaign_details" class="text-sm text-gray-600 mb-3 whitespace-pre-line">
      {{ centre.campaign_details }}
    </p>

    <div class="flex flex-col gap-3">
      <!-- Join Now Button - Primary Action -->
      <button
        @click="handleJoinNow"
        class="w-full btn btn-primary text-center"
      >
        Register
      </button>

      <!-- Contact Actions -->
      <div class="flex items-center justify-between gap-2">
        <a :href="`tel:${centre.phone}`" class="text-purple-600 font-semibold text-sm hover:text-purple-700" @click.stop>
          📞 {{ $t('centreCard.call') }}
        </a>

        <button
          v-if="centre.latitude && centre.longitude"
          @click.stop="openDirections"
          title="Get Directions"
          class="transition hover:opacity-80"
        >
          <img src="/images/google-map.png" alt="Get Directions" class="w-5 h-5">
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Centre } from '@/types'

interface Props {
  centre: Centre
}

const props = defineProps<Props>()
const emit = defineEmits<{
  joinNow: [centre: Centre]
}>()

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

const handleJoinNow = () => {
  emit('joinNow', props.centre)
}
</script>
