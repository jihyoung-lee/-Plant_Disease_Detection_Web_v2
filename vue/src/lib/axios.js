import axios from 'axios';

export const isProd = import.meta.env.MODE === 'production'

export function getSavedToken() {
    const token = localStorage.getItem('token')

    // 예전에 undefined가 문자열로 저장된 경우도 같이 정리
    if (!token || token === 'undefined' || token === 'null') {
        localStorage.removeItem('token')
        return null
    }

    return token
}

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: isProd, // 운영환경은 JWT 쿠키로 인증
});

const savedToken = getSavedToken()
if (savedToken) {
    api.defaults.headers.common['Authorization'] = `Bearer ${savedToken}`
}

// 로컬 로그인과 운영환경 구글 로그인에서 받은 JWT 처리
export function setAuthToken(token) {
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
        localStorage.setItem('token', token)
    } else {
        delete api.defaults.headers.common['Authorization']
        localStorage.removeItem('token')
    }
}

export default api
