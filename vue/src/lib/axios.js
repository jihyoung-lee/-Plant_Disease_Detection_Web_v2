import axios from 'axios';

const isProd = import.meta.env.MODE === 'production'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: isProd, // 운영환경일때 쿠키 전송
});

// 개발환경일 때만 Authorization 헤더 적용
if (!isProd) {
    const savedToken = localStorage.getItem('token')
    if (savedToken) {
        api.defaults.headers.common['Authorization'] = `Bearer ${savedToken}`
    }
}

// 개발환경에서 로그인 시 토큰 저장 함수
export function setAuthToken(token) {
    if (!isProd) {
        if (token) {
            api.defaults.headers.common['Authorization'] = `Bearer ${token}`
            localStorage.setItem('token', token)
        } else {
            delete api.defaults.headers.common['Authorization']
            localStorage.removeItem('token')
        }
    }
}
export default api