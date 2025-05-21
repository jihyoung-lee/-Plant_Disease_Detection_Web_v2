<template>
  <header class="w-full bg-white shadow-sm px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <img src="@/assets/farmer.svg" alt="로고" class="w-8 h-8" />
      <span class="text-lg font-semibold text-green-700">작물 진단</span>
    </div>
    <nav class="flex gap-4 text-sm text-gray-700">
      <RouterLink to="/" class="hover:text-green-600">작물 진단</RouterLink>
      <RouterLink to="/disease" class="hover:text-green-600">병해충 정보</RouterLink>
    </nav>
  </header>
</template>