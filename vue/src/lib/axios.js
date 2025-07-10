// axios.js
import axios from 'axios';

const token = localStorage.getItem('token'); // 저장한 토큰 불러오기

const api = axios.create({
    baseURL: 'http://127.0.0.1:8081/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
       // 'Authorization': `Bearer ${token}`,
    },
    withCredentials: true,
});
export function setAuthToken(token) {
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
        localStorage.setItem('token', token)
    } else {
        delete api.defaults.headers.common['Authorization']
        localStorage.removeItem('token')
    }
}

// 초기화용
const savedToken = localStorage.getItem('token')
if (savedToken) {
    setAuthToken(savedToken)
}
export default api
/*
// 이거 추가 (✅ export)
export function setAuthToken(token) {
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        localStorage.setItem('token', token);
    } else {
        delete api.defaults.headers.common['Authorization'];
        localStorage.removeItem('token');
    }
}

// 초기화용
const savedToken = localStorage.getItem('token');
if (savedToken) setAuthToken(savedToken);

// ✅ 반드시 api도 export
export default api;
 */
