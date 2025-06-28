<template>
  <div class="max-w-sm mx-auto mt-20 p-6 border rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-center">로그인</h2>

    <form @submit.prevent="handleLogin">
      <input
          v-model="email"
          type="email"
          placeholder="이메일"
          class="input input-bordered w-full mb-3"
          required
      />
      <input
          v-model="password"
          type="password"
          placeholder="비밀번호"
          class="input input-bordered w-full mb-3"
          required
      />

      <button class="btn btn-primary w-full" :disabled="loading">
        {{ loading ? '로그인 중...' : '로그인' }}
      </button>

      <p class="text-red-500 text-sm mt-2" v-if="error">{{ error }}</p>
    </form>
    <p class="mt-4 text-center text-sm text-gray-600">
      아직 회원이 아니신가요?
      <router-link to="/register" class="text-blue-600 hover:underline">
        회원가입하기
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api, { setAuthToken } from '@/lib/axios'

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const router = useRouter()

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const res = await api.post('/login', { email: email.value, password: password.value })
    const token = res.data.token
    setAuthToken(token)  // axios 헤더 + localStorage 저장
    router.push('/list')     // 홈이나 원하는 페이지로 이동
  } catch (err) {
    error.value = '이메일 또는 비밀번호가 잘못되었습니다.'
  } finally {
    loading.value = false
  }
}
</script>
