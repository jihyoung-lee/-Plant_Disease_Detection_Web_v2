import { ref, OnMounted } from 'vue';
import api from '@/lib/axios.js';

const user = ref(null);
const isAuthenticated = ref(false);

export function useAuth() {
    const fetchUser = async () => {
        try {
            const response = await api.get('')
        }
    }
}