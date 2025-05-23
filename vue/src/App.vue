<template>
  <div class="app-layout">
    <img :src="bg1" alt="왼쪽 배경" class="left-bg" />
    <img :src="bg2" alt="오른쪽 배경" class="right-bg" />
    <div class="content">
      <navbar />
      <router-view />
      <Footer />
    </div>

  </div>
</template>

<script setup>
import navbar from '@/components/navbar.vue'
import bg1 from '@/assets/bg.png'
import bg2 from '@/assets/hero-bg.png'
import Footer from '@/components/Footer.vue'
import Navbar from "@/components/navbar.vue";
</script>

<style scoped>
.app-layout {
  display: flex;
  align-items: flex-start; /* 위쪽 정렬 */
  justify-content: center;
}

.left-bg {
  display: block;
  width: auto;
  height: auto;
  margin: 0;
  padding: 0;
}
.right-bg {
  position: absolute;
  top: 0;
  right: 0;
  width: 30%;
  height: auto;
  margin: 0;
  padding: 0;
  opacity: 0.3;
  z-index: 10;
  pointer-events: none;
}

.content {
  flex: 1;
  padding: 10px;
  width: 100%;
  max-width: 1400px;
}
.content-area {
  width: 100%;
  margin: 0 auto;
  padding: 3rem;
  box-sizing: border-box;
  overflow-x: auto;
}
</style>
