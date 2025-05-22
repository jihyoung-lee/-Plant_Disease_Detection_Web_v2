<template>
  <div class="w-full px-4 py-8">
    <h2>병해충 도감 검색</h2>
    <div class="search-bar">
      <select v-model="searchType">
        <option :value="1">작물명</option>
        <option :value="2">병명</option>
      </select>
      <input
          v-model="search"
          placeholder="검색어를 입력하세요"
          @keyup.enter="fetchData(1)"
      />
      <button class="search-button" @click="fetchData(1)">🔍 검색</button>
    </div>
    <table border="1">
      <thead>
      <tr>
        <th>사진</th>
        <th>작물</th>
        <th>병명</th>
      </tr>
      </thead>
      <tbody v-if="!loading">
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
              :to="`/disease/${encodeURIComponent(item.cropName)}/${encodeURIComponent(item.sickNameKor)}`"
          >
            {{ item.sickNameKor }}
          </router-link>
        </td>
      </tr>
      </tbody>
      <!-- 스켈레톤 UI -->
      <tbody v-else>
      <tr v-for="n in 5" :key="'skeleton-' + n">
        <td><div class="skeleton-box skeleton-img"></div></td>
        <td><div class="skeleton-box skeleton-text"></div></td>
        <td><div class="skeleton-box skeleton-text"></div></td>
      </tr>
      </tbody>
    </table>

    <p v-if="error" style="color:red;">{{ error }}</p>

    <div class="pagination" v-if="pagination && pagination.total > pagination.per_page">
      <button
          class="pagination-button"
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
  name: 'DiseaseSearch',
  data() {
    return {
      searchType: 1,
      search: '사과',
      items: [],
      error: '',
      pagination: {
        current_page: 1,
        per_page: 5, // Laravel과 일치
        total: 0,
        last_page: 1,
      },
      loading: false,
    };
  },
  created() {
    this.fetchData(1);
  },
  methods: {
    async fetchData(page = 1) {
      this.loading = true;
      this.error = '';
      try {
        if (!this.search.trim()) {
          this.error = '검색어를 입력해주세요.';
          return;
        }

        const res = await axios.get('http://127.0.0.1/api/diseases', {
          params: {
            type: this.searchType,
            search: this.search,
            page,
          },
        });

        this.items = res.data.data || [];
        this.pagination = res.data.pagination || {
          current_page: 1,
          per_page: 5,
          total: 0,
          last_page: 1,
        };
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
  min-width: 120px;
}
.pagination {
  margin-top: 1rem;
  text-align: center;
}
.pagination .active {
  background-color: #4caf50;
  color: white;
}
.table-img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  display: block;
  margin: 0 auto;
}
.skeleton-box {
  background: linear-gradient(90deg, rgba(59, 129, 129, 0.44), rgba(8, 241, 102, 0.44), rgba(28, 197, 114, 0.98));
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 4px;
}
.skeleton-img {
  width: 100px;
  height: 100px;
  margin: 0 auto;
}
.skeleton-text {
  width: 80%;
  height: 20px;
  margin: 0 auto;
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
