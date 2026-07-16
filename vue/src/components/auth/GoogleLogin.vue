<template>
  <GoogleLogin
      :callback="onSuccess"
      @error="onError"
  />
</template>

<script setup>
import { GoogleLogin } from 'vue3-google-login'
import api from '@/lib/axios'
import { useUserStore } from '@/stores/user'
import { useRouter } from 'vue-router'

const userStore = useUserStore()
const router = useRouter()
const onSuccess = async (res) => {

  const googleIdToken = res.credential

  try {
    const response = await api.post('/auth/google', {
      token: googleIdToken
    })

    const jwt = response.data.token
    const user = response.data.user

    await userStore.completeLogin(jwt, user)
    router.push('/')
  } catch (err) {
    console.error('백엔드 로그인 실패:', err.response?.data || err.message)
  }
}

const onError = () => {
  console.error('구글 로그인 실패')
}
</script>
