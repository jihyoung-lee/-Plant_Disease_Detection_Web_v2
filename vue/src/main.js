import './assets/main.css'
import axios from 'axios'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router';
import './assets/tailwind.css'
import { createPinia } from 'pinia'

axios.defaults.baseURL = 'http://127.0.0.1:8081'
axios.defaults.withCredentials = true

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')