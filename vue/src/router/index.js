import { createRouter, createWebHistory } from 'vue-router';
import DiseaseSearch from '@/components/DiseaseSearch.vue';
import DiseaseDetail from '../components/DiseaseInfo.vue';
const routes = [
    {
        path: '/',
        name: 'Home',
        component: DiseaseSearch, // 일단 DiseaseSearch를 기본으로
    },
    {
        path: '/disease/:cropName/:sickNameKor',
        name: 'DiseaseDetail',
        component: DiseaseDetail,
        props: true,
    },
];


const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
