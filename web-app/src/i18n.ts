import { createI18n } from 'vue-i18n'

// Supported languages
export const SUPPORTED_LOCALES = ['ta', 'en', 'ml', 'hi'] as const
export type Locale = typeof SUPPORTED_LOCALES[number]

export const LOCALE_NAMES: Record<Locale, string> = {
  ta: 'தமிழ்',
  en: 'English',
  ml: 'മലയാளം',
  hi: 'हिन्दी'
}

// Load locale messages from JSON files
async function loadLocaleMessages(locale: Locale) {
  const messages = await fetch(`/locales/lang-${locale}.json`)
  return await messages.json()
}

// Get language from URL query parameter
function getLocaleFromURL(): Locale | null {
  const params = new URLSearchParams(window.location.search)
  const lang = params.get('lang') as Locale
  return SUPPORTED_LOCALES.includes(lang) ? lang : null
}

// Update URL with language parameter
function updateURL(locale: Locale) {
  const url = new URL(window.location.href)
  url.searchParams.set('lang', locale)
  window.history.replaceState({}, '', url.toString())
}

// Get stored locale or default to Tamil
// Priority: URL param > localStorage > default (Tamil)
function getStoredLocale(): Locale {
  // First check URL parameter
  const urlLocale = getLocaleFromURL()
  if (urlLocale) {
    return urlLocale
  }

  // Then check localStorage
  const stored = localStorage.getItem('locale') as Locale
  return SUPPORTED_LOCALES.includes(stored) ? stored : 'ta'
}

// Create i18n instance
export const i18n = createI18n({
  legacy: false, // Use Composition API
  locale: getStoredLocale(),
  fallbackLocale: 'en',
  messages: {} // Messages will be loaded dynamically
})

// Load initial locale
export async function setupI18n() {
  const locale = i18n.global.locale.value as Locale
  const messages = await loadLocaleMessages(locale)
  i18n.global.setLocaleMessage(locale, messages)

  // Set initial URL parameter and HTML lang attribute
  updateURL(locale)
  document.querySelector('html')?.setAttribute('lang', locale)

  return i18n
}

// Switch language function
export async function setLocale(locale: Locale) {
  // Load messages if not already loaded
  if (!i18n.global.availableLocales.includes(locale)) {
    const messages = await loadLocaleMessages(locale)
    i18n.global.setLocaleMessage(locale, messages)
  }

  // Set locale
  i18n.global.locale.value = locale
  localStorage.setItem('locale', locale)

  // Update URL parameter
  updateURL(locale)

  // Update HTML lang attribute
  document.querySelector('html')?.setAttribute('lang', locale)
}
