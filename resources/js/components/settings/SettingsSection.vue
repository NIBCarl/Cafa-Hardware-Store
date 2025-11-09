<template>
  <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
    <div class="px-4 py-5 sm:px-6">
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ title }}
      </h3>
      <p v-if="description" class="mt-1 text-sm text-gray-500">
        {{ description }}
      </p>
    </div>
    <div class="px-4 py-5 sm:p-6">
      <slot></slot>
    </div>
    <div
      v-if="$slots.actions"
      class="px-4 py-4 sm:px-6 bg-gray-50 flex justify-end space-x-3"
    >
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  }
});
</script>
