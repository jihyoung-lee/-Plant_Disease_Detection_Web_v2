<template>
  <div class="content-area">
    <h2>병해충 예방 정보</h2>
    <p v-if="loading">🔄 예방 정보를 불러오는 중입니다...</p>

    <div class="table-container" v-show="!loading">
      <table border="1">
        <thead>
        <tr>
          <th>작물명</th>
          <th>병명</th>
          <th>예방 방법</th>
        </tr>
        </thead>
        <tbody>
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
  created() {
    this.fetchData();
  },
};
</script>

<style scoped>
.content-area {
  width: 100%;
  padding: 3rem;
  box-sizing: border-box;
  overflow-x: auto;
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
</style>
