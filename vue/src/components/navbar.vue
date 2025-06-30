<template>
  <div class="navbar bg-base-100 shadow-sm w-full px-4">
    <div class="w-10 rounded-full">
      <img alt="logo" :src="logo" />
    </div>

    <div class="flex-1">
      <a class="btn btn-ghost text-xl">{{ route.meta.title || '병해충 AI' }}</a>
    </div>

    <SearchInput />

    <div v-if="auth.user" class="flex items-center gap-2">
      <span>{{ auth.user.name }}</span>
      <button class="btn btn-outline btn-sm" @click="handleLogout">로그아웃</button>
    </div>
    <router-link v-else to="/login" class="btn">로그인</router-link>
  </div>
</template>
<script setup>
import logo from '@/assets/farmer.svg'
import { onMounted } from 'vue'
import { useAuthStore } from '@/lib/auth'
import { useRouter, useRoute } from 'vue-router'
import SearchInput from '@/components/SearchInput.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(async () => {
  const publicPages = ['/login', '/register']
  if (publicPages.includes(route.path)) return

  if (!auth.user) {
    await auth.fetchUser()
    if (!auth.user) router.push('/login')
  }
})

</script>
