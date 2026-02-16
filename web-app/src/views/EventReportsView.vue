<template>
  <div class="event-reports-view">
    <!-- Navigation -->
    <Navbar />

    <!-- Header Section -->
    <section class="pt-28 pb-12 bg-gradient-to-r from-purple-600 to-blue-600 text-white">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-4xl md:text-5xl font-bold mb-2">{{ $t('event.reports', 'Event Reports') }}</h1>
            <p class="text-lg text-white/90">{{ $t('branch.current', 'Current Branch') }}: <strong>{{ selectedBranch }}</strong></p>
          </div>
          <button
            @click="openEventReportModal"
            class="btn bg-white text-purple-600 hover:bg-gray-100 font-bold"
          >
            + {{ $t('event.add', 'Add Event Report') }}
          </button>
        </div>
      </div>
    </section>

    <!-- Content Section -->
    <section class="section">
      <div class="container mx-auto px-4">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-12">
          <div class="inline-block">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
          </div>
          <p class="mt-4 text-gray-600">{{ $t('event.loading', 'Loading event reports...') }}</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <!-- Empty State -->
        <div v-else-if="events.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="text-xl font-bold text-gray-700 mb-2">{{ $t('event.noReports', 'No event reports yet') }}</h3>
          <p class="text-gray-600 mb-6">Start by submitting your first event report to share your branch activities.</p>
          <button
            @click="openEventReportModal"
            class="btn btn-primary"
          >
            {{ $t('event.submit', 'Submit Event Report') }}
          </button>
        </div>

        <!-- Events Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <EventCard
            v-for="event in events"
            :key="`${event.branch}-${event.submitted_at}`"
            :event="event"
          />
        </div>
      </div>
    </section>

    <!-- Footer -->
    <Footer />
  </div>

  <!-- Event Report Modal -->
  <EventReportModal
    :is-open="showEventReportModal"
    @close="closeEventReportModal"
    @success="handleEventReportSuccess"
  />
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useBranchStore } from '@/stores/branchStore'
import { useEventReportStore } from '@/stores/eventReportStore'
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'
import EventReportModal from '@/components/EventReportModal.vue'
import EventCard from '@/components/EventCard.vue'

const branchStore = useBranchStore()
const eventReportStore = useEventReportStore()

const selectedBranch = computed(() => branchStore.selectedBranch)
const events = computed(() => eventReportStore.events)
const isLoading = computed(() => eventReportStore.isLoading)
const error = computed(() => eventReportStore.error)

const showEventReportModal = ref(false)

const openEventReportModal = () => {
  showEventReportModal.value = true
}

const closeEventReportModal = () => {
  showEventReportModal.value = false
}

const handleEventReportSuccess = () => {
  showEventReportModal.value = false
  // Refresh the event list
  loadEvents()
}

const loadEvents = async () => {
  if (selectedBranch.value) {
    await eventReportStore.fetchEvents(selectedBranch.value)
  }
}

onMounted(() => {
  loadEvents()
})
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
