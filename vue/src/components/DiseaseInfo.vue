<template>
  <Loading v-if="loading" />
  <div v-else class="content-area">
    <div class="table-container">
      <table>
        <thead>
        <tr>
          <td>피해 사진</td>
          <td v-for="item in services">
            <img
                v-if="item.imageList[0].image"
                :src="item.imageList[0].image"
                alt="이미지"
                class="w-[300px] h-[300px] object-contain"
            />
          </td>
        </tr>
        </thead>
        <tbody>
        <tr>
          <td>작물명</td>
          <td v-for="item in services">{{ item.cropName }}</td>
        </tr>
        <tr>
          <td>병명</td>
          <td v-for="item in services">{{ item.sickNameKor }}</td>
        </tr>
        </tbody>
      </table>
      <div v-for="(item, index) in services" :key="index">
        <!-- 증상 -->
        <div class="collapse collapse-arrow border border-base-300 bg-base-100 rounded-box mb-2">
          <input type="checkbox" />
          <div class="collapse-title font-semibold">증상</div>
          <div class="collapse-content">
            <p v-html="formatPrevention(item.symptoms)" style="white-space: pre-line"></p>
          </div>
        </div>

        <!-- 발생 생태 -->
        <div class="collapse collapse-arrow border border-base-300 bg-base-100 rounded-box mb-2">
          <input type="checkbox" />
          <div class="collapse-title font-semibold">발생 생태</div>
          <div class="collapse-content">
            <p v-html="formatPrevention(item.developmentCondition)" style="white-space: pre-line"></p>
          </div>
        </div>

        <!-- 방제 방법 -->
        <div class="collapse collapse-arrow border border-base-300 bg-base-100 rounded-box mb-2">
          <input type="checkbox" />
          <div class="collapse-title font-semibold">방제 방법</div>
          <div class="collapse-content">
            <p v-html="formatPrevention(item.preventionMethod)"></p>
          </div>
        </div>
      </div>
    </div>

    <p v-if="error" style="color:red;">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import api from '@/lib/axios'
import Loading from '@/components/Loading.vue'
import { useRoute } from 'vue-router'

// props
const props = defineProps({
  cropName: String,
  sickNameKor: String
})

// 상태
const services = ref([])
const error = ref('')
const loading = ref(false)

// 줄바꿈 <br/> 변환 함수
function formatPrevention(text) {
  return text ? text.replace(/\n/g, '<br/>') : ''
}

// 데이터 요청
async function fetchData() {
  loading.value = true
  error.value = ''

  try {
    const res = await api.get(`/disease-info`, {
      params: {
        cropName: props.cropName,
        sickNameKor: props.sickNameKor,
      },
    })

    const service = res.data.raw?.service
    services.value = Array.isArray(service) ? service : [service]

    if (!service) error.value = '결과가 없습니다.'
  } catch (err) {
    error.value = 'API 요청 실패: ' + (err.response?.data?.error || err.message)
  } finally {
    loading.value = false
  }
}

// 마운트 시 호출
onMounted(() => {
  loading.value = true
  nextTick(fetchData)
})
</script>


<style scoped>
.table-container {
  overflow-x: auto;
}

table {
  min-width: 800px; /* 이부분 표 크기 */
  width: 100%;
  table-layout: auto;
}

th,
td {
  padding: 12px;
  border: 1px solid #ccc;
  text-align: left;
  vertical-align: top;
  white-space: normal;
  word-break: break-word;
}
table th,
table td,
table tr {
  border: none !important;
}
@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>
