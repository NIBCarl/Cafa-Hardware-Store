<template>
  <div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <component
            :is="icon"
            class="h-6 w-6"
            :class="[
              trend > 0 ? 'text-green-500' : trend < 0 ? 'text-red-500' : 'text-gray-400'
            ]"
          />
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 truncate">
              {{ title }}
            </dt>
            <dd class="flex items-baseline">
              <div class="text-2xl font-semibold text-gray-900">
                {{ formatValue(value) }}
              </div>
              <div
                v-if="trend !== null"
                class="ml-2 flex items-baseline text-sm font-semibold"
                :class="[
                  trend > 0 ? 'text-green-600' : 'text-red-600'
                ]"
              >
                <component
                  :is="trend > 0 ? ArrowUpIcon : ArrowDownIcon"
                  class="h-4 w-4 flex-shrink-0 self-center"
                  aria-hidden="true"
                />
                <span class="ml-1">{{ Math.abs(trend) }}%</span>
                <span class="sr-only">
                  {{ trend > 0 ? 'Increased' : 'Decreased' }} by
                </span>
              </div>
            </dd>
          </dl>
        </div>
      </div>
    </div>
    <div class="bg-gray-50 px-5 py-3">
      <div class="text-sm">
        <router-link
          v-if="link"
          :to="link"
          class="font-medium text-primary-700 hover:text-primary-900 transition-colors"
        >
          View details
        </router-link>
        <span v-else class="text-gray-500">
          {{ subtitle }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ArrowUpIcon,
  ArrowDownIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  format: {
    type: String,
    default: 'number'
  },
  trend: {
    type: Number,
    default: null
  },
  icon: {
    type: [Object, Function],
    required: true
  },
  link: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  }
});

const formatValue = (value) => {
  switch (props.format) {
    case 'currency':
      return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
      }).format(value);
    case 'number':
      return new Intl.NumberFormat('en-PH').format(value);
    case 'percentage':
      return `${value}%`;
    default:
      return value;
  }
};
</script>
