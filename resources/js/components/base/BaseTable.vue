<template>
  <div class="overflow-hidden bg-white shadow-lg rounded-xl border border-gray-200">
    <!-- Table header with actions -->
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center bg-gradient-to-r from-white to-gray-50 border-b border-gray-200">
      <div class="flex items-center space-x-4">
        <h3 class="text-lg font-semibold leading-6 text-gray-900">
          {{ title }}
        </h3>
        <p v-if="totalItems" class="text-sm font-medium text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
          Total: {{ totalItems }}
        </p>
      </div>
      <div class="flex items-center space-x-2">
        <slot name="actions"></slot>
      </div>
    </div>

    <!-- Search and filters -->
    <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 shadow-inner">
      <div class="flex flex-wrap gap-4 items-center justify-between">
        <div class="flex-1 min-w-0 max-w-md">
          <BaseInput
            id="table-search"
            v-model="searchQuery"
            type="search"
            placeholder="Search..."
            :icon="MagnifyingGlassIcon"
            @input="$emit('search', searchQuery)"
          />
        </div>
        <div class="flex items-center space-x-2">
          <slot name="filters"></slot>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-gray-50 shadow-inner">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"
              :class="column.class"
            >
              <div
                class="flex items-center space-x-1 cursor-pointer hover:text-primary-600 transition-colors"
                @click="handleSort(column.key)"
                v-if="column.sortable"
              >
                <span>{{ column.label }}</span>
                <ChevronUpDownIcon
                  class="w-4 h-4 transition-colors"
                  :class="{
                    'text-primary-600': sortBy === column.key,
                    'text-gray-400': sortBy !== column.key
                  }"
                />
              </div>
              <span v-else>{{ column.label }}</span>
            </th>
            <th v-if="$slots.actions" scope="col" class="relative px-6 py-3">
              <span class="sr-only">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <!-- Loading state -->
          <tr v-if="loading">
            <td
              :colspan="columns.length + ($slots.actions ? 1 : 0)"
              class="px-6 py-12 text-center"
            >
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              <p class="mt-2 text-sm text-gray-500">Loading...</p>
            </td>
          </tr>
          <template v-else-if="items.length">
            <tr v-for="item in items" :key="item.id" class="hover:bg-gradient-to-r hover:from-primary-50 hover:to-primary-100 hover:shadow-sm transition-all duration-200">
              <td
                v-for="column in columns"
                :key="column.key"
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                :class="column.class"
              >
                <slot
                  :name="column.key"
                  :item="item"
                  :value="item[column.key]"
                >
                  {{ formatValue(item[column.key], column.format) }}
                </slot>
              </td>
              <td
                v-if="$slots.actions"
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <slot name="actions" :item="item"></slot>
              </td>
            </tr>
          </template>
          <tr v-else>
            <td
              :colspan="columns.length + ($slots.actions ? 1 : 0)"
              class="px-6 py-4 text-center text-gray-500"
            >
              {{ emptyMessage || 'No items found' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div
      v-if="showPagination && totalPages > 1"
      class="px-4 py-3 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 sm:px-6"
    >
      <div class="flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
          <BaseButton
            variant="secondary"
            :disabled="currentPage === 1"
            @click="$emit('page-change', currentPage - 1)"
          >
            Previous
          </BaseButton>
          <BaseButton
            variant="secondary"
            :disabled="currentPage === totalPages"
            @click="$emit('page-change', currentPage + 1)"
          >
            Next
          </BaseButton>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-semibold text-primary-600">{{ startItem }}</span>
              to
              <span class="font-semibold text-primary-600">{{ endItem }}</span>
              of
              <span class="font-semibold text-primary-600">{{ totalItems }}</span>
              results
            </p>
          </div>
          <div>
            <nav
              class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px"
              aria-label="Pagination"
            >
              <button
                v-for="page in displayedPages"
                :key="page"
                @click="$emit('page-change', page)"
                :class="[
                  page === currentPage
                    ? 'z-10 bg-gradient-to-r from-primary-500 to-primary-600 border-primary-500 text-white shadow-md'
                    : 'bg-white border-gray-300 text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-100 hover:text-primary-600',
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-all duration-200'
                ]"
              >
                {{ page }}
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import {
  MagnifyingGlassIcon,
  ChevronUpDownIcon
} from '@heroicons/vue/24/outline';
import BaseInput from './BaseInput.vue';
import BaseButton from './BaseButton.vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  items: {
    type: Array,
    required: true
  },
  currentPage: {
    type: Number,
    default: 1
  },
  totalItems: {
    type: Number,
    default: 0
  },
  perPage: {
    type: Number,
    default: 10
  },
  sortBy: {
    type: String,
    default: ''
  },
  sortOrder: {
    type: String,
    default: 'asc'
  },
  showPagination: {
    type: Boolean,
    default: true
  },
  emptyMessage: {
    type: String,
    default: ''
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['search', 'sort', 'page-change']);

const searchQuery = ref('');

// Computed
const totalPages = computed(() => Math.ceil(props.totalItems / props.perPage));

const startItem = computed(() => {
  return (props.currentPage - 1) * props.perPage + 1;
});

const endItem = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.totalItems);
});

const displayedPages = computed(() => {
  const delta = 2;
  const range = [];
  const rangeWithDots = [];
  let l;

  for (let i = 1; i <= totalPages.value; i++) {
    if (
      i === 1 ||
      i === totalPages.value ||
      (i >= props.currentPage - delta && i <= props.currentPage + delta)
    ) {
      range.push(i);
    }
  }

  range.forEach(i => {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l !== 1) {
        rangeWithDots.push('...');
      }
    }
    rangeWithDots.push(i);
    l = i;
  });

  return rangeWithDots;
});

// Methods
const handleSort = (key) => {
  const newOrder = props.sortBy === key && props.sortOrder === 'asc' ? 'desc' : 'asc';
  emit('sort', { key, order: newOrder });
};

const formatValue = (value, format) => {
  if (!value || !format) return value;

  switch (format) {
    case 'currency':
      return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
      }).format(value);
    case 'date':
      return new Date(value).toLocaleDateString();
    case 'datetime':
      return new Date(value).toLocaleString();
    default:
      return value;
  }
};
</script>
