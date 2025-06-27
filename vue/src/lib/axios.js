import axios from 'axios';

const api = axios.create({
    baseURL: 'http://127.0.0.1:8088/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// 로그인 시 토큰 설정 (회원가입/로그인이 필요한 경우만 사용)
export function setAuthToken(token) {
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        localStorage.setItem('token', token); // 옵션: 로컬 스토리지 저장
    } else {
        delete api.defaults.headers.common['Authorization'];
        localStorage.removeItem('token');
    }
}

// 초기화 시 토큰 복원
const savedToken = localStorage.getItem('token');
if (savedToken) setAuthToken(savedToken);

export default api;