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
            {{ $t('hero_h1') }}
          </h1>
          <p class="text-lg md:text-xl text-gray-600 mb-8">
            {{ $t('hero_p1') }}
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
        {{ $t('card_p1')}}
        </p>

        <div class="grid gap-8 grid-cols-1 md:grid-cols-3">
          <div
              v-for="(card, index) in cards"
              :key="index"
              class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-left"
          >
            <img :src="card.icon" alt="icon" class="h-12 w-12 mb-4" />
            <h3 class="font-semibold text-lg mb-2">{{ t(card.titleKey) }}</h3>
            <p class="text-gray-600">{{ t(card.descKey) }}</p>
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
              <h4 class="text-xl font-bold mb-2">{{ t(info.titleKey) }}</h4>
              <p class="mb-4">{{ t(info.textKey) }}</p>
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
            <h4 class="text-xl font-semibold">{{ t(step.titleKey) }}</h4>
            <p class="text-gray-300">{{ t(step.descKey) }}</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>


<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const farmerImage = new URL('../assets/bgimg.png', import.meta.url).href

const cards = [
  {
    icon: new URL('../assets/clipboard-data.svg', import.meta.url).href,
    titleKey: 'card_1_title',
    descKey: 'card_1_desc'
  },
  {
    icon: new URL('../assets/phone-vibrate.svg', import.meta.url).href,
    titleKey: 'card_2_title',
    descKey: 'card_2_desc'
  },
  {
    icon: new URL('../assets/journal-richtext.svg', import.meta.url).href,
    titleKey: 'card_3_title',
    descKey: 'card_3_desc'
  }
]
const infos = [
  {
    img: new URL('../assets/leaf.jpg', import.meta.url).href,
    titleKey: 'infos_1_title',
    textKey: 'infos_1_text',
    link: '/photo'
  },
  {
    img: new URL('../assets/fruit.jpg', import.meta.url).href,
    titleKey: 'infos_2_title',
    textKey: 'infos_2_text',
    link: '/search'
  }
]

const steps = [
  { titleKey: 'step_1_title', descKey: 'step_1_desc' },
  { titleKey: 'step_2_title', descKey: 'step_2_desc' },
  { titleKey: 'step_3_title', descKey: 'step_3_desc' },
  { titleKey: 'step_4_title', descKey: 'step_4_desc' }
]
</script>

<style scoped>
.bg-dark-gradient {
  background: rgba(0, 0, 0, 0.6);
}
</style>