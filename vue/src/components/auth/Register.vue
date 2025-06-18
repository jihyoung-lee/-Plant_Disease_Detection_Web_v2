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

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const error = ref('')
const register = async () => {
  try {
    const res = await axios.post('http://127.0.0.1/api/register', form.value)
    console.log('가입 성공:', res.data)
    alert('가입 완료! 이메일 인증 링크를 확인해주세요.')
    router.push('/login')
  } catch (err) {
    error.value =
        err.response?.data?.errors
            ? Object.values(err.response.data.errors).flat().join('\n')
            : err.response?.data?.message || '가입 실패'
  }
}
</script>
