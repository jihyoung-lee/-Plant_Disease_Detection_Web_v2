import './assets/main.css'
import axios from 'axios'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router';
import { createPinia } from 'pinia'
import './assets/tailwind.css'

axios.defaults.baseURL = 'http://127.0.0.1:8081'
axios.defaults.withCredentials = true

const pinia = createPinia()

createApp(App)
    .use(router)
    .use(pinia)
    .mount('#app');
