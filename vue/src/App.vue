<template>
  <div class="app-layout">
    <img :src="bg1" alt="왼쪽 배경" class="left-bg" />
    <img :src="bg2" alt="오른쪽 배경" class="right-bg" />
    <div class="content">
      <navbar />
      <router-view />
      <Modal ref="modalRef" />
      <Footer />
    </div>

  </div>
</template>

<script setup>
import navbar from '@/components/navbar.vue'
import bg1 from '@/assets/bg.png'
import bg2 from '@/assets/hero-bg.png'
import Footer from '@/components/Footer.vue'
import Navbar from "@/components/navbar.vue"
import Modal from "@/components/Modal.vue"
import { ref, provide } from 'vue'

const modalRef = ref(null)
provide('aiModal', modalRef)
</script>

<style scoped>
.app-layout {
  display: flex;
  align-items: flex-start; /* 위쪽 정렬 */
  justify-content: center;
  width: 50vw;
  height: 100vh;
}

.left-bg {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 20vw;
  max-width: 280px;
  height: auto;
  opacity: 0.6;
  z-index: 0;
  pointer-events: none;
}

.right-bg {
  position: absolute;
  top: 0;
  right: 0;
  width: 35vw;         /* 기존보다 확대 */
  max-width: 480px;    /* 최대 사이즈 설정 */
  height: auto;
  opacity: 0.3;
  z-index: 0;
  pointer-events: none;
}

.content {
  width: 100%;
  height: 100%;
  margin: 0 auto;
  box-sizing: border-box;
}
.content-area {
  width: 100%;
  margin: 0 auto;
  padding: 3rem;
  box-sizing: border-box;
  overflow-x: auto;
}
</style>
