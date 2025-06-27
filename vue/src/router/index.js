import { createRouter, createWebHistory } from 'vue-router';
import DiseaseSearch from '@/components/DiseaseSearch.vue';
import DiseaseDetail from '../components/DiseaseInfo.vue';
import TrainList from '../components/TrainList.vue'
import Register from '../components/auth/Register.vue'
import Login from '@/components/auth/Login.vue'
const routes = [
    {
        path: '/',
        name: 'Home',
        meta: { title: '병해충 도감 검색' },
        component: DiseaseSearch, // 일단 DiseaseSearch를 기본으로
    },
    {
        path: '/disease/:cropName/:sickNameKor',
        name: 'DiseaseDetail',
        meta: { title: '병해충 상세정보' },
        component: DiseaseDetail,
        props: true,
    },
    {
        path: '/disease-search',
        name: 'DiseaseSearch',
        meta: { title: '병해충 도감 검색' },
        component: DiseaseSearch,
    },
    {
        path: '/list',
        name: 'TrainList',
        meta: { title: '병해충 판별 결과' },
        component: TrainList,
    },
    {
        path: '/register',
        name: 'Register',
        meta: { title: '회원가입' },
        component: Register,
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guestOnly: true }, // 로그인된 사용자 접근 방지하려면 사용
    },
];


const router = createRouter({
    history: createWebHistory(),
    routes,
});
// 중복 로그인 방지
router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('token')

    if (to.meta.guestOnly && isAuthenticated) {
        next('/') // 이미 로그인한 경우 홈으로
    } else {
        next()
    }
})

export default router;
