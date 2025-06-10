<template>
  <button class="btn btn-outline btn-accent" onclick="my_modal_3.showModal()">AI진단</button>

  <dialog id="my_modal_3" class="modal">
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
            <option v-for="crop in cropOptions" :key="crop" :value="crop">{{ crop }}</option>
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
              <a v-if="item.link" :href="item.link" class="text-green-400 underline">{{ item.sickNameKor }}</a>
              <span v-else>{{ item.sickNameKor }}</span>
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
import axios from 'axios'

// 상태
const services = ref([])
const error = ref('')
const loading = ref(false)
const photo = ref(null) // 선택된 파일 저장
const previewUrl = ref(null)
const selectedCrop = ref('') // 작물 선택값
const cropOptions = ref(['감자', '딸기', '토마토', '복숭아', '후추', '체리','블루베리','옥수수','포도']) // 필요 시 더 추가 가능
// 파일 선택 핸들러
function onFileChange(event) {
  photo.value = event.target.files[0]
  previewUrl.value = URL.createObjectURL(photo.value)
}

// 데이터 요청
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
  services.value = [] // 이전 결과 초기화

  try {
    const formData = new FormData()
    formData.append('image', photo.value)
    formData.append('cropName', selectedCrop.value)

    const res = await axios.post(`http://127.0.0.1/api/predict`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    const data = res.data.data
    services.value = [{
      cropName: data.cropName,
      sickNameKor: data.sickNameKor,
      confidence: data.confidence,
      link: null
    }]
    await fetchDiseaseInfo(services.value[0]) //도감 조회
  } catch (err) {
    error.value = 'API 요청 실패: ' + (err.response?.data?.error || err.message)
  } finally {
    loading.value = false
  }
}
async function fetchDiseaseInfo(item) {
  try {
    const res = await axios.get(`http://127.0.0.1/api/disease-info`, {
      params: { cropName: item.cropName, sickNameKor: item.sickNameKor }
    });
    if (res.data) {
      item.link = `/disease/${encodeURIComponent(item.cropName)}/${encodeURIComponent(item.sickNameKor)}`;
    } else {
      item.link = null;
    }
  } catch (err) {
    item.link = null;
  }
}
</script>
