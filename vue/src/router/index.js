import { createRouter, createWebHistory } from 'vue-router'
import i18n from '../i18n.js'
import Home from '@/components/Home.vue'
import DiseaseSearch from '@/components/DiseaseSearch.vue'
import DiseaseDetail from '../components/DiseaseInfo.vue'
import TrainList from '../components/TrainList.vue'
import Register from '../components/auth/Register.vue'
import Login from '@/components/auth/Login.vue'
import Logout from '@/components/auth/Logout.vue'
import { useUserStore } from '@/stores/user'

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
        meta: { requiresAuth: true, titleKey: 'meta_train_list' },
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

// 쿠키 로그인도 있어서 localStorage 말고 서버 기준으로 확인
router.beforeEach(async (to) => {
    const userStore = useUserStore()
    const isAuthenticated = await userStore.fetchUser()

    const titleKey = to.meta.titleKey
    if (titleKey) {
        document.title = i18n.global.t(titleKey)
    }

    if (to.meta.requiresAuth && !isAuthenticated) {
        return { name: 'Login' }
    }

    if (to.meta.guestOnly && isAuthenticated){
        return { name: 'Home' }
    }

    return true
})

export default router
