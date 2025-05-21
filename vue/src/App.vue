<template>
  <!-- 가운데 콘텐츠 -->
    <main class="flex-1 px-4 py-6 relative z-10 max-w-5xl mx-auto w-full">
      <router-view />
    </main>


<script>
import bg1 from '@/assets/bg.png';
import bg2 from '@/assets/hero-bg.png';

export default {
  data() {
    return {
      bg1,
      bg2,
    };
  },
};
</script>
