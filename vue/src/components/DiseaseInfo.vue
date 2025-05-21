<template>
  <div class="box">
    <h2>병해충 예방 정보</h2>
    <p v-if="loading">🔄 예방 정보를 불러오는 중입니다...</p>
    <table v-show="!loading" border="1">
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

        if (!service) {
          this.error = '결과가 없습니다.';
        } else {
          this.services = Array.isArray(service) ? service : [service];
        }
      } catch (err) {
        console.error(err);
        this.error =
            'API 요청 실패: ' + (err.response?.data?.error || err.message);
      }finally {
        this.loading = false; // 끝나면 로딩 false로 변경
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
