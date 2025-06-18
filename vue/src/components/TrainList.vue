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
          <h3 class="card-title">
            {{ item.cropName }} -
            <a v-if="item.link" :href="item.link" class="text-green-400 underline">
              {{ item.sickNameKor }}
            </a>
            <span v-else>{{ item.sickNameKor }}</span>
          </h3>
          <p>신뢰도: {{ (item.confidence ?? 0).toFixed(2) }}%</p>
          <p class="text-sm text-gray-400">업로드: {{ new Date(item.created_at).toLocaleDateString() }}</p>
          <p v-if="item.userOpinion" class="text-sm text-primary">사용자 의견: {{ item.userOpinion }}</p>

          <button class="btn btn-sm btn-error mt-2" @click="deleteItem(item.id)">
            삭제
          </button>
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

// 삭제 기능
const deleteItem = async (id) => {
  if (!confirm('정말 삭제할까요?')) return

  try {
    await axios.delete(`http://127.0.0.1/api/results/${id}`)
    items.value = items.value.filter(item => item.id !== id)
  } catch (err) {
    alert('삭제 실패')
  }
}

// 도감 링크 조회 함수
const fetchDiseaseInfo = async (item) => {
  try {
    const res = await axios.get(`http://127.0.0.1/api/disease-info`, {
      params: {
        cropName: item.cropName?.trim(),
        sickNameKor: item.sickNameKor?.trim(),
      }
    })

    if (
        res.data &&
        res.data.raw &&
        res.data.raw.service &&
        res.data.raw.service.errorCode === 'ERR_201'
    ) {
      item.link = null
    } else if (res.data && Object.keys(res.data).length > 0) {
      item.link = `/disease/${encodeURIComponent(item.cropName)}/${encodeURIComponent(item.sickNameKor)}`
    } else {
      item.link = null
    }
  } catch (err) {
    item.link = null
  }
}

// 페이지 로드시 실행
onMounted(async () => {
  try {
    const res = await axios.get('http://127.0.0.1/api/results')
    items.value = res.data.data || []

    // 각 아이템에 대해 병해충 링크 확인
    await Promise.all(items.value.map(item => fetchDiseaseInfo(item)))

  } catch (err) {
    error.value = '데이터를 불러오지 못했어요'
  } finally {
    loading.value = false
  }
})
</script>
