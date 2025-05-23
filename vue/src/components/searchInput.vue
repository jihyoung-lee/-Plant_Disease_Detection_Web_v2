<template>
  <div class="flex gap-2 items-center">
    <select v-model="searchType" class="select select-bordered select-sm">
      <option :value="1">작물명</option>
      <option :value="2">병명</option>
    </select>

    <input
        v-model="search"
        placeholder="검색어를 입력하세요"
        class="input input-bordered input-sm"
        @keyup.enter="searchAction"
    />

    <button class="btn btn-sm btn-primary" @click="searchAction">검색</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const searchType = ref(1)         // 1: 작물명, 2: 병명
const search = ref('사과')

const router = useRouter()

function searchAction() {
  if (!search.value.trim()) return

  router.push({
    path: '/disease-search',
    query: {
      type: searchType.value,
      search: search.value.trim(),
    }
  })
}
</script>
