<template>
  <div class="space-y-4">
    <!-- Time Period Selection -->
    <div class="grid grid-cols-4 gap-2">
      <button
        v-for="period in timePeriods"
        :key="period.name"
        @click="selectTimePeriod(period.name)"
        class="py-3 px-4 rounded-xl font-medium text-sm touch-target transition-all duration-200"
        :class="selectedTimePeriod === period.name
          ? 'bg-primary text-white shadow-lg'
          : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
      >
        <span>{{ period.name }}</span>
      </button>
    </div>

    <!-- Time Slots Grid -->
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <h4 class="text-sm font-medium text-gray-700">Select meditation time</h4>
        <div v-if="selectedDuration > 0" class="text-sm text-primary font-medium">
          {{ selectedDuration }} minutes selected
        </div>
      </div>

      <!-- Touch instructions -->
      <div class="text-xs text-gray-500 bg-blue-50 p-3 rounded-lg border border-blue-200">
        <div class="flex items-center">
          <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-blue-700">Touch and drag to select time range. Minimum 30 minutes.</span>
        </div>
      </div>

      <!-- Time slots -->
      <div 
        class="grid grid-cols-6 gap-1 p-4 bg-gray-50 rounded-xl select-none"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove" 
        @touchend="handleTouchEnd"
        @mousedown="handleMouseStart"
        @mousemove="handleMouseMove"
        @mouseup="handleMouseEnd"
        @mouseleave="handleMouseEnd"
      >
        <div
          v-for="slot in filteredTimeSlots"
          :key="slot"
          :data-time-slot="slot"
          class="time-slot aspect-square flex items-center justify-center text-xs rounded-lg cursor-pointer transition-all duration-150 touch-target font-medium"
          :class="getSlotClasses(slot)"
        >
          {{ formatTimeSlot(slot) }}
        </div>
      </div>
    </div>

    <!-- Quick duration buttons -->
    <div class="space-y-2">
      <h4 class="text-sm font-medium text-gray-700">Quick select duration</h4>
      <div class="grid grid-cols-4 gap-2">
        <button
          v-for="duration in [30, 60, 90, 120]"
          :key="duration"
          @click="quickSelectDuration(duration)"
          class="py-2 px-4 rounded-lg text-sm font-medium touch-target transition-colors"
          :class="selectedDuration === duration
            ? 'bg-primary text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
        >
          {{ duration }}min
        </button>
      </div>
    </div>

    <!-- Selected time display -->
    <div v-if="selectedTimeRange.start && selectedTimeRange.end" class="card p-4 bg-green-50 border-green-200">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-medium text-green-800">Selected Time</div>
          <div class="text-sm text-green-700">
            {{ formatTime(selectedTimeRange.start) }} - {{ formatTime(selectedTimeRange.end) }}
          </div>
        </div>
        <div class="text-right">
          <div class="font-semibold text-green-800">{{ selectedDuration }}min</div>
          <div class="text-xs text-green-600">{{ Math.round(selectedDuration / 60 * 100) / 100 }}h</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useSessionsStore } from '@/stores/sessions'
import type { TimeRange } from '@/types'

// Props and emits
interface Props {
  modelValue: TimeRange
}

