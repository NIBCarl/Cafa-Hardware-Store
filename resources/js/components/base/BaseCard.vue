<template>
  <div
    class="bg-white overflow-hidden shadow-sm rounded-lg"
    :class="{
      'hover:shadow-md transition-shadow duration-200': hover
    }"
  >
    <div v-if="title || $slots.header" class="px-4 py-5 sm:px-6 border-b border-gray-200">
      <slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">{{ title }}</h3>
        <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
      </slot>
    </div>
    <div :class="{ 'px-4 py-5 sm:p-6': !noPadding }">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  noPadding: {
    type: Boolean,
    default: false
  },
  hover: {
    type: Boolean,
    default: false
  }
});
</script>
