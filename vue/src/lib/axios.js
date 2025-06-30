import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true, // 쿠키 자동 포함
});
let isRefreshing = false;

api.interceptors.response.use(
    res => res,
    async err => {
        const orig = err.config;
        if (!err.response) return Promise.reject(err);

        // auth 관련 경로는 인터셉터에서 그냥 건너뛰기
        const skip = ['/login', '/register', '/refresh', '/me'].some(p => orig.url.includes(p));
        if (skip) return Promise.reject(err);

        if (err.response.status === 401 && !orig._retry && !isRefreshing) {
            orig._retry = true;
            isRefreshing = true;
            try {
                await api.post('/refresh');
                return api(orig);
            } catch (e) {
                return Promise.reject(e);
            } finally {
                isRefreshing = false;
            }
        }

        return Promise.reject(err);
    }
);

export default api;
