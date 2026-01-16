<template>
  <footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid md:grid-cols-3 gap-8 mb-8">
        <!-- Brand Section -->
        <div>
          <div class="flex items-center space-x-2 mb-4">
            <span class="text-3xl"><img data-v-4d808a77="" src="/images/sn-logo.png" alt="Shivanum Naanum Logo" class="h-12 w-auto"></span>
            <h3 class="text-xl font-bold">{{ $t('hero.title') }}</h3>
          </div>
          <p class="text-gray-400">{{ $t('footer.tagline') }}</p>
          <p class="text-gray-400 mt-2 text-sm">{{ $t('footer.organisedBy') }}</p>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="font-bold mb-4">{{ $t('footer.quickLinks') }}</h4>
          <ul class="space-y-2 text-gray-400">
            <li v-for="link in quickLinks" :key="link.href">
              <a :href="link.href" class="hover:text-white transition-colors">
                {{ $t(link.label) }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Contact -->
        <div>
          <h4 class="font-bold mb-4">{{ $t('footer.contact') }}</h4>
          <div class="space-y-2 text-gray-400">
            <a :href="`tel:${contactInfo.phone}`" class="block hover:text-white transition-colors">
              <span class="font-medium">{{ $t('footer.call') }}:</span> {{ contactInfo.phone }}
            </a>
            <a :href="`https://wa.me/${contactInfo.whatsapp}`" class="block hover:text-white transition-colors">
              <span class="font-medium">{{ $t('footer.whatsapp') }}:</span> {{ contactInfo.whatsapp }}
            </a>
          </div>

          <!-- Social Links (if any) -->
          <div class="mt-4 flex space-x-4">
            <a href="#" class="text-gray-400 hover:text-white transition-colors" aria-label="Facebook">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition-colors" aria-label="Instagram">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="border-t border-gray-800 pt-8 text-center text-gray-400 text-sm">
        <p>&copy; {{ currentYear }} {{ $t('footer.copyright') }}</p>
        <p class="mt-2">
          {{ $t('footer.eventName') }}
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const quickLinks = [
  { href: '#home', label: 'nav.home' },
  { href: '#find-centre', label: 'nav.findCentre' },
  { href: '#about', label: 'nav.about' },
  { href: '#join-us', label: 'nav.joinUs' },
  { href: '#faq', label: 'nav.faq' }
]

const contactInfo = {
  phone: '8XXXXXX',
  whatsapp: '9XXXXXXXX'
}

const currentYear = computed(() => new Date().getFullYear())
</script>
