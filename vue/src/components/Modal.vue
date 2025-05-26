<template>
  <button class="btn btn-outline btn-accent" onclick="my_modal_3.showModal()">AI진단</button>
  <dialog id="my_modal_3" class="modal">
    <div class="modal-box">
      <form method="dialog">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
      </form>
      <h3 class="text-lg font-bold">AI 병해 진단 서비스</h3>
      <p class="py-4">Press ESC key or click on ✕ button to close</p>
    </div>
  </dialog>
</template>

<script>
export default {
  name: "modal"
}
</script>

<style scoped>

</style>