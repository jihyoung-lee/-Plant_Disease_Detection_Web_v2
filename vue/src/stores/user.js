// stores/user.js
import { defineStore } from 'pinia'
import api, { setAuthToken } from '@/lib/axios.js'

export const useUserStore = defineStore('user', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
    }),

    getters: {
        isLoggedIn: (state) => !!state.token,
    },

    actions: {
        setUser(userData, token) {
            this.user = userData
            this.token = token
            localStorage.setItem('token', token)
            setAuthToken(token)
        },

        clearUser() {
            this.user = null
            this.token = null
            localStorage.removeItem('token')
            setAuthToken(null)
        },

        async fetchUser() {
            try {
                setAuthToken(this.token)
                const res = await api.get('/me')
                this.user = res.data
                console.log('🟢 사용자 정보 가져오기 성공:', this.user)
            } catch (err) {
                console.error('🔴 사용자 정보 가져오기 실패:', err?.response?.data || err.message)
                this.clearUser()
            }
        }
    }
})
