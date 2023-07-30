<script setup>

</script>
<template>
  <DialogModal
    :show="showModal"
    @close="closeModal"
  >
    <template #title>
      {{ title }}
    </template>

    <template #content>
      {{ content }}
    </template>

    <template #footer>
      <SecondaryButton @click="closeModal">
        Cancel
      </SecondaryButton>

      <DangerButton
        class="ml-3"
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
        @click="action"
      >
        {{ process }}
      </DangerButton>
    </template>
  </DialogModal>
</template>