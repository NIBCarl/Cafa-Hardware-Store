<template>
  <div class="space-y-4">
    <div>
      <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
        Search Products
      </label>
      <div class="relative">
        <input
          id="search"
          v-model="searchQuery"
          type="text"
          placeholder="Search by name, SKU, or scan barcode..."
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm pl-10"
          @input="handleSearch"
          @keydown.enter="handleBarcodeEnter"
        />
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      <p class="mt-2 text-sm text-gray-500">Searching products...</p>
    </div>

    <!-- Search Results -->
    <div v-else-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[600px] overflow-y-auto">
      <div
        v-for="product in products"
        :key="product.id"
        class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
        :class="product.stock_quantity === 0 ? 'opacity-50 bg-gray-50' : 'bg-white'"
        @click="handleAddToCart(product)"
      >
        <div class="flex gap-3 mb-3">
          <!-- Product Image -->
          <div class="flex-shrink-0 w-16 h-16 rounded-md overflow-hidden bg-gray-200 flex items-center justify-center">
            <img 
              v-if="product.image"
              :src="getImageUrl(product.image)" 
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>

          <!-- Product Info -->
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start mb-1">
              <h3 class="font-semibold text-gray-900 text-sm truncate pr-2">{{ product.name }}</h3>
              <span
                v-if="product.stock_quantity === 0"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 flex-shrink-0"
              >
                Out of Stock
              </span>
              <span
                v-else-if="product.stock_quantity <= product.low_stock_threshold"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 flex-shrink-0"
              >
                Low Stock
              </span>
            </div>
            <p class="text-xs text-gray-500 mb-1">SKU: {{ product.sku }}</p>
          </div>
        </div>
        
        <div class="flex justify-between items-center">
          <div>
            <p class="text-lg font-bold text-primary-600">₱{{ Number(product.price).toFixed(2) }}</p>
            <p class="text-xs text-gray-500">Stock: {{ product.stock_quantity }}</p>
          </div>
          <button
            v-if="product.stock_quantity > 0"
            class="px-3 py-1 bg-primary-600 text-white rounded-md text-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
            @click.stop="handleAddToCart(product)"
          >
            Add
          </button>
          <button
            v-else
            disabled
            class="px-3 py-1 bg-gray-300 text-gray-500 rounded-md text-sm cursor-not-allowed"
          >
            Unavailable
          </button>
        </div>
      </div>
    </div>

    <!-- No Results -->
    <div v-else-if="searchQuery && !loading" class="text-center py-8">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="mt-2 text-sm text-gray-500">No products found</p>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <p class="mt-2 text-sm text-gray-500">Start typing to search for products</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useToastStore } from '@/stores/toast';
import { productsApi } from '@/services/api';

const cartStore = useCartStore();
const toastStore = useToastStore();

const searchQuery = ref('');
const products = ref([]);
const loading = ref(false);
let searchTimeout = null;

const handleSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  if (!searchQuery.value || searchQuery.value.length < 2) {
    products.value = [];
    return;
  }

  searchTimeout = setTimeout(async () => {
    loading.value = true;
    try {
      const response = await productsApi.list({ 
        search: searchQuery.value,
        is_active: 1,  // Send as integer instead of boolean
        per_page: 50 
      });
      // Handle paginated response
      products.value = response.data.data || response.data || [];
    } catch (error) {
      console.error('Failed to search products:', error);
      toastStore.error('Failed to search products');
    } finally {
      loading.value = false;
    }
  }, 300);
};

const handleBarcodeEnter = async () => {
  // If enter is pressed, try to find exact barcode match
  if (!searchQuery.value) return;

  loading.value = true;
  try {
    const response = await productsApi.list({ 
      search: searchQuery.value,
      is_active: 1,  // Send as integer instead of boolean
      per_page: 1
    });
    
    // Handle paginated response
    const productsData = response.data.data || response.data || [];
    
    if (productsData.length > 0) {
      const product = productsData[0];
      handleAddToCart(product);
      searchQuery.value = '';
      products.value = [];
    }
  } catch (error) {
    console.error('Failed to find product:', error);
  } finally {
    loading.value = false;
  }
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath.startsWith('http')) return imagePath;
  return `/storage/${imagePath}`;
};

const handleAddToCart = (product) => {
  if (product.stock_quantity === 0) {
    toastStore.error('Product is out of stock');
    return;
  }

  cartStore.addItem(product);
  toastStore.success(`${product.name} added to cart`);
};
</script>