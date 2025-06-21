<template>
<div class="max-w-md mx-auto p-6">
<h2 class="text-2xl font-bold mb-4">회원가입</h2>

<!-- 1단계 기본정보 -->
<form @submit.prevent="register" v-if="step === 1">
  <input v-model="form.name" class="input input-boreder w-full mb-2" placeholder="이름" required />
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
      <span v-if="emailStatus === 'checking'"
            class="absolute right-3 top-1/2 -translate-y-1/2 loading loading-spinner loading-xs"></span>
      <span v-if="emailStatus === 'invalid'"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-error">✗</span>
      <span v-if="emailStatus === 'duplicate'"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-error">✗</span>
      <span v-if="emailStatus === 'valid'"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-success">✓</span>
    </div>
    <button
        type="button"
        class="btn btn-success btn-sm"
        @click="notification"
        :disabled="emailStatus !== 'valid' || loading"
    >
      인증
    </button>
  </div>
  <p v-if="emailError" class="text-xs text-error mt-1">{{ emailError }}</p>
  <input v-model="form.password" type="password" class="input input-bordered w-full mb-2" placeholder="비밀번호" required minlength="8" />
  <input v-model="form.password_confirmation" type="password" class="input input-bordered w-full mb-4" placeholder="비밀번호 확인" required minlength="8" />
</form>

</div>
<!-- 2단계 인증번호 입력 -->
<form @submit.prevent="verify" v-if="step === 2">
<p class="mb-2">이메일로 전송된 인증번호 6자리를 입력해주세요.</p>
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
    <span v-if="verifying" class="flex items-center justify-center">
      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      확인 중...
    </span>
  <span v-else>인증 완료</span>
</button>

<!-- 재전송 버튼 -->
<button type="button" class="btn btn-ghost w-full mt-2" @click="resendVerification" :disabled="resendCooldown > 0">
  <span v-if="resendCooldown > 0">{{ resendCooldown }}초 후 재전송 가능</span>
  <span v-else>인증번호 다시 받기</span>
</button>

<!-- 오류 메시지 -->
<div v-if="error" class="mt-4 p-4 bg-red-50 text-red-600 rounded-md">
  <p class="font-medium">오류 발생:</p>
  <p class="mt-1 whitespace-pre-line">{{ error }}</p>
</div>
</form>
</template>


<script setup>
import { ref, onUnmounted, nextTick } from 'vue'
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
const validateEmail = async () => {
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
    const {data} = await api.get('/check-email', {
      params: {email: form.value.email}
    })

    if (data.exists) {
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
}

// 인증번호 전송
const notification = async () => {
  error.value = '';
  loading.value = true;

  try {
    // API 호출 (응답 데이터 받기)
    const response = await api.post('/notification', {
      email: form.value.email,
      name: form.value.name
    });

    // 응답 데이터 활용
    if (response.data.success) {
      step.value = 2;
      startCooldownTimer(response.data.expires_in || 180); // 기본값 3분

      // 첫 번째 입력 필드에 포커스
      nextTick(() => {
        if (codeInputs.value[0]) {
          codeInputs.value[0].focus();
        }
      });

      // 성공 메시지 표시 (옵션)
      alert('인증번호가 전송되었습니다. 이메일을 확인해주세요.');
    } else {
      throw new Error(response.data.message || '인증번호 전송에 실패했습니다');
    }
  } catch (err) {
    // 상세한 에러 처리
    if (err.response) {
      emailError.value = err.response.data.message ||
          `서버 오류 (${err.response.status})`;
    } else {
      emailError.value = err.message || '네트워크 연결에 문제가 있습니다';
    }

    // 로그 기록 (개발용)
    console.error('인증번호 전송 실패:', err);
  } finally {
    loading.value = false;
  }
};
  const verify = async () => {
    error.value = ''
    verifying.value = true

    try {
      const code = verificationCode.value.join('')
      await api.post('/verify',{
        email : form.value.email,
        code : code
      })
      // 회원가입 완료
      await completeRegistration()
    } catch (err) {
      handleError(err)
      verificationCode.value = Array(6).fill('')
      if (codeInputs.value[0]) codeInputs.value[0].focus()
    }finally {
      verifying.value = false
    }
  }

  const completeRegistration = async () => {
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
      // 성공 시 알림 및 리다이렉트
      alert('회원가입이 완료되었습니다.')
      router.push('/list')
    } catch (err) {
      handleError(err)
    } finally {
      loading.value = false
    }
  }
  // 인증번호 재전송
  const resendVerification = async () => {
    if (resendCooldown.value > 0) return

    try {
      await api.post('/resend-code', {
        email: form.value.email
      })
      verificationCode.value = Array(6).fill('')
      if (codeInputs.value[0]) codeInputs.value[0].focus()
      startCooldownTimer()
    } catch (err) {
      handleError(err)
    }
  }

// 인증번호 입력 처리
  const handleCodeInput = (index, event) => {
    if (event.target.value && index < 6 && codeInputs.value[index]) {
      codeInputs.value[index].focus()
    }
  }

  const handleCodeDelete = (index, event) => {
    if (event.key === 'Backspace' && !event.target.value && index > 1 && codeInputs.value[index-2]) {
      codeInputs.value[index-2].focus()
    }
  }
  // 재전송 타이머
  const startCooldownTimer = () => {
    clearInterval(cooldownInterval)
    resendCooldown.value = 180 // 3분

    cooldownInterval = setInterval(() => {
      resendCooldown.value--
      if (resendCooldown.value <= 0) {
        clearInterval(cooldownInterval)
      }
    }, 1000)
  }
  // 오류 처리
  const handleError = (err) => {
    if (err.response) {
      if (err.response.status === 422) {
        error.value = Object.values(err.response.data.errors).flat().join('\n')
      } else {
        error.value = err.response.data.message || '서버 오류가 발생했습니다.'
      }
    } else {
      error.value = '네트워크 연결에 문제가 있습니다. 다시 시도해주세요.'
    }
  }
// 컴포넌트 언마운트 시 정리
  onUnmounted(() => {
    clearInterval(cooldownInterval)
  })

</script>