<!-- loading.vue -->
<template>
  <div class="loading-overlay">
    <span class="loading loading-dots loading-lg text-success"></span>
  </div>
</template>

<style scoped>
.loading-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255,255,255,0.6);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
