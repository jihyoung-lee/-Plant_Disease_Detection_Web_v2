// stores/user.js
import { defineStore } from 'pinia'
import api, { getSavedToken, isProd, setAuthToken } from '@/lib/axios.js'

export const useUserStore = defineStore('user', {
    state: () => ({
        user: null,
        token: getSavedToken(),
        authChecked: false,
    }),

    getters: {
        isLoggedIn: (state) => !!state.user,
    },

    actions: {
        setUser(userData, token = null) {
            this.user = userData
            this.authChecked = true

            if (token) {
                this.token = token
                setAuthToken(token)
            }
        },

        async completeLogin(token = null, userData = null) {
            // 일반 운영 로그인은 토큰 대신 쿠키가 내려옴
            this.token = token || null
            setAuthToken(this.token)
            this.authChecked = false

            if (userData) {
                this.setUser(userData, token)
                return true
            }

            return this.fetchUser()
        },

        clearUser() {
            this.user = null
            this.token = null
            this.authChecked = true
            setAuthToken(null)
        },

        async fetchUser(force = false) {
            if (this.authChecked && !force) {
                return this.isLoggedIn
            }

            // 로컬은 저장된 토큰이 없으면 확인할 것도 없음
            if (!isProd && !this.token) {
                this.clearUser()
                return false
            }

            try {
                setAuthToken(this.token)
                const res = await api.get('/me')
                this.user = res.data.user
                this.authChecked = true
                return true
            } catch {
                this.clearUser()
                return false
            }
        },

        async logout() {
            try {
                await api.post('/logout')
            } finally {
                this.clearUser()
            }
        }
    }
})
