<template>
  <div class="content-area">
    <div class="table-container">
      <table border="1">
        <thead>
        <tr>
          <th>작물명</th>
          <th>병명</th>
          <th>예방 방법</th>
        </tr>
        </thead>

        <!-- 로딩 중일 때 -->
        <tbody v-if="loading">
        <tr v-for="n in 1" :key="'skeleton-' + n">
          <td><div class="skeleton-box h-4 w-24"></div></td>
          <td><div class="skeleton-box h-4 w-32"></div></td>
          <td><div class="skeleton-box  h-4 w-full"></div></td>
        </tr>
        </tbody>

        <!-- 로딩 끝났을 때 -->
        <tbody v-else>
        <tr v-for="(item, index) in services" :key="index">
          <td>{{ item.cropName }}</td>
          <td>{{ item.sickNameKor }}</td>
          <td v-html="formatPrevention(item.preventionMethod)"></td>
        </tr>
        </tbody>
      </table>
    </div>

    <p v-if="error" style="color:red;">{{ error }}</p>
  </div>
</template>

<script>
import axios from 'axios';
import { nextTick } from 'vue'

export default {
  props: ['cropName', 'sickNameKor'],
  data() {
    return {
      services: [],
      error: '',
      loading: false,
    };
  },
  methods: {
    async fetchData() {
      this.loading = true;
      this.error = '';

      try {
        // 스켈레톤 확인용 딜레이
        await new Promise(resolve => setTimeout(resolve, 800));

        const res = await axios.get(`http://127.0.0.1/api/disease-info`, {
          params: {
            cropName: this.cropName,
            sickNameKor: this.sickNameKor,
          },
        });

        const service = res.data.raw?.service;
        this.services = Array.isArray(service) ? service : [service];
        if (!service) this.error = '결과가 없습니다.';
      } catch (err) {
        this.error = 'API 요청 실패: ' + (err.response?.data?.error || err.message);
      } finally {
        this.loading = false;
      }
    },
    formatPrevention(text) {
      return text ? text.replace(/\n/g, '<br/>') : '';
    },
  },
  mounted() {
    this.loading = true;
    nextTick(() => {
      this.fetchData();
    });
  },
};
</script>

<style scoped>
.skeleton-box {
  background: linear-gradient(90deg, rgba(59, 129, 129, 0.44), rgba(8, 241, 102, 0.44), rgba(28, 197, 114, 0.98));
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 4px;
}
.table-container {
  overflow-x: auto;
}

table {
  min-width: 800px; /* 이부분 표 크기 */
  width: 100%;
  table-layout: auto;
  border-collapse: collapse;
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

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>
