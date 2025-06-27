import { ref, onMounted } from 'vue';
import api from '@/lib/axios';

const user = ref(null);
const isAuthenticated = ref(false);

export function useAuth() {
    const fetchUser = async () => {
        try {
            const response = await api.get('/user');
            user.value = response.data;
            isAuthenticated.value = true;
        } catch (e) {
            user.value = null;
            isAuthenticated.value = false;
        }
    };

    onMounted(() => {
        if (localStorage.getItem('token')) {
            fetchUser();
        }
    });

    return {
        user,
        isAuthenticated,
        fetchUser,
    };
}
