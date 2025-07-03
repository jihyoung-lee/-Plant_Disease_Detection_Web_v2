<template>
  <div class="navbar bg-base-100 shadow-sm w-full px-4">
    <!-- 로고 -->
    <div class="w-10 rounded-full">
      <img alt="logo" :src="logo" />
    </div>

    <!-- 제목 -->
    <div class="flex-1">
      <a class="btn btn-ghost text-xl">{{ route.meta.title || $t("title") }}</a>
    </div>

    <!-- 오른쪽 영역 -->
    <div class="flex items-center gap-4">
      <SearchInput />

      <template v-if="userStore.user">
        <button class="btn" popovertarget="popover-1" style="anchor-name:--anchor-1">
          {{ userStore.user.name }}
        </button>
        <ul class="dropdown menu w-52 rounded-box bg-base-100 shadow-sm"
            popover id="popover-1" style="position-anchor:--anchor-1">
          <router-link to="/logout" class="btn btn-sm">{{ $t('logout') }}</router-link>
        </ul>
      </template>

      <router-link v-else to="/login" class="btn btn-sm">{{ $t('login') }}</router-link>
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
