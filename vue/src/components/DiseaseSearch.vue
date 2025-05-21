<template>
  <div class="w-full px-4 py-8">
    <h2>병해충 도감 검색</h2>
    <div class="search-bar">
      <select v-model="searchType">
        <option :value="1">작물명</option>
        <option :value="2">병명</option>
      </select>
      <input v-model="search" placeholder="검색어를 입력하세요" @keyup.enter="fetchData(1)" />
      <button class="search-button" @click="fetchData(1)">🔍 검색</button>
    </div>
    <p v-if="loading">🔄 예방 정보를 불러오는 중입니다...</p>
    <table v-show="!loading" border="1">
      <thead>
      <tr>
        <th>사진</th>
        <th>작물</th>
        <th>병명</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(item, index) in items" :key="index">
        <td>
          <img
              v-if="item.oriImg"
              :src="item.oriImg"
              alt="이미지"
              class="table-img"
          />
          <span v-else>-</span>
        </td>
        <td>{{ item.cropName }}</td>
        <td>
        <router-link
            :to="`/disease/${encodeURIComponent(item.cropName)}/${encodeURIComponent(item.sickNameKor)}`">
          {{ item.sickNameKor }}
        </router-link>
        </td>
      </tr>
      </tbody>
    </table>

    <p v-if="error" style="color:red;">{{ error }}</p>

    <div class="pagination" v-if="pagination.total > pagination.per_page">
      <button class="pagination-button"
          v-for="n in pagination.last_page"
          :key="n"
          :class="{ active: pagination.current_page === n }"
          @click="fetchData(n)"
      >
        {{ n }}
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      searchType: 1,
      search: '사과',
      items: [],
      error: '',
      pagination: {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
      },
      loading: false,
    };
  },
  created() {
    this.fetchData(1); // 첫화면 자동 검색
  },
  methods: {
    async fetchData(page = 1) {
      this.loading = true;
      this.error = '';

      if (!this.search.trim()) {
        this.error = '검색어를 입력해주세요.';
        this.loading = false;
        return;
      }

      try {
        const res = await axios.get('http://127.0.0.1/api/diseases', {
          params: {
            type: this.searchType,
            search: this.search,
            page,
          },
        });

        this.items = res.data.data;
        this.pagination = res.data.pagination;
      } catch (err) {
        console.error(err);
        this.error =
            'API 호출 실패: ' + (err.response?.data?.error || err.message);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
<style scoped>
.pagination-button {
  padding: 4px 8px;
  min-width: auto;
  font-size: 0.9rem;
}
.search-bar {
  display: flex;
  gap: 10px;
  margin-bottom: 1rem;
}
input,
select {
  padding: 6px;
  font-size: 1rem;
}
.search-button {
  min-width: 120px;
  padding: 12px 12px;
  cursor: pointer;
}

table {
  width: 200%;
  border-collapse: collapse;
  text-align: center;
}
h2 {
  text-align: center;
  font-size: 1.5rem;
  margin-bottom: 1rem;
}
th,
td {
  padding: 8px;
  border-bottom: 1px solid #ccc;
  min-width: 120px; /* 필요시 더 키워도 돼 */
}
.pagination {
  margin-top: 1rem;
  text-align: center;
}
.pagination-button {
  margin-right: 4px;
  padding: 4px 8px;
}
.pagination .active {
  background-color: #4caf50;
  color: white;
}
.mx-auto {
  margin-left: 0 !important;
  margin-right: 0 !important;
}
.table-img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  display: block;
  margin: 0 auto;
}
</style>
