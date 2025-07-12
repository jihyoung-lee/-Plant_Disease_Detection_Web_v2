<template>
  <div class="p-4">
    <Loading v-if="loading" />
    <div v-else-if="error" class="text-red-500">{{ error }}</div>

    <!-- 데이터 없음 상태 -->
    <div
        v-else-if="items.length === 0"
        class="flex flex-col items-center justify-center h-[400px] text-center text-success bg-base-100 border-2 border-dashed border-success rounded-lg"
    >
      <svg
          xmlns="http://www.w3.org/2000/svg"
          class="w-16 h-16 mb-4 text-success"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7a2 2 0 012-2h3l2-2h2l2 2h3a2 2 0 012 2v12a2 2 0 01-2 2z" />
      </svg>
      <h2 class="text-lg font-semibold text-success">{{ $t('analysis_h2') }}</h2>
      <p class="text-sm mb-4 text-success">{{ $t('analysis_p1') }}</p>
      <button @click="openModal" class="btn btn-success btn-sm animate-bounce hover:scale-105 transition-transform duration-200">
        {{ $t('analysis_btn1') }}
      </button>
    </div>

    <!-- 데이터가 있을 때 -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="item in items" :key="item.id" class="card shadow-md bg-base-100">
        <figure>
          <img :src="item.url" alt="사진" class="w-full h-48 object-cover"/>
        </figure>
        <div class="card-body">
          <h3 class="card-title">
            {{ item.cropName }} -
            <a v-if="item.link" :href="item.link" class="text-success underline">
              {{ item.sickNameKor }}
            </a>
            <span v-else>{{ item.sickNameKor }}</span>
          </h3>
          <p>신뢰도: {{ (item.confidence ?? 0).toFixed(2) }}%</p>
          <p class="text-sm text-gray-400">업로드: {{ new Date(item.created_at).toLocaleDateString() }}</p>
          <p v-if="item.userOpinion" class="text-sm text-success">사용자 의견: {{ item.userOpinion }}</p>
          <button class="btn btn-sm btn-accent mt-2" @click="openOpinionModal(item)">의견 남기기</button>
          <button class="btn btn-sm btn-error mt-2" @click="deleteItem(item.id)">
            삭제
          </button>
        </div>
      </div>
    </div>
  </div>

  <OpinionModal ref="modalRef" :targetItem="selectedItem" @sent="fetchResults" />

  <!-- 페이지네이션 -->
  <div class="pagination mt-6 text-center" v-if="pagination && pagination.total > pagination.per_page">
    <div class="join">
      <button
          class="join-item btn"
          v-for="n in pagination.last_page"
          :key="n"
          :class="{ 'btn-active': String(route.query.page || '1') === String(n) }"
          @click="goToPage(n)"
      >
        {{ n }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/lib/axios'
import Loading from '@/components/Loading.vue'
import OpinionModal from '@/components/OpinionModal.vue'
import { inject } from 'vue'

const aiModal = inject('aiModal')

function openModal() {
  aiModal?.value?.openModal()
}

const items = ref([])
const pagination = ref(null)
const loading = ref(true)
const error = ref(null)

const modalRef = ref(null)
const selectedItem = ref(null)

const route = useRoute()
const router = useRouter()

// 병해충 도감 링크 조회
const fetchDiseaseInfo = async (item) => {
  try {
    const res = await api.get(`/disease-info`, {
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
  } catch {
    item.link = null
  }
}
//의견 모달
const openOpinionModal = (item) => {
  selectedItem.value = item
  modalRef.value.open()
}

// 페이지 전환 함수
const goToPage = (page) => {
  if (page !== Number(route.query.page || 1)) {
    router.push({ query: { ...route.query, page } })
  }
}

// 데이터 로딩 함수
const fetchResults = async () => {
  loading.value = true
  error.value = null

  const page = route.query.page || 1

  try {
    const res = await api.get(`/results`, {
      params: { page }
    })

    items.value = res.data.data || []

    pagination.value = {
      total: res.data.total,
      per_page: res.data.per_page,
      last_page: res.data.last_page,
    }

    await Promise.all(items.value.map(item => fetchDiseaseInfo(item)))

  } catch (err) {
    error.value = '데이터를 불러오지 못했어요'
  } finally {
    loading.value = false
  }
}

// 삭제
const deleteItem = async (id) => {
  if (!confirm('정말 삭제할까요?')) return

  try {
    await api.delete(`/results/${id}`)
    await fetchResults() // 삭제 후 새로고침
  } catch {
    alert('삭제 실패')
  }
}

// 초기 로딩 & 페이지 변경 감지
onMounted(fetchResults)
watch(() => route.query.page, fetchResults)
</script>
