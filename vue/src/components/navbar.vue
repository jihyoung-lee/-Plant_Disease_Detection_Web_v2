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
      <!-- 로그인 상태일 때 -->
      <div v-if="userStore.user" class="dropdown dropdown-end">
        <label tabindex="0" class="btn">
          {{ userStore.user.name }}
        </label>
        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
          <li><button @click="openModal" >{{ $t('analysis') }}</button></li>
          <li><router-link to="/list">{{ $t('reports') }}</router-link></li>
          <li><router-link to="/logout">{{ $t('logout') }}</router-link></li>
        </ul>
      </div>

      <!-- 비로그인 -->
      <router-link v-else to="/login" class="btn btn-sm">
        {{ $t('login') }}
      </router-link>
      <select class="select select-bordered select-xs w-24" :value="locale" @change="changeLang">
        <option value="ko">한국어</option>
        <option value="en">English</option>
      </select>
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

import { useI18n } from 'vue-i18n'

const { locale } = useI18n()

const aiModal = inject('aiModal')
const route = useRoute()
const userStore = useUserStore()

function openModal() {
  aiModal?.value?.openModal()
}

function changeLang(e) {
  locale.value = e.target.value
  localStorage.setItem('lang', e.target.value) // 새로고침에도 기억되게
}

onMounted(async () => {
  if (!userStore.user) {
    await userStore.fetchUser()
  }
})
</script>
