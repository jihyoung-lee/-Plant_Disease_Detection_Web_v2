<template>
  <GoogleLogin @success="onSuccess" @error="onError" />
</template>

<script>
import { GoogleLogin } from 'vue3-google-login'
import api from '@/lib/axios'

const onSuccess = async (res) => {
  const token = res.credential

  // 이 토큰을 라라벨 백엔드로 전달해서 로그인 처리
  const response = await api.post('auth/google', {
    token,
  })

  const jwt = response.data.token
  localStorage.setItem('token', jwt)

  console.log('로그인 성공:', response.data.user)
}
const onError = () => {
  console.error('구글 로그인 실패')
}
</script>


