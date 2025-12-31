import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { setupI18n } from './i18n'
import './style.css'

// Setup i18n and mount app
;(async () => {
  const i18n = await setupI18n()

  const app = createApp(App)

  app.use(createPinia())
  app.use(router)
  app.use(i18n)

  app.mount('#app')
})()
