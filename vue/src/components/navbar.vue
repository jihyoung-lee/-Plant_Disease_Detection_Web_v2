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

      <div v-if="userStore.user" class="flex items-center gap-2">
        <Modal />
        <router-link to="/profile" class="btn btn-outline btn-sm">{{ userStore.user.name }}</router-link>
      </div>
      <router-link v-else to="/login" class="btn btn-sm">로그인</router-link>
    </div>
  </div>
</template>

<script setup>
import logo from '@/assets/farmer.svg'
import SearchInput from "@/components/SearchInput.vue"
import Modal from "@/components/Modal.vue"
import { useRoute } from 'vue-router'
import { onMounted } from 'vue'
import { useUserStore } from '@/stores/user'

const route = useRoute()
const userStore = useUserStore()

onMounted(async () => {
  if (!userStore.token) {
    console.warn('🔴 토큰 없음')
    return
  }

  if (!userStore.user) {
    await userStore.fetchUser() // ✅ store에서 사용자 정보 불러오기
  }
})
</script>
