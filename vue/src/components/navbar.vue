<template>
<div class="navbar bg-base-100 shadow-sm w-full px-4">
  <div class="w-10 rounded-full">
    <img
        alt="logo"
        :src="logo"/>
  </div>
<div class="flex-1">
  <a class="btn btn-ghost text-xl"> {{ route.meta.title || '병해충 AI' }}</a>
</div>
  <modal />
  <div class="flex-none">
    <SearchInput />
  </div>
  <router-link to="/register" class="btn">회원가입</router-link>
</div>
</template>
<script setup>
import logo from '@/assets/farmer.svg'
import { useRoute } from 'vue-router'
import SearchInput from "@/components/SearchInput.vue";
import Modal from "@/components/Modal.vue";

const route = useRoute()
</script>