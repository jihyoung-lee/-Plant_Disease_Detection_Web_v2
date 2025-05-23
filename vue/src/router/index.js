import { createRouter, createWebHistory } from 'vue-router';
import DiseaseSearch from '@/components/DiseaseSearch.vue';
import DiseaseDetail from '../components/DiseaseInfo.vue';
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
];


const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
