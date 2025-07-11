<template>
  <div class="max-w-sm mx-auto mt-20 p-6 border rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-center">{{ $t('login') }}</h2>

    <form @submit.prevent="handleLogin">
      <input
          v-model="email"
          type="email"
          :placeholder="$t('email')"
          class="input input-bordered w-full mb-3"
          required
      />
      <input
          v-model="password"
          type="password"
          :placeholder="$t('password')"
          class="input input-bordered w-full mb-3"
          required
      />

      <button class="btn btn-primary w-full" :disabled="loading">
        {{ loading ? $t('logging') : $t('login')  }}
      </button>

      <p class="text-red-500 text-sm mt-2" v-if="error">{{ error }}</p>
    </form>
    <div class="flex justify-center mt-4">
      <GoogleLogin />
    </div>
    <p class="mt-4 text-center text-sm text-gray-600">
      {{ $t("login_p1") }}
      <router-link to="/register" class="text-blue-600 hover:underline">
        {{ $t("signup") }}
      </router-link>
    </p>
  </div>
</template>

<script setup>
import GoogleLogin from "@/components/auth/GoogleLogin.vue"
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api, { setAuthToken } from '@/lib/axios'
import { useUserStore } from '@/stores/user'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const router = useRouter()
const userStore = useUserStore()

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const res = await api.post('/login', { email: email.value, password: password.value })
    const token = res.data.token
    setAuthToken(token)  // axios 헤더 + localStorage 저장

    // 로그인 성공 후
    userStore.setUser(response.data.user, response.data.token)

    router.push('/list')     // 홈이나 원하는 페이지로 이동
  } catch (err) {
    error.value = '이메일 또는 비밀번호가 잘못되었습니다.'
  } finally {
    loading.value = false
  }
}
</script>
