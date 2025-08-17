<template>
  <div class="space-y-3">
    <!-- Time Period Selection -->
    <div class="grid grid-cols-4 gap-2">
      <button
        v-for="period in timePeriods"
        :key="period.name"
        @click="selectTimePeriod(period.name)"
        class="py-2 px-3 rounded-lg font-medium text-sm touch-target transition-all duration-200"
        :class="selectedTimePeriod === period.name
          ? 'bg-primary text-white shadow-lg'
          : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
      >
        <span>{{ period.name }}</span>
      </button>
    </div>

    <!-- Time Slots Grid -->
    <div class="space-y-1">
      <div class="flex items-center justify-between">
        <h4 class="text-sm font-medium text-gray-700">Select meditation time</h4>
        <div v-if="selectedDuration > 0" class="text-sm text-primary font-medium">
          {{ selectedDuration }} minutes selected
        </div>
      </div>

      <!-- Touch instructions -->


      <!-- Time slots -->
      <div 
        class="grid grid-cols-6 gap-1 p-3 bg-gray-50 rounded-lg select-none"
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
          class="time-slot aspect-square flex flex-col items-center justify-center text-xs rounded-lg cursor-pointer transition-all duration-150 touch-target font-medium"
          :class="getSlotClasses(slot)"
        >
          <div class="font-semibold">{{ getTimeOnly(slot) }}</div>
          <div class="text-xs opacity-80">{{ getAmPm(slot) }}</div>
        </div>
      </div>
    </div>

    <!-- Quick duration buttons -->
 

    <!-- Selected time ranges display -->
    <div v-if="selectedRanges.length > 0" class="space-y-3">
      <div class="flex items-center justify-between">
        <div class="font-medium text-gray-800">Selected Time Ranges</div>
        <button
          @click="clearAllSelections"
          class="text-sm text-red-600 hover:text-red-800 font-medium"
        >
          Clear All
        </button>
      </div>
      
      <div class="space-y-2">
        <div
          v-for="(range, index) in selectedRanges"
          :key="index"
          class="card p-3 bg-green-50 border-green-200"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-sm text-green-700">
              <div class="text-center">
                <div class="font-semibold">{{ getTimeOnly(range.start!) }}</div>
                <div class="text-xs">{{ getAmPm(range.start!) }}</div>
              </div>
              <span class="mx-1">-</span>
              <div class="text-center">
                <div class="font-semibold">{{ getTimeOnly(range.end!) }}</div>
                <div class="text-xs">{{ getAmPm(range.end!) }}</div>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="text-right text-xs">
                <div class="font-semibold text-green-800">
                  {{ sessionsStore.timeToMinutes(range.end!) - sessionsStore.timeToMinutes(range.start!) }}min
                </div>
              </div>
              <button
                @click="removeRange(index)"
                class="text-red-500 hover:text-red-700 text-xs"
                title="Remove this range"
              >
                ✕
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card p-3 bg-blue-50 border-blue-200">
        <div class="flex items-center justify-between text-sm">
          <div class="font-medium text-blue-800">Total Duration</div>
          <div class="font-bold text-blue-800">
            {{ selectedDuration }}min ({{ Math.round(selectedDuration / 60 * 100) / 100 }}h)
          </div>
        </div>
      </div>
    </div>
    
    <!-- Instructions -->
    <div v-if="selectedRanges.length === 0" class="text-center text-gray-500 text-sm p-4">
      Drag to select meditation time ranges. Click on range starts to remove them.
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useSessionsStore } from '@/stores/sessions'
import type { TimeRange, MultipleTimeRanges } from '@/types'

// Props and emits
interface Props {
  modelValue: MultipleTimeRanges
}

