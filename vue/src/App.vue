<template>
  <div class="app-layout">
    <img :src="bg1" alt="왼쪽 배경" class="left-bg" />
    <img :src="bg2" alt="오른쪽 배경" class="right-bg" />
    <div class="content">
      <router-view />
      <Footer />
    </div>

  </div>
</template>

<script setup>
import bg1 from '@/assets/bg.png'
import bg2 from '@/assets/hero-bg.png'
import Footer from '@/components/Footer.vue'
</script>

<style scoped>
.app-layout {
  display: flex;
  align-items: flex-start; /* 위쪽 정렬 */
  justify-content: flex-start; /* 왼쪽 정렬 */
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
  z-index: -1;
  pointer-events: none;
}

.content {
  flex: 1;
  padding: 10px;
}
</style>
