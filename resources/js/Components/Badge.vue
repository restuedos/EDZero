<script setup>
defineProps({
    value: String,
});
</script>

<template>
  <span
    v-if="value"
    class="badge badge-sm badge-outline"
  >{{ value }}</span>
  <span
    v-else
    class="badge badge-sm badge-outline"
  ><slot /></span>
</template>
