<template>
  <div class="max-w-md mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">회원가입</h2>
    <form @submit.prevent="register">
      <input v-model="form.name" class="input input-bordered w-full mb-2" placeholder="이름" />
      <input v-model="form.email" class="input input-bordered w-full mb-2" placeholder="이메일" />
      <input v-model="form.password" type="password" class="input input-bordered w-full mb-2" placeholder="비밀번호" />
      <input v-model="form.password_confirmation" type="password" class="input input-bordered w-full mb-4" placeholder="비밀번호 확인" />

      <button class="btn btn-primary w-full">가입하기</button>
      <p class="text-red-500 mt-2" v-if="error">{{ error }}</p>
    </form>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
axios.defaults.withCredentials = true // 쿠키 허용

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const error = ref('')

const register = async () => {
  try {
    // 1. CSRF 쿠키 요청
    await axios.get('/sanctum/csrf-cookie', {
      withCredentials: true
    })

    // 2. 회원가입 요청
    await axios.post('/register', form.value)

    // 3. 로그인
    await axios.post('/login', {
      email: form.value.email,
      password: form.value.password
    })

    // 4. 이메일 인증 요청 (이때 user()가 null이면 이 부분에서 오류 발생함)
    await axios.post('/api/email/verification-notification')

    // 5. 완료 후 이동
    alert('가입 완료! 이메일 인증 링크를 확인해주세요.')
    router.push('/list')

  } catch (err) {
    error.value =
        err.response?.data?.errors
            ? Object.values(err.response.data.errors).flat().join('\n')
            : err.response?.data?.message || '가입 실패'
  }
}
</script>
