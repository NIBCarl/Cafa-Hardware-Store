import { vi } from 'vitest';
import { config } from '@vue/test-utils';
import { defineStore } from 'pinia';

// Mock Pinia stores
export const useToastStore = defineStore('toast', {
  state: () => ({
    toasts: []
  }),
  actions: {
    success: vi.fn(),
    error: vi.fn(),
    warning: vi.fn(),
    info: vi.fn()
  }
});

// Mock Vue Router
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: vi.fn(),
    replace: vi.fn()
  }),
  useRoute: () => ({
    params: {},
    query: {}
  })
}));

// Mock Heroicons
vi.mock('@heroicons/vue/24/outline', () => ({
  MagnifyingGlassIcon: {
    template: '<div class="h-5 w-5"></div>'
  },
  QrCodeIcon: {
    template: '<div class="h-5 w-5"></div>'
  },
  XMarkIcon: {
    template: '<div class="h-5 w-5"></div>'
  },
  MinusCircleIcon: {
    template: '<div class="h-5 w-5"></div>'
  },
  PlusCircleIcon: {
    template: '<div class="h-5 w-5"></div>'
  },
  TrashIcon: {
    template: '<div class="h-5 w-5"></div>'
  },
  ArrowsUpDownIcon: {
    template: '<div class="h-5 w-5"></div>'
  },
  PencilIcon: {
    template: '<div class="h-5 w-5"></div>'
  }
}));

// Mock base components
config.global.stubs = {
  BaseButton: {
    template: `
      <button
        :title="title"
        :class="[
          variant === 'primary' ? 'w-full' : '',
          variant === 'secondary' ? 'text-gray-400 hover:text-gray-500' : '',
          variant === 'danger' ? 'text-red-400 hover:text-red-500' : ''
        ]"
        @click="$emit('click')"
      >
        <slot></slot>
      </button>
    `,
    props: ['variant', 'icon', 'loading', 'disabled', 'title'],
    emits: ['click']
  },
  BaseInput: {
    template: '<input v-model="value" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    props: ['modelValue'],
    computed: {
      value: {
        get() {
          return this.modelValue;
        },
        set(value) {
          this.$emit('update:modelValue', value);
        }
      }
    }
  },
  BaseModal: {
    template: '<div><slot></slot></div>',
    props: ['title']
  }
};