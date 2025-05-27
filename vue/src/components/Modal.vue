<template>
  <button class="btn btn-outline btn-accent" onclick="my_modal_3.showModal()">AI진단</button>

  <dialog id="my_modal_3" class="modal">
    <div class="modal-box">
      <form method="dialog">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
      </form>

      <h3 class="text-lg font-bold">AI 병해 진단 서비스</h3>

      <!-- 파일 선택 -->
      <p class="py-4">
        <input type="file" class="file-input file-input-success" @change="onFileChange" />
      </p>

      <!-- 결과 표시 -->
      <div v-if="services.length > 0">
        <p v-for="item in services" :key="item.cropName">
          <strong>작물명:</strong> {{ item.cropName }} / <strong>병명:</strong> {{ item.sickNameKor }} / <strong>정확도:</strong> {{ item.confidence }}
        </p>
      </div>

      <p v-if="error">{{ error }}</p>

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

// 파일 선택 핸들러
function onFileChange(event) {
  photo.value = event.target.files[0]
}

// 데이터 요청
async function fetchData() {
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

    const res = await axios.post(`http://127.0.0.1/api/predict`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    const data = res.data.data
    services.value = [{
      cropName: data.cropName,
      sickNameKor: data.sickNameKor,
      confidence: data.confidence
    }]
  } catch (err) {
    error.value = 'API 요청 실패: ' + (err.response?.data?.error || err.message)
  } finally {
    loading.value = false
  }
}
</script>
