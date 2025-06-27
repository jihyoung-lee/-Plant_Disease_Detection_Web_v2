<template>
  <div class="navbar bg-base-100 shadow-sm w-full px-4">
    <div class="w-10 rounded-full">
      <img alt="logo" :src="logo" />
    </div>

    <div class="flex-1">
      <a class="btn btn-ghost text-xl">{{ route.meta.title || '병해충 AI' }}</a>
    </div>

    <modal />
    <div class="flex-none">
      <SearchInput />
    </div>

    <div v-if="user" class="flex items-center gap-2">
      <span>{{ user.name }}</span>
      <router-link to="/profile" class="btn btn-outline btn-sm">Profile</router-link>
    </div>
    <router-link v-else to="/login" class="btn">로그인</router-link>
  </div>
</template>

<script setup>
import logo from '@/assets/farmer.svg'
import axios from '@/lib/axios.js'
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import SearchInput from "@/components/SearchInput.vue"
import Modal from "@/components/Modal.vue"

const route = useRoute()

const user = ref(null)

onMounted(async () => {
  try {
    const response = await axios.get('/user')
    user.value = response.data
  } catch (e) {
    user.value = null
  }
})
</script>