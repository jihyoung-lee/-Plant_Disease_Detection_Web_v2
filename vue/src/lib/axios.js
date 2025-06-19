import axios from 'axios';
const api = axios.create({
    baseURL: 'http://localhost/api', // /api 접두사 추가
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// CSRF 토큰 자동 처리 인터셉터
api.interceptors.request.use(async (config) => {
    // GET 요청이면서 CSRF 토큰 요청이 아닌 경우에만 헤더 추가
    if (config.method?.toUpperCase() !== 'GET' || config.url !== '/sanctum/csrf-cookie') {
        const token = getCookie('XSRF-TOKEN');

        if (token) {
            config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
        } else {
            // 토큰이 없는 경우 먼저 토큰 요청
            await axios.get('/sanctum/csrf-cookie', {
                baseURL: 'http://localhost',
                withCredentials: true
            });
            const newToken = getCookie('XSRF-TOKEN');
            if (newToken) {
                config.headers['X-XSRF-TOKEN'] = decodeURIComponent(newToken);
            }
        }
    }

    config.headers['X-Requested-With'] = 'XMLHttpRequest';
    return config;
});

// 에러 처리 인터셉터
api.interceptors.response.use(
    response => response,
    async (error) => {
        if (error.response?.status === 419) {
            // CSRF 토큰 만료 시 새로 갱신
            await axios.get('/sanctum/csrf-cookie', {
                baseURL: 'http://localhost',
                withCredentials: true
            });
            // 원래 요청 재시도
            return api.request(error.config);
        }
        return Promise.reject(error);
    }
);

// 쿠키에서 값 추출 헬퍼 함수
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

export default api;