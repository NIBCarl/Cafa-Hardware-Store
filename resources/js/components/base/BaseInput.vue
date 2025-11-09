<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-semibold text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <div class="relative">
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :class="[
          'block w-full rounded-lg shadow-inner focus:ring-2 focus:ring-offset-0 transition-all duration-200 px-4 py-2.5',
          error
            ? 'border-2 border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 bg-red-50'
            : 'border border-gray-300 focus:border-primary-500 focus:ring-primary-500 focus:shadow-md bg-white hover:border-gray-400',
          { 'pl-11': icon },
          { 'opacity-60 cursor-not-allowed bg-gray-100': disabled }
        ]"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
      />
      <div v-if="icon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <div :class="[
          'p-1.5 rounded-md',
          error ? 'bg-red-100' : 'bg-gray-100'
        ]">
          <component
            :is="icon"
            class="h-4 w-4"
            :class="error ? 'text-red-500' : 'text-gray-500'"
            aria-hidden="true"
          />
        </div>
      </div>
    </div>
    <p v-if="error" class="mt-1.5 text-sm text-red-600 font-medium flex items-center">
      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
      </svg>
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1.5 text-sm text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  },
  icon: {
    type: [Object, Function],
    default: null
  }
});

defineEmits(['update:modelValue']);
</script>
