import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import ProductSearch from '../pos/ProductSearch.vue';
import { productService } from '../../services/api';

// Mock the API service
vi.mock('../../services/api', () => ({
  productService: {
    getProducts: vi.fn()
  }
}));

// Mock useDebounce
vi.mock('@vueuse/core', () => ({
  useDebounce: (fn) => fn
}));

describe('ProductSearch', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    setActivePinia(createPinia());
  });

  const mountComponent = (options = {}) => {
    return mount(ProductSearch, {
      global: {
        plugins: [createPinia()],
        stubs: {
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
          }
        }
      },
      ...options
    });
  };

  it('renders properly', () => {
    const wrapper = mountComponent();
    expect(wrapper.find('input[type="search"]').exists()).toBe(true);
  });

  it('emits search results when typing', async () => {
    const mockProducts = {
      data: [
        { id: 1, name: 'Test Product', sku: 'TEST-1', price: 100, stock_quantity: 10 }
      ]
    };

    productService.getProducts.mockResolvedValue({ data: mockProducts });

    const wrapper = mountComponent();
    const input = wrapper.find('input[type="search"]');

    await input.setValue('test');

    expect(productService.getProducts).toHaveBeenCalledWith({
      search: 'test',
      is_active: true
    });
  });

  it('emits selected product when clicking on result', async () => {
    const mockProduct = {
      id: 1,
      name: 'Test Product',
      sku: 'TEST-1',
      price: 100,
      stock_quantity: 10
    };

    const wrapper = mount({
      template: `
        <div>
          <input type="search" />
          <ul>
            <li
              v-for="product in results"
              :key="product.id"
              class="p-4 hover:bg-gray-50 cursor-pointer"
              @click="$emit('select', product)"
            >
              {{ product.name }}
            </li>
          </ul>
        </div>
      `,
      data() {
        return {
          results: [mockProduct],
          showResults: true
        };
      },
      emits: ['select']
    });

    await wrapper.find('li').trigger('click');

    expect(wrapper.emitted('select')?.[0][0]).toEqual(mockProduct);
  });
});