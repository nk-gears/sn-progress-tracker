<template>
  <div class="home-view">
    <!-- Navigation -->
    <Navbar />

    <!-- Hero Section -->
    <HeroSection />

    <!-- Find Centre Section -->
    <section id="find-centre" class="section bg-gray-50">
      <div class="container mx-auto px-4">
        <h2 class="section-title">{{ $t('findCentre.title') }}</h2>
        <p class="section-subtitle">{{ $t('findCentre.subtitle') }}</p>

        <div class="max-w-3xl mx-auto">
          <div class="content-card">
            <div class="space-y-4">
              <select v-model="selectedState" class="form-select" @change="onStateChange">
                <option value="">{{ $t('findCentre.selectState') }}</option>
                <option value="tamil-nadu">{{ $t('findCentre.states.tamilNadu') }}</option>
                <option value="south-kerala">{{ $t('findCentre.states.southKerala') }}</option>
                <option value="puducherry">{{ $t('findCentre.states.puducherry') }}</option>
              </select>

              <select v-model="selectedDistrict" class="form-select" :disabled="!selectedState">
                <option value="">{{ $t('findCentre.selectDistrict') }}</option>
              </select>

              <select v-model="selectedCentre" class="form-select" :disabled="!selectedDistrict">
                <option value="">{{ $t('findCentre.selectCentre') }}</option>
              </select>

              <button @click="useMyLocation" class="btn btn-location w-full bg-blue-600 text-white hover:bg-blue-700">
                {{ $t('findCentre.useLocation') }}
              </button>
            </div>
          </div>

          <!-- Sample Centre Cards -->
          <div class="mt-8 space-y-4">
            <CentreCard
              v-for="centre in sampleCentres"
              :key="centre.id"
              :centre="centre"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- What is Shivanum Naanum Section -->
    <section id="about" class="section">
      <div class="container mx-auto px-4">
        <h2 class="section-title">{{ $t('about.title') }}</h2>
        <div class="max-w-4xl mx-auto content-card">
          <div class="prose prose-lg max-w-none space-y-4">
            <p>{{ $t('about.para1') }}</p>
            <p>{{ $t('about.para2') }}</p>
            <p>{{ $t('about.para3') }}</p>
            <p class="font-bold text-purple-700 text-lg">{{ $t('about.para4') }}</p>
          </div>

          <div class="mt-6 text-center">
            <a href="#find-centre" class="btn btn-primary">{{ $t('about.findCentreBtn') }}</a>
            <a href="https://www.brahmakumaris.com" target="_blank" class="btn btn-secondary ml-4">{{ $t('about.knowMoreBtn') }}</a>
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
            <p>{{ $t('organiser.para2') }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Join WhatsApp Group Section -->
    <WhatsAppJoinForm />

    <!-- Contact Section -->
    <section id="contact" class="section bg-gray-50">
      <div class="container mx-auto px-4">
        <h2 class="section-title">{{ $t('contact.title') }}</h2>
        <div class="max-w-2xl mx-auto content-card text-center">
          <p class="mb-6">{{ $t('contact.subtitle') }}</p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="tel:8XXXXXX" class="btn btn-primary">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              8XXXXXX ({{ $t('contact.call') }})
            </a>
            <a href="https://wa.me/9XXXXXXXX" class="btn btn-primary">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
              9XXXXXXXX ({{ $t('contact.whatsapp') }})
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
import CentreCard from '@/components/CentreCard.vue'
import WhatsAppJoinForm from '@/components/WhatsAppJoinForm.vue'
import FAQSection from '@/components/FAQSection.vue'
import Footer from '@/components/Footer.vue'
import type { Centre } from '@/types'

// Form state
const selectedState = ref('')
const selectedDistrict = ref('')
const selectedCentre = ref('')

// Sample centres data
const sampleCentres = ref<Centre[]>([
  {
    id: 1,
    name: 'West Mambalam, Chennai',
    address: '203, Murugan illam, chitlapakkam main road, Ganesh Nagar, Selaiyur Chennai 73',
    district: 'Chennai',
    state: 'Tamil Nadu',
    latitude: 13.0418,
    longitude: 80.2341,
    phone: '+91-XXXXXXXXXX',
    distance: 2
  },
  {
    id: 2,
    name: 'Ashok Nagar, Chennai',
    address: '13, Vadivel street, Kattabomman block, Jawahar nagar, West Jafferkhanpet, Chennai - 83',
    district: 'Chennai',
    state: 'Tamil Nadu',
    latitude: 13.0358,
    longitude: 80.2102,
    phone: '+91-XXXXXXXXXX',
    distance: 4
  },
  {
    id: 3,
    name: 'Anna Nagar, Chennai',
    address: '13, Vadivel street, Kattabomman block, Jawahar nagar, West Jafferkhanpet, Chennai - 83',
    district: 'Chennai',
    state: 'Tamil Nadu',
    latitude: 13.0850,
    longitude: 80.2101,
    phone: '+91-XXXXXXXXXX',
    distance: 4
  }
])

const onStateChange = () => {
  selectedDistrict.value = ''
  selectedCentre.value = ''
}

const useMyLocation = () => {
  if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        console.log('Location:', position.coords.latitude, position.coords.longitude)
        // TODO: Calculate distances and sort centres
        alert('Location detected! Finding nearest centres...')
      },
      (error) => {
        console.error('Location error:', error)
        alert('Please enable location access to find nearest centres')
      }
    )
  } else {
    alert('Geolocation is not supported by your browser')
  }
}
</script>

<style scoped>
.prose p {
  @apply mb-4 text-gray-700 leading-relaxed;
}
</style>