interface Emits {
  (e: 'update:modelValue', value: TimeRange): void
  (e: 'duration-changed', duration: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const sessionsStore = useSessionsStore()

// Local state for touch/mouse handling
const isSelecting = ref(false)
const startSlot = ref<string | null>(null)
const currentSlot = ref<string | null>(null)

// Computed properties from store
const timePeriods = computed(() => sessionsStore.timePeriods)
const selectedTimePeriod = computed(() => sessionsStore.selectedTimePeriod)
const filteredTimeSlots = computed(() => sessionsStore.filteredTimeSlots)
const selectedTimeRange = computed(() => props.modelValue)
const selectedDuration = computed(() => {
  if (!selectedTimeRange.value.start || !selectedTimeRange.value.end) return 0
  const start = sessionsStore.timeToMinutes(selectedTimeRange.value.start)
  const end = sessionsStore.timeToMinutes(selectedTimeRange.value.end)
  return end - start
})

// Methods
const selectTimePeriod = (period:  '*.vue' | 'Afternoon' | 'Evening' | 'All' ) => {
  sessionsStore.setTimePeriod(period)
  // Clear selection when changing period
  emit('update:modelValue', { start: null, end: null })
}

const formatTime = (time: string): string => {
  return sessionsStore.formatTime(time)
}

const formatTimeSlot = (slot: string): string => {
  return sessionsStore.formatTime(slot).replace(' ', '')
}

const getSlotClasses = (slot: string): string => {
  const isInRange = isSlotInRange(slot)
  const isStart = slot === selectedTimeRange.value.start
  const isEnd = isSlotBeforeTime(slot, selectedTimeRange.value.end || '')
  
  if (isStart && isEnd && selectedDuration.value === 30) {
    return 'bg-primary text-white shadow-lg transform scale-105'
  } else if (isStart) {
    return 'bg-primary text-white shadow-lg'
  } else if (isInRange) {
    return 'bg-primary bg-opacity-70 text-white'
  } else {
    return 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
  }
}

const isSlotInRange = (slot: string): boolean => {
  if (!selectedTimeRange.value.start || !selectedTimeRange.value.end) return false
  
  const slotMinutes = sessionsStore.timeToMinutes(slot)
  const startMinutes = sessionsStore.timeToMinutes(selectedTimeRange.value.start)
  const endMinutes = sessionsStore.timeToMinutes(selectedTimeRange.value.end)
  
  return slotMinutes >= startMinutes && slotMinutes < endMinutes
}

const isSlotBeforeTime = (slot: string, endTime: string): boolean => {
  if (!endTime) return false
  const slotMinutes = sessionsStore.timeToMinutes(slot)
  const endMinutes = sessionsStore.timeToMinutes(endTime)
  return slotMinutes < endMinutes
}

const getSlotFromEvent = (event: TouchEvent | MouseEvent): string | null => {
  let element: Element | null = null
  
  if (event instanceof TouchEvent && event.touches.length > 0) {
    element = document.elementFromPoint(
      event.touches[0].clientX,
      event.touches[0].clientY
    )
  } else if (event instanceof MouseEvent) {
    element = event.target as Element
  }
  
  // Find the time slot element
  while (element && !element.hasAttribute('data-time-slot')) {
    element = element.parentElement
  }
  
  return element?.getAttribute('data-time-slot') || null
}

const startSelection = (slot: string) => {
  isSelecting.value = true
  startSlot.value = slot
  currentSlot.value = slot
  
  const nextSlot = getNextSlot(slot)
  emit('update:modelValue', { start: slot, end: nextSlot })
  
  // Haptic feedback
  if (navigator.vibrate) {
    navigator.vibrate(50)
  }
}

const updateSelection = (slot: string) => {
  if (!isSelecting.value || !startSlot.value) return
  
  currentSlot.value = slot
  const startIndex = filteredTimeSlots.value.indexOf(startSlot.value)
  const currentIndex = filteredTimeSlots.value.indexOf(slot)
  
  if (currentIndex >= startIndex) {
    const endSlot = getNextSlot(slot)
    emit('update:modelValue', { start: startSlot.value, end: endSlot })
  }
}

const endSelection = () => {
  isSelecting.value = false
  
  // Emit duration change
  emit('duration-changed', selectedDuration.value)
  
  // Remove any active states
  document.querySelectorAll('.time-slot').forEach(el => {
    el.classList.remove('touching')
  })
}

const getNextSlot = (slot: string): string => {
  const currentIndex = filteredTimeSlots.value.indexOf(slot)
  const nextIndex = currentIndex + 1
  
  if (nextIndex < filteredTimeSlots.value.length) {
    return filteredTimeSlots.value[nextIndex]
  }
  
  // Calculate next 30-minute slot
  const minutes = sessionsStore.timeToMinutes(slot) + 30
  return sessionsStore.minutesToTime(minutes)
}

const quickSelectDuration = (duration: number) => {
  const firstSlot = filteredTimeSlots.value[0]
  if (!firstSlot) return
  
  const startMinutes = sessionsStore.timeToMinutes(firstSlot)
  const endMinutes = startMinutes + duration
  const endSlot = sessionsStore.minutesToTime(endMinutes)
  
  emit('update:modelValue', { start: firstSlot, end: endSlot })
  emit('duration-changed', duration)
}

// Touch event handlers
const handleTouchStart = (event: TouchEvent) => {
  event.preventDefault()
  const slot = getSlotFromEvent(event)
  if (slot) startSelection(slot)
}

const handleTouchMove = (event: TouchEvent) => {
  event.preventDefault()
  const slot = getSlotFromEvent(event)
  if (slot) updateSelection(slot)
}

const handleTouchEnd = () => {
  endSelection()
}

// Mouse event handlers
const handleMouseStart = (event: MouseEvent) => {
  const slot = getSlotFromEvent(event)
  if (slot) startSelection(slot)
}

const handleMouseMove = (event: MouseEvent) => {
  const slot = getSlotFromEvent(event)
  if (slot) updateSelection(slot)
}

const handleMouseEnd = () => {
  endSelection()
}

// Watch for external changes
watch(() => selectedDuration.value, (newDuration) => {
  emit('duration-changed', newDuration)
})

// Lifecycle
onMounted(() => {
  // Prevent default touch actions on time slots
  document.addEventListener('touchmove', (e) => {
    if ((e.target as Element)?.closest('.time-slot')) {
      e.preventDefault()
    }
  }, { passive: false })
})

onUnmounted(() => {
  // Cleanup handled by component unmount
})
</script>

<style scoped>
.time-slot {
  min-height: 44px;
  min-width: 44px;
}

.time-slot.touching {
  background-color: rgba(59, 130, 246, 0.5);
  transform: scale(1.05);
}

.select-none {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
</style>