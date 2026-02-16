<template>
  <nav class="navbar fixed top-0 left-0 right-0 shadow-md z-50" style="background-color: #0B238F;">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between h-20">
        <!-- Logo/Brand -->
        <a href="#home" class="flex items-center space-x-3">
          <img src="/images/sn-logo.png" alt="Shivanum Naanum Logo" class="h-12 w-auto" />
          <div>
            <div class="font-bold text-white text-lg">{{ $t('hero.title') }}</div>
            <div class="text-sm text-white/90">Brahma Kumaris</div>
          </div>
        </a>

        <!-- Desktop Menu -->
        <div class="hidden md:flex space-x-4 items-center">
          <ul class="flex space-x-6 items-center">
            <li v-for="item in navItems" :key="item.href">
              <a :href="item.href" class="nav-link">{{ $t(item.label) }}</a>
            </li>
            <li v-if="hasBranch">
              <button @click="navigateToDashboard" class="nav-link">Dashboard</button>
            </li>
          </ul>
          <LanguageSwitcher />
          <!-- WhatsApp Button -->
          <a href="https://wa.me/+919566004465" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition-colors text-sm">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            Join via WhatsApp
          </a>
        </div>

        <!-- Mobile Menu Button -->
        <button @click="toggleMenu" class="md:hidden p-2 text-white" aria-label="Toggle menu">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!isOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile Menu -->
      <Transition name="slide">
        <div v-if="isOpen" class="md:hidden py-4">
          <ul class="space-y-2 mb-4">
            <li v-for="item in navItems" :key="item.href">
              <a :href="item.href" @click="closeMenu" class="block py-2 nav-link">
                {{ $t(item.label) }}
              </a>
            </li>
            <li v-if="hasBranch">
              <button @click="navigateToDashboard" class="block w-full text-left py-2 nav-link">
                Dashboard
              </button>
            </li>
          </ul>
          <div class="pt-4 border-t border-white/20 space-y-3">
            <LanguageSwitcher />
            <!-- Mobile WhatsApp Button -->
            <a href="https://wa.me/+919566004465" target="_blank" @click="closeMenu" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition-colors text-sm">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
              Join via WhatsApp
            </a>
          </div>
        </div>
      </Transition>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useBranchStore } from '@/stores/branchStore'
import LanguageSwitcher from './LanguageSwitcher.vue'

const router = useRouter()
const branchStore = useBranchStore()

const isOpen = ref(false)

const navItems = [
  { href: '#home', label: 'nav.home' },
  { href: '#find-centre', label: 'nav.findCentre' },
  { href: '#about', label: 'nav.about' },
  { href: '#organiser', label: 'nav.organiser' },
  { href: '#join-us', label: 'nav.joinUs' },
  { href: '#contact', label: 'nav.contact' },
  { href: '#faq', label: 'nav.faq' }
]

const hasBranch = computed(() => branchStore.hasBranch())

const toggleMenu = () => {
  isOpen.value = !isOpen.value
}

const closeMenu = () => {
  isOpen.value = false
}

const navigateToDashboard = () => {
  router.push('/dashboard')
  closeMenu()
}
</script>

<style scoped>
.nav-link {
  @apply text-white hover:text-purple-200 transition-colors font-medium;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
