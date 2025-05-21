import { createRouter, createWebHistory } from 'vue-router';
import DiseaseSearch from '@/components/DiseaseSearch.vue';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: DiseaseSearch, // 일단 DiseaseSearch를 기본으로
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
