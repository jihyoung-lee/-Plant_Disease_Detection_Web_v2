import './assets/main.css'
import axios from 'axios'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router';
import { createPinia } from 'pinia'
import './assets/tailwind.css'
import i18n from './i18n.js'
import GoogleLogin from 'vue3-google-login'

axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL
axios.defaults.withCredentials = true

const pinia = createPinia()
const savedLang = localStorage.getItem('lang')
if (savedLang) {
    i18n.global.locale.value = savedLang
}

createApp(App)
    .use(router)
    .use(pinia)
    .use(i18n)
    .use(GoogleLogin,{
        clientId: import.meta.env.VITE_GOOGLE_CLIENT_ID,
    })
    .mount('#app');
