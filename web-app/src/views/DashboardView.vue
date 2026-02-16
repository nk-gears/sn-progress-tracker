<template>
  <div class="dashboard-view">
    <!-- Navigation -->
    <Navbar />

    <!-- Hero Section -->
    <section class="pt-28 pb-12 bg-gradient-to-r from-purple-600 to-blue-600 text-white">
      <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-2">{{ $t('nav.dashboard', 'Dashboard') }}</h1>
        <p class="text-lg text-white/90">{{ $t('branch.current', 'Current Branch') }}: <strong>{{ selectedBranch }}</strong></p>
      </div>
    </section>

    <!-- Main Content -->
    <section class="section">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Submit Event Report Card --> 
          <div class="content-card text-center">
            <div class="mb-4">
              <svg class="w-16 h-16 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <h2 class="text-2xl font-bold mb-4">{{ $t('event.submit', 'Submit Event Report') }}</h2>
            <p class="text-gray-600 mb-6">Share details about events happening in your branch, including photos and videos.</p>
            <button
              @click="openEventReportModal"
              class="btn btn-primary"
            >
              {{ $t('event.new', 'Add New Event Report') }}
            </button>
          </div>

          <!-- View Event Reports Card -->
          <div class="content-card text-center">
            <div class="mb-4">
              <svg class="w-16 h-16 mx-auto text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h2 class="text-2xl font-bold mb-4">{{ $t('event.view', 'View Event Reports') }}</h2>
            <p class="text-gray-600 mb-6">See all event reports submitted by your branch in one place.</p>
            <router-link
              to="/event-reports"
              class="btn btn-primary"
            >
              {{ $t('event.viewAll', 'View All Reports') }}
            </router-link>
          </div>
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
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useBranchStore } from '@/stores/branchStore'
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'
import EventReportModal from '@/components/EventReportModal.vue'

const router = useRouter()
const branchStore = useBranchStore()

const selectedBranch = computed(() => branchStore.selectedBranch)
const showEventReportModal = ref(false)

const openEventReportModal = () => {
  showEventReportModal.value = true
}

const closeEventReportModal = () => {
  showEventReportModal.value = false
}

const handleEventReportSuccess = () => {
  showEventReportModal.value = false
  // Navigate to event reports view to see the newly submitted event
  router.push('/event-reports')
}
</script>

<style scoped>
.prose p {
  @apply mb-4 text-gray-700 leading-relaxed;
}
</style>
