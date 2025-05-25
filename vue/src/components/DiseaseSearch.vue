<template>
  <div class="content-area">
    <h2 class="text-2xl font-semibold mb-4">검색 결과</h2>
    <table border="1" class="w-full">
      <thead>
      <tr>
        <th>사진</th>
        <th>작물</th>
        <th>병명</th>
      </tr>
      </thead>
      <tbody v-if="!loading">
      <tr v-for="(item, index) in items" :key="index" class="hover:bg-green-100 cursor-pointer transition-colors">
        <td>
          <div class="avatar">
          <div class="w-24 rounded">
          <img
              v-if="item.oriImg"
              :src="item.oriImg"
              alt="이미지"
              class="w-[100px] h-[100px] object-contain"
          />

          <span v-else>-</span>
          </div>
          </div>
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
          :class="{ active: String(route.query.page || '1') === String(n) }"
          @click="goToPage(n)"
      >
        {{ n }}
      </button>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { watch, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

    const route = useRoute();
    const router = useRouter();

    const items = ref([]);
    const error = ref('');
    const loading = ref(false);
    const pagination = ref({
      current_page: 1,
      per_page: 5,
      total: 0,
      last_page: 1,
    });

    async function fetchData() {
      const search = route.query.search || '';
      const type = route.query.type || '1';
      const page = parseInt(route.query.page || '1', 10);

      if (!search.trim()) {
        error.value = '검색어를 입력해주세요.';
        items.value = [];
        return;
      }
      pagination.value.current_page = page;

      loading.value = true;
      error.value = '';

      try {
        const res = await axios.get('http://127.0.0.1/api/diseases', {
          params: {
            type,
            search,
            page,
          }
        });

        items.value = res.data.data || [];
        pagination.value = res.data.pagination || {
          current_page: 1,
          per_page: 5,
          total: 0,
          last_page: 1,
        };
        pagination.value.current_page = page; // 현재 페이지 명시
      } catch (err) {
        error.value = 'API 호출 실패: ' + (err.response?.data?.error || err.message);
      } finally {
        loading.value = false;
      }
    }

    function goToPage(page) {
      router.push({
        path: '/disease-search',
        query: {
          ...route.query,
          page,
        },
      });
    }

    //첫 실행 + 쿼리변경 감지
    onMounted(fetchData);
    watch(() => route.query, fetchData);
</script>

<style scoped>
.pagination-button {
  padding: 4px 8px;
  min-width: auto;
  font-size: 0.9rem;
}
input,
select {
  padding: 6px;
  font-size: 1rem;
}
table {
  min-width: 800px;
  width: 100%;
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
