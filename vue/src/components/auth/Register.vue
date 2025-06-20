<template>
  <!--
  <div class="max-w-md mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">회원가입</h2>
    <form @submit.prevent="register">
      <input v-model="form.name" class="input input-bordered w-full mb-2" placeholder="이름" required />
      <input v-model="form.email" type="email" class="input input-bordered w-full mb-2" placeholder="이메일" required />
      <button type="submit" class="btn btn-primary w-full" :disabled="loading">
        인증번호 전송
      </button>
      <input v-model="form.password" type="password" class="input input-bordered w-full mb-2" placeholder="비밀번호" required minlength="8" />
      <input v-model="form.password_confirmation" type="password" class="input input-bordered w-full mb-4" placeholder="비밀번호 확인" required minlength="8" />

      <button type="submit" class="btn btn-primary w-full" :disabled="loading">
        <span v-if="loading" class="flex items-center justify-center">
          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          처리 중...
        </span>
        <span v-else>가입하기</span>
      </button>

      <div v-if="error" class="mt-4 p-4 bg-red-50 text-red-600 rounded-md">
        <p class="font-medium">오류 발생:</p>
        <p class="mt-1">{{ error }}</p>
      </div>
    </form>
  </div>
  -->

  <div class="max-w-md mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">회원가입</h2>

    <!-- 1단계 기본정보 -->
    <form @submit.prevent="register" v-if="step === 1">
      <input v-model="form.name" class="input input-boreder w-full mb-2" placeholder="이름" required />
      <div class="relative">
      <input v-model="form.email"
             type="email"
             class="input input-bordered w-full mb-2"
             placeholder="이메일"
             required
             @blur="validateEmail"
      />
      <span v-if="emailStatus === 'checking'" class="absolute right-3 top-3 loading loading-spinner loading-xs"></span>
      <span v-if="emailStatus === 'invalid'" class="absolute right-3 top-3 text-error">✗</span>
      <span v-if="emailStatus === 'duplicate'" class="absolute right-3 top-3 text-error">✗</span>
      <span v-if="emailStatus === 'valid'" class="absolute right-3 top-3 text-success">✓</span>
      </div>
      <p v-if="emailError" class="text-xs text-error mt-1">{{ emailError }}</p>
  <input v-model="form.password" type="password" class="input input-bordered w-full mb-2" placeholder="비밀번호" required minlength="8" />
      <input v-model="form.password_confirmation" type="password" class="input input-bordered w-full mb-4" placeholder="비밀번호 확인" required minlength="8" />
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/lib/axios'

const router = useRouter()

const step = ref(1)
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})
const verificationCode = ref(Array(6).fill(''))
const codeInputs = ref([])
const verifying = ref(false)
const error = ref('')
const emailStatus = ref('') // '', 'checking', 'invalid', 'duplicate', 'valid'
const emailError = ref('')
const resendCooldown = ref(0)
let cooldownInterval = null
const loading = ref(false)

// 이메일 유효성 검사
const validateEmail = async () =>{
  if (!form.value.email) {
    emailStatus.value = ''
    emailError.value = ''
    return
  }

  // 기본 형식 검사
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.value.email)) {
    emailStatus.value = 'invalid'
    emailError.value = '유효한 이메일 형식이 아닙니다'
    return
}
  emailStatus.value = 'checking'
  emailError.value = ''
  try {
  // 이메일 중복 확인
  const { data } = await api.get('/check-email', {
    params: { email: form.value.email }
  })

  if (data.exists){
    emailStatus.value = 'duplicate'
    emailError.value = '이미 사용 중인 이메일입니다'
  } else {
    emailStatus.value = 'valid'
    emailError.value = ''
  }
} catch (err) {
  emailStatus.value = ''
  emailError.value = '이메일 확인에 실패했습니다'
}
const register = async () => {
  error.value = ''
  loading.value = true

  try {
    // 회원가입 요청
    await api.post('/register', form.value)

    // 자동 로그인
    await api.post('/login', {
      email: form.value.email,
      password: form.value.password
    })

    // 이메일 인증 요청
    await api.post('/email/verification-notification')

    // 성공 시 알림 및 리다이렉트
    alert('회원가입이 완료되었습니다. 이메일 인증 메일을 확인해주세요.')
    router.push('/list')
  } catch (err) {
    handleError(err)
  } finally {
    loading.value = false
  }
}

const handleError = (err) => {
  if (err.response) {
    if (err.response.status === 422) {
      // 유효성 검사 오류
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join('\n')
    } else {
      // 기타 서버 오류
      error.value = err.response.data.message || '서버 오류가 발생했습니다.'
    }
  } else {
    // 네트워크 오류 등
    error.value = '네트워크 연결에 문제가 있습니다. 다시 시도해주세요.'
  }
}
</script>