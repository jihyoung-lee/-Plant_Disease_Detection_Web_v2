<template>
  <div class="box">
    <h2>병해충 예방 정보</h2>
    <div>
      <input v-model="cropName" placeholder="작물명 (예: 고추)" />
      <input v-model="sickNameKor" placeholder="병명 (예: 탄저병)" />
      <button @click="fetchData">조회하기</button>
    </div>

    <table v-if="services.length" border="1">
      <thead>
      <tr>
        <th>작물명</th>
        <th>병명 (중문)</th>
        <th>예방 방법</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(item, index) in services" :key="index">
        <td>{{ item.cropName }}</td>
        <td>{{ item.sickNameChn }}</td>
        <td v-html="formatPrevention(item.preventionMethod)"></td>
      </tr>
      </tbody>
    </table>

    <p v-if="error" style="color:red;">{{ error }}</p>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      cropName: '고추',
      sickNameKor: '탄저병',
      services: [],
      error: ''
    };
  },
  methods: {
    async fetchData() {
      this.error = '';
      this.services = [];

      const url = `http://127.0.0.1:80/api/disease-info?cropName=${this.cropName}&sickNameKor=${this.sickNameKor}`;

      try {
        const res = await axios.get(url);
        console.log(res.data); // 구조 확인용

        const service = res.data.raw?.service;

        if (!service) {
          this.error = '결과가 없습니다.';
        } else {
          this.services = Array.isArray(service) ? service : [service];
        }
      } catch (err) {
        console.error(err);
        this.error = 'API 요청 실패: ' + (err.response?.data?.error || err.message);
      }
    },
    formatPrevention(text) {
      return text ? text.replace(/\n/g, '<br/>') : '';
    }
  }
};
</script>

<style>
.box {
  max-width: 800px;
  margin: auto;
  padding: 1rem;
}
input {
  margin-right: 8px;
  padding: 6px;
}
button {
  padding: 6px 12px;
}
table {
  margin-top: 20px;
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 0.5rem;
  text-align: left;
  vertical-align: top;
}
</style>