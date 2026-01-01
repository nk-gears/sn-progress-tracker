<template>
  <div class="relative">
    <button
      @click="toggleDropdown"
      class="flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-white"
      aria-label="Change language"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
      </svg>
      <span class="font-medium">{{ currentLocaleName }}</span>
      <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
        @click="closeDropdown"
      >
        <button
          v-for="locale in SUPPORTED_LOCALES"
          :key="locale"
          @click="changeLocale(locale)"
          class="w-full text-left px-4 py-2 hover:bg-purple-50 transition-colors flex items-center justify-between"
          :class="{ 'bg-purple-100 text-purple-700 font-semibold': currentLocale === locale, 'text-gray-700': currentLocale !== locale }"
        >
          <span>{{ LOCALE_NAMES[locale] }}</span>
          <svg v-if="currentLocale === locale" class="w-5 h-5 text-purple-700" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLocale, SUPPORTED_LOCALES, LOCALE_NAMES, type Locale } from '@/i18n'

const { locale } = useI18n()
const isOpen = ref(false)

const currentLocale = computed(() => locale.value as Locale)
const currentLocaleName = computed(() => LOCALE_NAMES[currentLocale.value])

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const closeDropdown = () => {
  isOpen.value = false
}

const changeLocale = async (newLocale: Locale) => {
  if (newLocale !== currentLocale.value) {
    await setLocale(newLocale)
  }
  closeDropdown()
}

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
