import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './style.css'

// Create Vue application
const app = createApp(App)

// Install Pinia for state management
app.use(createPinia())

// Install Vue Router
app.use(router)

// Mount the application
app.mount('#app')

// PWA Registration
import { registerSW } from 'virtual:pwa-register'

if ('serviceWorker' in navigator) {
  const updateSW = registerSW({
    onNeedRefresh() {
      // Show a prompt to reload/refresh the app due to an available update
      if (confirm('New content available, reload?')) {
        updateSW(true)
      }
    },
    onOfflineReady() {
      console.log('App ready to work offline')
    },
  })
}