<script setup>
import Badge from '@/Components/Badge.vue';
defineProps({
    condition: Boolean,
    classTrue: {
      type: String,
      required: false,
    },
    valueTrue: String,
    classFalse: {
      type: String,
      required: false,
    },
    valueFalse: String,
});
</script>

<template>
  <Badge
    v-if="condition"
    class="ml-2 badge-success"
    :class="classTrue"
    :value="valueTrue"
  />
  <Badge
    v-else
    class="ml-2 badge-warning"
    :class="classFalse"
    :value="valueFalse"
  />
</template>
