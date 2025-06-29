<template>
  <div class="max-w-md mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">회원가입</h2>

    <form @submit.prevent="handleSubmit">
      <!-- 1단계: 기본정보 입력 -->
      <div v-if="step === 1">
        <input v-model="form.name" class="input input-bordered w-full mb-2" placeholder="이름" required />

        <div class="flex items-center gap-2 mb-2">
          <div class="relative w-full">
            <input v-model="form.email"
                   type="email"
                   class="input input-bordered w-full pr-10"
                   placeholder="이메일"
                   required
                   @blur="validateEmail"
            />
            <!-- 상태 아이콘 -->
            <span v-if="emailStatus === 'checking'" class="absolute right-3 top-1/2 -translate-y-1/2 loading loading-spinner loading-xs"></span>
            <span v-if="emailStatus === 'invalid'" class="absolute right-3 top-1/2 -translate-y-1/2 text-error">✗</span>
            <span v-if="emailStatus === 'duplicate'" class="absolute right-3 top-1/2 -translate-y-1/2 text-error">✗</span>
            <span v-if="emailStatus === 'valid'" class="absolute right-3 top-1/2 -translate-y-1/2 text-success">✓</span>
          </div>

          <button type="submit" class="btn btn-success w-24 shrink-0" :disabled="emailStatus !== 'valid' || loading">
            인증
          </button>
        </div>

        <p v-if="emailError" class="text-xs text-error mt-1">{{ emailError }}</p>
      </div>

      <!-- 2단계: 인증번호 입력 -->
      <div v-if="step === 2">
        <p class="mb-2 text-sm text-gray-700">이메일로 전송된 인증번호 6자리를 입력해주세요.</p>
        <div class="flex justify-between gap-2 mb-4">
          <input
              v-for="(digit, index) in verificationCode"
              :key="index"
              v-model="verificationCode[index]"
              ref="codeInputs"
              maxlength="1"
              type="text"
              inputmode="numeric"
              class="input input-bordered w-10 text-center"
              @input="handleCodeInput(index + 1, $event)"
              @keydown="handleCodeDelete(index + 1, $event)"
          />
        </div>

        <button type="submit" class="btn btn-primary w-full" :disabled="verifying">
          <span v-if="verifying">확인 중...</span>
          <span v-else>인증 완료</span>
        </button>

        <button type="button" class="btn btn-ghost w-full mt-2" @click="resendVerification" :disabled="resendCooldown > 0">
          <span v-if="resendCooldown > 0">{{ resendCooldown }}초 후 재전송 가능</span>
          <span v-else>인증번호 다시 받기</span>
        </button>

        <div v-if="error" class="mt-4 p-4 bg-red-50 text-red-600 rounded-md">
          <p class="font-medium">오류 발생:</p>
          <p class="mt-1 whitespace-pre-line">{{ error }}</p>
        </div>
      </div>

      <!-- 3단계: 비밀번호 입력 -->
      <div v-if="step === 3">
        <input v-model="form.password" type="password" class="input input-bordered w-full mb-2" placeholder="비밀번호" required minlength="8" />
        <input v-model="form.password_confirmation" type="password" class="input input-bordered w-full mb-4" placeholder="비밀번호 확인" required minlength="8" />
        <button type="submit" class="btn btn-primary w-full" :disabled="loading">
          가입 완료
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, nextTick, onUnmounted } from 'vue'
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
const emailStatus = ref('')
const emailError = ref('')
const resendCooldown = ref(0)
let cooldownInterval = null
const loading = ref(false)

// 이메일 유효성 검사
const validateEmail = async () => {
  if (!form.value.email) return

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.value.email)) {
    emailStatus.value = 'invalid'
    emailError.value = '유효한 이메일 형식이 아닙니다'
    return
  }

  emailStatus.value = 'checking'
  emailError.value = ''

  try {
    const { data } = await api.get('/check-email', {
      params: { email: form.value.email }
    })

    if (data.exists) {
      emailStatus.value = 'duplicate'
      emailError.value = '이미 사용 중인 이메일입니다'
    } else {
      emailStatus.value = 'valid'
    }
  } catch (err) {
    emailStatus.value = ''
    emailError.value = '이메일 확인에 실패했습니다'
  }
}

// 인증번호 전송
const notification = async () => {
  error.value = ''
  loading.value = true

  try {
    const res = await api.post('/notification', {
      email: form.value.email,
      name: form.value.name
    })

    if (res.data.success) {
      step.value = 2
      startCooldownTimer(res.data.expires_in || 180)

      nextTick(() => {
        if (codeInputs.value[0]) codeInputs.value[0].focus()
      })

      alert('인증번호가 전송되었습니다.')
    } else {
      throw new Error(res.data.message || '전송 실패')
    }
  } catch (err) {
    emailError.value = err.response?.data?.message || err.message || '에러 발생'
  } finally {
    loading.value = false
  }
}

// 인증번호 확인
const verify = async () => {
  error.value = ''
  verifying.value = true

  try {
    const code = verificationCode.value.join('')
    await api.post('/verify', {
      email: form.value.email,
      code: code
    })
    alert('인증 완료!')
    step.value = 3
  } catch (err) {
    handleError(err)
    verificationCode.value = Array(6).fill('')
    if (codeInputs.value[0]) codeInputs.value[0].focus()
  } finally {
    verifying.value = false
  }
}

const completeRegistration = async () => {
  error.value = ''
  loading.value = true

  try {
    const payload = {
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    }

    await api.post('/register', payload)

    await api.post('/login', {
      email: form.value.email,
      password: form.value.password
    })
    // 수정필요
    setAuthToken(response.data.token);

    alert('회원가입 완료!')
    router.push('/list')
  } catch (err) {
    handleError(err)
  } finally {
    loading.value = false
  }
}

// 단계별 핸들링
const handleSubmit = () => {
  if (step.value === 1) {
    notification()
  } else if (step.value === 2) {
    verify()
  } else if (step.value === 3) {
    completeRegistration()
  }
}

// 인증번호 입력 처리
const handleCodeInput = (index, e) => {
  if (e.target.value && index < 6) {
    const inputs = document.querySelectorAll('input[type="text"]')
    if (inputs[index]) inputs[index].focus()
  }
}
const handleCodeDelete = (index, e) => {
  if (e.key === 'Backspace' && !e.target.value && index > 1) {
    const inputs = document.querySelectorAll('input[type="text"]')
    if (inputs[index - 2]) inputs[index - 2].focus()
  }
}

// 재전송
const resendVerification = async () => {
  if (resendCooldown.value > 0) return

  try {
    await api.post('/resend-code', { email: form.value.email })
    verificationCode.value = Array(6).fill('')
    if (codeInputs.value[0]) codeInputs.value[0].focus()
    startCooldownTimer()
  } catch (err) {
    handleError(err)
  }
}

// 타이머
const startCooldownTimer = (time = 180) => {
  clearInterval(cooldownInterval)
  resendCooldown.value = time
  cooldownInterval = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) clearInterval(cooldownInterval)
  }, 1000)
}

// 공통 에러 처리
const handleError = (err) => {
  if (err.response) {
    if (err.response.status === 422) {
      error.value = Object.values(err.response.data.errors).flat().join('\n')
    } else {
      error.value = err.response.data.message || '서버 오류'
    }
  } else {
    error.value = '네트워크 오류, 다시 시도해주세요.'
  }
}

// 언마운트 시 타이머 정리
onUnmounted(() => {
  clearInterval(cooldownInterval)
})
</script>
