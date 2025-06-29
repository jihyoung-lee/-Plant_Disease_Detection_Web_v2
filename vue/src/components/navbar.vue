<template>
  <div class="navbar bg-base-100 shadow-sm w-full px-4">
    <div class="w-10 rounded-full">
      <img alt="logo" :src="logo" />
    </div>

    <div class="flex-1">
      <a class="btn btn-ghost text-xl">{{ route.meta.title || '병해충 AI' }}</a>
    </div>

    <SearchInput />

    <div v-if="user" class="flex items-center gap-2">
      <span>{{ user.name }}</span>
      <button class="btn btn-outline btn-sm" @click="logout">로그아웃</button>
    </div>
    <router-link v-else to="/login" class="btn">로그인</router-link>
  </div>
</template>

<script setup>
import logo from '@/assets/farmer.svg'
import api, { removeAuthToken, setAuthToken } from '@/lib/axios.js'
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SearchInput from "@/components/SearchInput.vue"

const route = useRoute()
const router = useRouter()
const user = ref(null)

const logout = async () => {
  try {
    await api.post('/logout')  // 백엔드로 로그아웃 요청
  } catch (err) {
    console.warn('서버 로그아웃 실패:', err.message)  // 어차피 클라쪽 정리 중요함
  } finally {
    removeAuthToken()
    router.push('/login')  // 로그인 페이지로 이동
  }
}

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) return

  setAuthToken(token)

  try {
    const res = await api.get('/me')
    user.value = res.data
  } catch (err) {
    console.error('사용자 정보 불러오기 실패:', err)
    localStorage.removeItem('token')
    router.push('/login')
  }
})
</script>
