import { createApp } from 'vue'
import { Quasar, Notify, Loading, Dialog, LocalStorage, SessionStorage } from 'quasar'
import { createPinia } from 'pinia'

// Import icon libraries
import '@quasar/extras/material-icons/material-icons.css'

// Import Quasar css
import 'quasar/src/css/index.sass'

// Import app styles
import './css/app.scss'

import App from './App.vue'
import router from './router'

const app = createApp(App)

// Use Pinia for state management
const pinia = createPinia()
app.use(pinia)

// Use Quasar
app.use(Quasar, {
  plugins: {
    Notify,
    Loading,
    Dialog,
    LocalStorage,
    SessionStorage
  },
  config: {
    notify: {
      position: 'top-right',
      timeout: 2500
    }
  }
})

// Use Vue Router
app.use(router)

app.mount('#q-app')
