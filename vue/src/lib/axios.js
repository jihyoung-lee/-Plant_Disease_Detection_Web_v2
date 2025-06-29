// axios.js
import axios from 'axios';

const token = localStorage.getItem('token'); // 저장한 토큰 불러오기

const api = axios.create({
    baseURL: 'http://127.0.0.1:8081/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
});


// 토큰 설정 함수
export function setAuthToken(token) {
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        localStorage.setItem('token', token);
    } else {
        delete api.defaults.headers.common['Authorization'];
        localStorage.removeItem('token');
    }
}

// 초기화 ( 시작 시 저장된 토큰 사용 )
const savedToken = localStorage.getItem('token');
if (savedToken) setAuthToken(savedToken);

// 응답 인터셉터 : 401-> /refresh
api.interceptors.response.use(
    res => res,
    async err => {
        const orig = err.config;

        //access token 만료 -> refresh
        if (err.response?.status === 401 && !orig._retry) {
            orig._retry = true;
            try {
                const { data } = await api.post('/refresh');
                const newToken = data.access_token;

                // 새 토큰 저장 및 헤더 갱신
                setAuthToken(newToken);
                orig.headers['Authorization'] = `Bearer ${newToken}`;

                return api(orig); // 원래 요청 재시도
            } catch (refreshErr){
                // refresh 실패 시  -> 로그아웃 처리 or 에러 반환
                setAuthToken(null);
                return Promise.reject(refreshErr);
            }
        }
        return Promise.reject(err);
    }
);
// ✅ 반드시 api도 export
export default api;

