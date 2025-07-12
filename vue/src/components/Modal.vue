<template>
  <dialog id="my_modal_3" class="modal" ref="dialogRef">
    <div class="modal-box">
      <form method="dialog">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
      </form>

      <h3 class="text-lg font-bold">AI 병해 진단 서비스</h3>
      <!-- 작물 선택 & 파일 선택: 정돈-->
      <div class="flex flex-col gap-4 items-center py-4">
        <!-- 작물 선택 -->
        <div class="w-80">
          <label for="crop-select" class="block font-semibold mb-1">작물 선택</label>
          <select id="crop-select" class="select select-bordered w-full"
                  v-model="selectedCrop">
            <option disabled value="">작물을 선택하세요</option>
            <option v-for="crop in cropOptions" :key="crop.value" :value="crop.value">{{ crop.label }}</option>
          </select>
        </div>

        <!-- 파일 선택 -->
        <div class="w-80">
          <label class="block font-semibold mb-1">이미지 업로드</label>
          <input type="file" class="file-input file-input-success w-full" @change="onFileChange" />
        </div>
      </div>
      <!-- 로딩 표시 -->
      <div v-if="loading" class="flex justify-center items-center py-4">
        <span class="loading loading-spinner text-accent loading-lg"></span>
        <span class="ml-2 text-accent font-semibold">AI 분석 중...</span>
      </div>

      <!-- 에러 표시 -->
      <p v-if="error" class="text-red-500 text-center">{{ error }}</p>

      <!-- 결과 카드 표시 -->
      <div v-if="!loading && services.length > 0">
        <div v-for="item in services" :key="item.cropName" class="card bg-base-100 w-96 shadow-sm mx-auto my-4">
          <figure>
            <img v-if="previewUrl" :src="previewUrl" alt="Uploaded Image" />
          </figure>
          <div class="card-body text-center items-center justify-center">
            <h2 class="card-title">
              <a v-if="item.link" :href="item.link" class="text-green-400 underline">
                {{ item.sickNameKor }}
              </a>
              <span v-else>
  {{ item.sickNameKor }}
</span>
              <div class="badge badge-secondary">{{ item.cropName }}</div>
            </h2>
            <div
                class="radial-progress text-success"
                :style="{ '--value': item.confidence }"
                :aria-valuenow="item.confidence"
                role="progressbar"
            >
              {{ item.confidence }}%
            </div>
          </div>
        </div>
      </div>
      <button class="btn btn-outline btn-success" @click="fetchData">진단</button>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/lib/axios'

const router = useRouter()
const dialogRef = ref(null)

defineExpose({
  openModal
})

const services = ref([])
const error = ref('')
const loading = ref(false)
const photo = ref(null)
const previewUrl = ref(null)
const selectedCrop = ref('')
const cropOptions = ref([
  { label: '감자', value: 'potato' },
  { label: '딸기', value: 'strawberry' },
  { label: '토마토', value: 'tomato' },
  { label: '복숭아', value: 'peach' },
  { label: '포도', value: 'grape' }
])

function openModal() {
  const token = localStorage.getItem('token')
  if (!token) {
    alert('로그인이 필요합니다.')
    router.push({ name: 'Login' }) // 로그인 페이지 이름으로 이동
    return
  }

  dialogRef.value?.showModal()
}

function onFileChange(event) {
  photo.value = event.target.files[0]
  previewUrl.value = URL.createObjectURL(photo.value)
}

// 도감 링크 조회 함수
async function fetchDiseaseInfo(item) {
  try {
    const res = await api.get(`disease-info`, {
      params: {
        cropName: item.cropName,
        sickNameKor: item.sickNameKor,
      }
    })

    if (res.data && Object.keys(res.data).length > 0) {
      item.link = `/disease/${encodeURIComponent(item.cropName)}/${encodeURIComponent(item.sickNameKor)}`
    } else {
      item.link = null
    }
  } catch {
    item.link = null
  }
}

// 데이터 요청 함수
async function fetchData() {
  if (!selectedCrop.value) {
    error.value = '작물을 선택해주세요.'
    return
  }

  if (!photo.value) {
    error.value = '이미지를 선택해주세요.'
    return
  }

  loading.value = true
  error.value = ''
  services.value = []

  try {
    const formData = new FormData()
    formData.append('image', photo.value)
    formData.append('cropName', selectedCrop.value)

    const res = await api.post(`/predict`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    const data = res.data.data

    const serviceItem = {
      cropName: data.cropName,
      sickNameKor: data.sickNameKor,
      confidence: data.confidence,
      link: null,
    }

    if (
        res.data &&
        res.data.raw &&
        res.data.raw.service &&
        res.data.raw.service.errorCode === 'ERR_201'
    ) {
      serviceItem.link = null
    } else {
      serviceItem.link = `/disease/${encodeURIComponent(serviceItem.cropName)}/${encodeURIComponent(serviceItem.sickNameKor)}`
    }

    services.value.push(serviceItem)

  } catch (err) {
    error.value = 'API 요청 실패: ' + (err.response?.data?.error || err.message)
  } finally {
    loading.value = false
  }
}

</script>
