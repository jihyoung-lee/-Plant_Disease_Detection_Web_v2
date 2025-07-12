<template>
  <div class="navbar bg-base-100 shadow-sm w-full px-4">
    <!-- 로고 -->
    <div class="w-10 rounded-full">
      <img alt="logo" :src="logo" />
    </div>

    <!-- 제목 -->
    <div class="flex-1">
      <router-link to="/" class="btn btn-ghost text-xl">
        {{ route.meta.title || $t("title") }}
      </router-link>
    </div>

    <!-- 오른쪽 영역 -->
    <div class="flex items-center gap-4">
      <SearchInput />

      <button class="btn btn-outline btn-accent" @click="openModal">
        AI진단
      </button>

      <!-- 로그인 상태일 때 -->
      <div v-if="userStore.user" class="dropdown dropdown-end">
        <label tabindex="0" class="btn">
          {{ userStore.user.name }}
        </label>
        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
          <li><router-link to="/list">{{ $t('analysis') }}</router-link></li>
          <li><router-link to="/logout">{{ $t('logout') }}</router-link></li>
        </ul>
      </div>

      <!-- 비로그인 -->
      <router-link v-else to="/login" class="btn btn-sm">
        {{ $t('login') }}
      </router-link>
    </div>
  </div>
</template>


<script setup>
import logo from '@/assets/farmer.svg'
import SearchInput from "@/components/SearchInput.vue"
import { useRoute } from 'vue-router'
import { onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { inject } from 'vue'

const aiModal = inject('aiModal')
const route = useRoute()
const userStore = useUserStore()

function openModal() {
  aiModal?.value?.openModal()
}

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
