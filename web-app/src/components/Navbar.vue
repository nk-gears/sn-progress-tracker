<template>
  <nav class="navbar fixed top-0 left-0 right-0 bg-blue-900 shadow-md z-50">
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
        <div class="hidden md:flex space-x-6 items-center">
          <ul class="flex space-x-6 items-center">
            <li v-for="item in navItems" :key="item.href">
              <a :href="item.href" class="nav-link">{{ $t(item.label) }}</a>
            </li>
          </ul>
          <LanguageSwitcher />
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
          </ul>
          <div class="pt-4 border-t border-white/20">
            <LanguageSwitcher />
          </div>
        </div>
      </Transition>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import LanguageSwitcher from './LanguageSwitcher.vue'

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

const toggleMenu = () => {
  isOpen.value = !isOpen.value
}

const closeMenu = () => {
  isOpen.value = false
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
