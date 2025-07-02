<template>
  <div class="text-gray-800">

    <!-- Hero Section -->
    <section id="header" class="relative min-h-screen bg-white flex items-center py-16 overflow-hidden">
      <img
          :src="farmerImage"
          class="absolute top-36 right-1 w-64 md:w-72 lg:w-[600px] z-0 pointer-events-none select-none"
      />

      <div class="container mx-auto px-6 md:px-12 z-10 relative">
        <div class="max-w-xl text-center md:text-left mx-auto md:ml-0">
          <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">
            AI 작물 병해 진단 시스템
          </h1>
          <p class="text-lg md:text-xl text-gray-600 mb-8">
            딥러닝을 활용한 자가 진단 AI, PC와 모바일 모두 지원
          </p>
          <a
              href="#howtouse"
              class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded shadow transition"
          >
            How To Use
          </a>
        </div>
      </div>
    </section>

    <!-- Cards Section -->
    <section id="predict" class="py-20 bg-gray-50">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold mb-4">작물의 병해 진단</h2>
        <p class="text-gray-600 mb-12">
          현재 총 5종의 주요 과수(감자, 딸기, 복숭아, 토마토, 포도)에 대한 병해 진단을 지원합니다.
        </p>

        <div class="grid gap-8 grid-cols-1 md:grid-cols-3">
          <div
              v-for="(card, index) in cards"
              :key="index"
              class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-left"
          >
            <img :src="card.icon" alt="icon" class="h-12 w-12 mb-4" />
            <h3 class="font-semibold text-lg mb-2">{{ card.title }}</h3>
            <p class="text-gray-600">{{ card.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Info Section -->
    <section id="invest" class="py-20">
      <div class="container mx-auto px-6">
        <div class="grid gap-10 grid-cols-1 md:grid-cols-2">
          <div
              v-for="(info, idx) in infos"
              :key="idx"
              class="relative overflow-hidden rounded-xl shadow-lg"
          >
            <img :src="info.img" alt="info image" class="w-full h-64 object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center items-center text-white p-6">
              <h4 class="text-xl font-bold mb-2">{{ info.title }}</h4>
              <p class="mb-4">{{ info.text }}</p>
              <button
                  class="bg-white text-green-600 px-4 py-2 rounded hover:bg-green-100 transition"
                  @click="$router.push(info.link)"
              >
                자세히 보기
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How to Use -->
    <section id="howtouse" class="py-20 bg-gray-800 text-white">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold mb-4">사용 방법</h2>
        <p class="text-gray-300 mb-6">단계별로 따라와 주세요</p>
        <button
            class="bg-white text-green-600 font-semibold py-3 px-6 rounded shadow hover:bg-green-100 transition mb-10"
            @click="$router.push('/photo')"
        >
          AI 진단하러 가기
        </button>

        <div class="space-y-8 max-w-2xl mx-auto text-left">
          <div v-for="(step, i) in steps" :key="i">
            <h4 class="text-xl font-semibold">{{ step.title }}</h4>
            <p class="text-gray-300">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>


<script setup>

const farmerImage = new URL('../assets/bgimg.png', import.meta.url).href

const cards = [
  {
    icon: new URL('../assets/clipboard-data.svg', import.meta.url).href,
    title: '쉽고 간편한 자가 진단',
    desc: '사진 한 장으로 병해를 자가 진단해 빠른 방제가 가능합니다'
  },
  {
    icon: new URL('../assets/phone-vibrate.svg', import.meta.url).href,
    title: 'PC와 모바일 모두 지원',
    desc: '기기 종류 상관 없이 간편하게 이용 가능합니다'
  },
  {
    icon: new URL('../assets/journal-richtext.svg', import.meta.url).href,
    title: '병해충 도감 연동',
    desc: '농촌진흥청의 다양한 정보를 확인할 수 있습니다'
  }
]

const infos = [
  {
    img: new URL('../assets/leaf.jpg', import.meta.url).href,
    title: '작물 진단',
    text: '딥러닝 기술을 이용한 작물 진단 서비스',
    link: '/photo'
  },
  {
    img: new URL('../assets/fruit.jpg', import.meta.url).href,
    title: '병해충 정보',
    text: '농촌진흥청에서 제공하는 병해충 정보',
    link: '/search'
  }
]

const steps = [
  { title: 'STEP 1 : AI 진단할 작물 이미지를 준비해주세요', desc: '이미지 용량이 너무 크면 조절해주세요' },
  { title: 'STEP 2 : 작물 진단 버튼 클릭 후 이미지를 업로드', desc: '사진을 첨부하지 않으면 경고메세지가 뜹니다' },
  { title: 'STEP 3 : 잠시 기다리면 진단 결과가 나옵니다', desc: '서버 연결이 끊기면 나중에 다시 시도해주세요' },
  { title: 'STEP 4 : 결과를 확인하고 링크를 클릭합니다', desc: '병해와 관련된 정보들을 확인할 수 있습니다' }
]
</script>

<style scoped>
.bg-dark-gradient {
  background: rgba(0, 0, 0, 0.6);
}
</style>