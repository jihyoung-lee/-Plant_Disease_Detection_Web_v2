<!-- OpinionModal.vue -->
<template>
  <dialog ref="dialogRef" class="modal">
    <div class="modal-box">
      <h3 class="font-bold text-lg">사용자 의견 남기기</h3>
      <form @submit.prevent="submitOpinion">
        <label class="label">작물 이름</label>
        <input class="input input-bordered w-full mb-2 bg-gray-100" :value="cropName" readonly />

        <label class="label">병해 이름</label>
        <input class="input input-bordered w-full mb-2" v-model="sickNameKor" />

        <div class="modal-action">
          <button type="submit" class="btn btn-success">전송</button>
          <button type="button" class="btn" @click="close">닫기</button>
        </div>
      </form>
    </div>
  </dialog>
</template>
<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  targetItem: Object,
})
const emit = defineEmits(['sent'])

const dialogRef = ref(null)
const cropName = ref('')
const sickNameKor = ref('')

// targetItem이 바뀔 때마다 cropName, sickNameKor 재설정
watch(() => props.targetItem, (val) => {
  if (val) {
    cropName.value = val.cropName || ''
    sickNameKor.value = val.sickNameKor || ''
  }
})

const open = () => {
  // 강제 리셋
  cropName.value = props.targetItem?.cropName || ''
  sickNameKor.value = props.targetItem?.sickNameKor || ''
  dialogRef.value?.showModal()
}

const close = () => dialogRef.value?.close()

const submitOpinion = async () => {
  try {
    await axios.post(`http://127.0.0.1/api/predict/${props.targetItem.id}/opinion`, {
      cropName: cropName.value,
      sickNameKor: sickNameKor.value,
    })
    emit('sent')
    close()
  } catch {
    alert('의견 전송 실패')
  }
}

defineExpose({ open })
</script>
