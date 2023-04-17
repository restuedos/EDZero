<script setup>
import { ref } from "vue";
import VOtpInput from "vue3-otp-input";
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    modelValue: {
      type: String,
      default: '',
    },
});

const emits = defineEmits(['update:modelValue']);

const input = ref(null);

const handleOnComplete = (value) => {
  emits('update:modelValue', value);
};

const clearInput = () => {
  input.value?.clearInput();
};

const fillInput = (value) => {
  input.value?.fillInput(value);
};

defineExpose({
  clearInput,
  fillInput
});
</script>

<template>
  <div style="display: flex; flex-direction: row">
    <v-otp-input
      ref="input"
      :value="modelValue ?? ''"
      input-classes="otp-input"
      separator=""
      :num-inputs="6"
      :should-auto-focus="true"
      input-type="number"
      inputmode="numeric"
      :conditional-class="['one', 'two', 'three', 'four', 'five', 'six']"
      :placeholder="['*', '*', '*', '*', '*', '*']"
      @on-complete="handleOnComplete"
    />

    <SecondaryButton
      class="ml-2"
      @click.prevent="clearInput"
    >
      Clear
    </SecondaryButton>
  </div>
</template>

<style>
.otp-input {
  width: 40px;
  height: 40px;
  padding: 5px;
  margin: 0 5px;
  font-size: 20px;
  border-radius: 4px;
  border: 1px solid rgba(0, 0, 0, 0.3);
  text-align: center;
}
/* Background colour of an input field with value */
.otp-input.is-complete {
  background-color: inherit;
}
.otp-input::-webkit-inner-spin-button,
.otp-input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input::placeholder {
  font-size: 15px;
  text-align: center;
  font-weight: 600;
}
</style>
