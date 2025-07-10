import { createRouter, createWebHistory } from 'vue-router'
import i18n from '../i18n.js'
import Home from '@/components/Home.vue'
import DiseaseSearch from '@/components/DiseaseSearch.vue'
import DiseaseDetail from '../components/DiseaseInfo.vue'
import TrainList from '../components/TrainList.vue'
import Register from '../components/auth/Register.vue'
import Login from '@/components/auth/Login.vue'
import Logout from '@/components/auth/Logout.vue'
const routes = [
    {
        path: '/',
        name: 'Home',
        meta: { titleKey: 'meta_home' },
        component: Home,
    },
    {
        path: '/disease/:cropName/:sickNameKor',
        name: 'DiseaseDetail',
        meta: { titleKey: 'meta_disease_detail' },
        component: DiseaseDetail,
        props: true,
    },
    {
        path: '/disease-search',
        name: 'DiseaseSearch',
        meta: { titleKey: 'meta_disease_search' },
        component: DiseaseSearch,
    },
    {
        path: '/list',
        name: 'TrainList',
        meta: { titleKey: 'meta_train_list' },
        component: TrainList,
    },
    {
        path: '/register',
        name: 'Register',
        meta: { titleKey: 'meta_register' },
        component: Register,
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guestOnly: true, titleKey: 'meta_login' },
    },
    {
        path: '/logout',
        component: Logout,
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

// 중복 로그인 방지 및 다국어 타이틀 적용
router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('token')

    if (to.meta.guestOnly && isAuthenticated) {
        next('/')
    } else {
        const titleKey = to.meta.titleKey
        if (titleKey) {
            document.title = i18n.global.t(titleKey)
        }
        next()
    }
})

export default router