interface Emits {
  (e: 'update:modelValue', value: MultipleTimeRanges): void
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
const selectedRanges = computed(() => props.modelValue.ranges)
const selectedDuration = computed(() => props.modelValue.totalDuration)

// Current selection state for new range being selected
const currentSelection = ref<TimeRange>({ start: null, end: null })

// Methods
const selectTimePeriod = (period: 'All' | 'Morning' | 'Afternoon' | 'Evening') => {
  sessionsStore.setTimePeriod(period)
  // Keep existing selections when changing period
  // Only clear current selection if user is in middle of selecting
  if (isSelecting.value) {
    isSelecting.value = false
    currentSelection.value = { start: null, end: null }
  }
}

const formatTime = (time: string): string => {
  return sessionsStore.formatTime(time)
}

const formatTimeSlot = (slot: string): string => {
  return sessionsStore.formatTime(slot).replace(' ', '')
}

const getTimeOnly = (slot: string): string => {
  const formatted = sessionsStore.formatTime(slot)
  return formatted.split(' ')[0]
}

const getAmPm = (slot: string): string => {
  const formatted = sessionsStore.formatTime(slot)
  return formatted.split(' ')[1]
}

const getSlotClasses = (slot: string): string => {
  const isInSelectedRanges = isSlotInSelectedRanges(slot)
  const isInCurrentSelection = isSlotInCurrentSelection(slot)
  const isStart = isSlotRangeStart(slot)
  
  if (isStart) {
    return 'bg-primary text-white shadow-lg transform scale-105 border-2 border-primary'
  } else if (isInSelectedRanges) {
    return 'bg-primary bg-opacity-80 text-white'
  } else if (isInCurrentSelection) {
    return 'bg-blue-300 text-white'
  } else {
    return 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
  }
}

const isSlotInSelectedRanges = (slot: string): boolean => {
  const slotMinutes = sessionsStore.timeToMinutes(slot)
  
  return selectedRanges.value.some(range => {
    if (!range.start || !range.end) return false
    const startMinutes = sessionsStore.timeToMinutes(range.start)
    const endMinutes = sessionsStore.timeToMinutes(range.end)
    return slotMinutes >= startMinutes && slotMinutes < endMinutes
  })
}

const isSlotInCurrentSelection = (slot: string): boolean => {
  if (!currentSelection.value.start || !currentSelection.value.end) return false
  
  const slotMinutes = sessionsStore.timeToMinutes(slot)
  const startMinutes = sessionsStore.timeToMinutes(currentSelection.value.start)
  const endMinutes = sessionsStore.timeToMinutes(currentSelection.value.end)
  
  return slotMinutes >= startMinutes && slotMinutes < endMinutes
}

const isSlotRangeStart = (slot: string): boolean => {
  return selectedRanges.value.some(range => range.start === slot) || 
         currentSelection.value.start === slot
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
  // Check if clicking on an existing range start - if so, remove that range
  const existingRangeIndex = selectedRanges.value.findIndex(range => range.start === slot)
  if (existingRangeIndex !== -1) {
    removeRange(existingRangeIndex)
    return
  }
  
  isSelecting.value = true
  startSlot.value = slot
  currentSlot.value = slot
  
  const nextSlot = getNextSlot(slot)
  currentSelection.value = { start: slot, end: nextSlot }
  
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
    currentSelection.value = { start: startSlot.value, end: endSlot }
  }
}

const endSelection = () => {
  if (!isSelecting.value) return
  
  isSelecting.value = false
  
  // Add the current selection to the ranges array
  if (currentSelection.value.start && currentSelection.value.end) {
    addRange(currentSelection.value)
  }
  
  // Clear current selection
  currentSelection.value = { start: null, end: null }
  
  // Remove any active states
  document.querySelectorAll('.time-slot').forEach(el => {
    el.classList.remove('touching')
  })
}

const addRange = (range: TimeRange) => {
  const newRanges = [...selectedRanges.value, range]
  const totalDuration = calculateTotalDuration(newRanges)
  
  emit('update:modelValue', { ranges: newRanges, totalDuration })
  emit('duration-changed', totalDuration)
}

const removeRange = (index: number) => {
  const newRanges = selectedRanges.value.filter((_, i) => i !== index)
  const totalDuration = calculateTotalDuration(newRanges)
  
  emit('update:modelValue', { ranges: newRanges, totalDuration })
  emit('duration-changed', totalDuration)
}

const calculateTotalDuration = (ranges: TimeRange[]): number => {
  return ranges.reduce((total, range) => {
    if (!range.start || !range.end) return total
    const start = sessionsStore.timeToMinutes(range.start)
    const end = sessionsStore.timeToMinutes(range.end)
    return total + (end - start)
  }, 0)
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

const clearAllSelections = () => {
  emit('update:modelValue', { ranges: [], totalDuration: 0 })
  emit('duration-changed', 0)
  currentSelection.value = { start: null, end: null }
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
watch(() => props.modelValue.totalDuration, (newDuration) => {
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