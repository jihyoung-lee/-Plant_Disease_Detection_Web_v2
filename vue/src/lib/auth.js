import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const loading = ref(false)

    const fetchUser = async () => {
        loading.value = true
        try {
            const res = await api.get('/me')
            user.value = res.data
        } catch (err) {
            if (err.response?.status !== 401) {
                console.error('사용자 정보 로딩 실패', err)
            }
            user.value = null
        } finally {
            loading.value = false
        }
    }

    const logout = async () => {
        try {
            await api.post('/logout')
        } catch (e) {
            console.warn('서버 로그아웃 실패', e)
        } finally {
            user.value = null
        }
    }

    return { user, loading, fetchUser, logout }
})
