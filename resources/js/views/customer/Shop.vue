<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <CustomerHeader />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Search and Filter Bar -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search products..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="selectedCategory"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
              @change="fetchProducts"
            >
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div>
            <select
              v-model="sortBy"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
              @change="fetchProducts"
            >
              <option value="name">Name A-Z</option>
              <option value="price">Price: Low to High</option>
              <option value="created_at">Newest First</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        <p class="mt-4 text-gray-600">Loading products...</p>
      </div>

      <!-- Products Grid -->
      <div v-else-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <ProductCard
          v-for="product in products"
          :key="product.id"
          :product="product"
          @add-to-cart="handleAddToCart"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters</p>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="mt-8 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
              page === pagination.current_page
                ? 'z-10 bg-primary-50 border-primary-500 text-primary-600'
                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { customerProductsApi } from '@/services/customerApi';
import { useCustomerCartStore } from '@/stores/customerCart';
import { useToastStore } from '@/stores/toast';
import CustomerHeader from '@/components/customer/CustomerHeader.vue';
import ProductCard from '@/components/customer/ProductCard.vue';

const cartStore = useCustomerCartStore();
const toastStore = useToastStore();

const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const selectedCategory = ref('');
const sortBy = ref('name');

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0,
});

const visiblePages = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const pages = [];
  
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i);
  }
  
  return pages;
});

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchProducts();
  }, 500);
};

const fetchProducts = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: searchQuery.value,
      category_id: selectedCategory.value,
      sort_by: sortBy.value,
      sort_order: sortBy.value === 'price' ? 'asc' : 'asc',
    };

    const response = await customerProductsApi.list(params);
    products.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
    };
  } catch (error) {
    console.error('Failed to fetch products:', error);
    toastStore.error('Failed to load products');
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await customerProductsApi.categories();
    categories.value = response.data.categories;
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchProducts(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const handleAddToCart = (product) => {
  cartStore.addToCart(product);
  toastStore.success(`${product.name} added to cart`);
};

onMounted(() => {
  fetchProducts();
  fetchCategories();
});
</script>

