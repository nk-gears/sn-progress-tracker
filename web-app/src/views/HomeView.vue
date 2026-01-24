<template>
  <div class="home-view">
    <!-- Navigation -->
    <Navbar />

    <!-- Hero Section -->
    <HeroSection />

    <!-- Find Centre Section -->
    <CentreFinder @join-now="handleJoinNow" />

    <!-- What is Shivanum Naanum Section -->
    <section id="about" class="section">
      <div class="container mx-auto px-4">
        <h2 class="section-title">{{ $t('about.title') }}</h2>
        <div class="max-w-4xl mx-auto content-card">
          <div class="prose prose-lg max-w-none space-y-1">
            <p>{{ $t('about.para1') }}</p>
            <p>{{ $t('about.para2') }}</p>
            <p>{{ $t('about.para3') }}</p>
            <!-- <p class="font-bold text-purple-700 text-lg">{{ $t('about.para4') }}</p> -->
          </div>

          <div class="mt-6 text-center">
            <a href="#find-centre" class="btn btn-primary">{{ $t('about.findCentreBtn') }}</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Brahma Kumaris Section -->
    <section id="organiser" class="section bg-gray-50">
      <div class="container mx-auto px-4">
        <h2 class="section-title">{{ $t('organiser.title') }}</h2>
        <div class="max-w-4xl mx-auto content-card">
          <div class="prose prose-lg max-w-none space-y-4">
            <p>{{ $t('organiser.para1') }}</p>
            <!-- <p>{{ $t('organiser.para2') }}</p> -->
          </div>
        </div>
      </div>
    </section>

    <!-- Join WhatsApp Group Section (Temporarily Hidden) -->
    <!-- <WhatsAppJoinForm ref="whatsappForm" :selected-centre-id="selectedCentreId" /> -->

    <!-- Contact Section -->
    <section id="contact" class="section bg-gray-50">
      <div class="container mx-auto px-4">
        <h2 class="section-title">{{ $t('contact.title') }}</h2>
        <div class="max-w-2xl mx-auto content-card text-center">
          <p class="mb-6">{{ $t('contact.subtitle') }}</p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="tel:9566004465" class="btn btn-primary">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              9566004465 ({{ $t('contact.call') }})
            </a>
            <a href="https://wa.me/+919566004465" class="btn btn-primary">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
              9566004465 ({{ $t('contact.whatsapp') }})
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <FAQSection />

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Navbar from '@/components/Navbar.vue'
import HeroSection from '@/components/HeroSection.vue'
import CentreFinder from '@/components/CentreFinder.vue'
// import WhatsAppJoinForm from '@/components/WhatsAppJoinForm.vue' // Temporarily disabled
import FAQSection from '@/components/FAQSection.vue'
import Footer from '@/components/Footer.vue'
import type { Centre } from '@/types'

// const whatsappForm = ref<InstanceType<typeof WhatsAppJoinForm> | null>(null) // Temporarily disabled
const selectedCentreId = ref<number>(0)

const handleJoinNow = (centre: Centre) => {
  // Join Now button now opens a modal in CentreFinder component
  // No action needed here - the modal is handled by CentreFinder
  console.log('User clicked Join Now for:', centre.name)
}
</script>

<style scoped>
.prose p {
  @apply mb-4 text-gray-700 leading-relaxed;
}
</style>
