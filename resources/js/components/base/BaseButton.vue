<template>
  <button
    :type="type"
    :class="[
      'inline-flex items-center justify-center rounded-lg font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md active:scale-95',
      sizeClasses[size],
      variantClasses[variant],
      { 'opacity-50 cursor-not-allowed': disabled || loading }
    ]"
    :disabled="disabled || loading"
  >
    <!-- Loading spinner -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
    <!-- Icon -->
    <component
      v-else-if="icon"
      :is="icon"
      class="-ml-1 mr-2 h-5 w-5"
      aria-hidden="true"
    />
    <slot></slot>
  </button>
</template>

<script setup>
const props = defineProps({
  type: {
    type: String,
    default: 'button'
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'success', 'warning'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  icon: {
    type: [Object, Function],
    default: null
  }
});

const sizeClasses = {
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-4 py-2.5 text-sm',
  lg: 'px-6 py-3 text-base'
};

const variantClasses = {
  primary: 'bg-gradient-to-r from-primary-600 to-primary-700 text-white hover:from-primary-700 hover:to-primary-800 focus:ring-primary-500',
  secondary: 'bg-gradient-to-r from-gray-200 to-gray-300 text-gray-700 hover:from-gray-300 hover:to-gray-400 focus:ring-gray-500',
  danger: 'bg-gradient-to-r from-red-600 to-red-700 text-white hover:from-red-700 hover:to-red-800 focus:ring-red-500',
  success: 'bg-gradient-to-r from-green-600 to-green-700 text-white hover:from-green-700 hover:to-green-800 focus:ring-green-500',
  warning: 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white hover:from-yellow-600 hover:to-yellow-700 focus:ring-yellow-500'
};
</script>
