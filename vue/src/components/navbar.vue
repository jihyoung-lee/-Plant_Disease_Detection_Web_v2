<template>
  <div class="navbar bg-base-100 shadow-sm w-full px-4">
    <!-- 로고 -->
    <div class="w-10 rounded-full">
      <img alt="logo" :src="logo" />
    </div>

    <!-- 제목 -->
    <div class="flex-1">
      <a class="btn btn-ghost text-xl">{{ route.meta.title || '병해충 AI' }}</a>
    </div>

    <!-- 오른쪽 영역: 검색 + 로그인/프로필 -->
    <div class="flex items-center gap-4">
      <SearchInput />

      <div v-if="user" class="flex items-center gap-2">
        <router-link to="/profile" class="btn btn-outline btn-sm">{{ user.name }}</router-link>
      </div>
      <router-link v-else to="/login" class="btn btn-sm">로그인</router-link>
    </div>
  </div>
</template>


<script setup>
import logo from '@/assets/farmer.svg'
import api, { setAuthToken } from '@/lib/axios.js'
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import SearchInput from "@/components/SearchInput.vue"
import Modal from "@/components/Modal.vue"

const route = useRoute()
const user = ref(null)

onMounted(async () => {
  const token = localStorage.getItem('token')

  if (token) {
    setAuthToken(token) // axios 인스턴스에 Authorization 헤더 세팅
    console.log('🟡 Token 설정됨:', token)
  } else {
    console.warn('🔴 토큰이 없습니다. 로그인 필요.')
    return
  }

  try {
    const response = await api.get('/me')
    user.value = response.data
    console.log('🟢 사용자 정보 불러오기 성공:', user.value)
  } catch (error) {
    console.error('🔴 사용자 정보 불러오기 실패:', error?.response?.data || error.message)
    user.value = null
  }
})
</script>
