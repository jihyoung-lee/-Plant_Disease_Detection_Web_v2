<template>
  <div class="p-4">
    <h2 class="text-2xl font-bold mb-4">병해충 판별 결과</h2>

    <Loading v-if="loading" />
    <div v-else-if="error" class="text-red-500">{{ error }}</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="item in items" :key="item.id" class="card shadow-md bg-base-100">
        <figure>
          <img :src="item.url" alt="사진" class="w-full h-48 object-cover"/>
        </figure>
        <div class="card-body">
          <h3 class="card-title">{{ item.cropName }} - {{ item.sickNameKor }}</h3>
          <p>신뢰도: {{ (item.confidence ?? 0).toFixed(2) }}%</p>
          <p class="text-sm text-gray-400">업로드: {{ new Date(item.created_at).toLocaleDateString() }}</p>
          <p v-if="item.userOpinion" class="text-sm text-primary">사용자 의견: {{ item.userOpinion }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Loading from '@/components/Loading.vue'

const items = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const res = await axios.get('http://127.0.0.1/api/results')
    items.value = res.data.data
  } catch (err) {
    error.value = '데이터를 불러오지 못했어요'
  } finally {
    loading.value = false
  }
})
</script>