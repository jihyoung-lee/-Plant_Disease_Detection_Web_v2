<template>
  <div class="box">
    <h2>병해충 예방 정보</h2>

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
  props: ['cropName', 'sickNameKor'],
  data() {
    return {
      services: [],
      error: '',
    };
  },
  methods: {
    async fetchData() {
      this.error = '';
      this.services = [];

      try {
        const res = await axios.get(`http://127.0.0.1/api/disease-info`, {
          params: {
            cropName: this.cropName,
            sickNameKor: this.sickNameKor,
          },
        });

        const service = res.data.raw?.service;

        if (!service) {
          this.error = '결과가 없습니다.';
        } else {
          this.services = Array.isArray(service) ? service : [service];
        }
      } catch (err) {
        console.error(err);
        this.error =
            'API 요청 실패: ' + (err.response?.data?.error || err.message);
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
