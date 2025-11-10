<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Inventory Management</h1>
        <p class="mt-1 text-sm text-gray-600">Manage products, categories, and stock levels</p>
      </div>
      <button
        @click="openProductModal()"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Product
      </button>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex space-x-8">
        <button
          @click="activeTab = 'products'"
          :class="[
            activeTab === 'products'
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          Products
        </button>
        <button
          @click="activeTab = 'categories'"
          :class="[
            activeTab === 'categories'
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          Categories
        </button>
      </nav>
    </div>

    <!-- Products Tab -->
    <div v-if="activeTab === 'products'">
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <!-- Filters -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 mb-4">
            <div>
              <input
                v-model="productFilters.search"
                type="text"
                placeholder="Search products..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                @input="fetchProducts"
              />
            </div>
            <div>
              <select
                v-model="productFilters.category_id"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
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
                v-model="productFilters.is_active"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                @change="fetchProducts"
              >
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>

          <!-- Products Table -->
          <div v-if="loadingProducts" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          </div>

          <div v-else-if="products.length === 0" class="text-center py-12">
            <p class="text-gray-500">No products found</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in products" :key="product.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ product.sku }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ product.category?.name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ₱{{ Number(product.price).toFixed(2) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        product.stock_quantity === 0
                          ? 'bg-red-100 text-red-800'
                          : product.stock_quantity <= product.low_stock_threshold
                          ? 'bg-yellow-100 text-yellow-800'
                          : 'bg-green-100 text-green-800'
                      ]"
                    >
                      {{ product.stock_quantity }} units
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                      ]"
                    >
                      {{ product.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <!-- Adjust Stock Button -->
                      <button
                        @click="openStockModal(product)"
                        class="action-btn action-btn-adjust group"
                        title="Adjust Stock"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                        <span class="action-btn-text">Adjust Stock</span>
                      </button>
                      
                      <!-- Edit Button -->
                      <button
                        @click="openProductModal(product)"
                        class="action-btn action-btn-edit group"
                        title="Edit Product"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="action-btn-text">Edit</span>
                      </button>
                      
                      <!-- Delete Button -->
                      <button
                        @click="deleteProduct(product)"
                        class="action-btn action-btn-delete group"
                        title="Delete Product"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span class="action-btn-text">Delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="productPagination.total > productPagination.per_page" class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ productPagination.from }} to {{ productPagination.to }} of {{ productPagination.total }} products
            </div>
            <div class="flex space-x-2">
              <button
                @click="changePage(productPagination.current_page - 1)"
                :disabled="!productPagination.prev_page_url"
                class="px-3 py-1 border rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button
                @click="changePage(productPagination.current_page + 1)"
                :disabled="!productPagination.next_page_url"
                class="px-3 py-1 border rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Categories Tab -->
    <div v-if="activeTab === 'categories'">
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Categories</h3>
            <button
              @click="openCategoryModal()"
              class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
            >
              Add Category
            </button>
          </div>

          <div v-if="loadingCategories" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          </div>

          <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="category in categories"
              :key="category.id"
              class="border rounded-lg p-4"
            >
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <h4 class="text-lg font-semibold text-gray-900">{{ category.name }}</h4>
                  <p class="text-sm text-gray-500 mt-1">{{ category.description }}</p>
                  <p class="text-xs text-gray-400 mt-2">{{ category.products_count || 0 }} products</p>
                </div>
                <span
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                    category.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ category.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <div class="mt-4 flex gap-2">
                <button
                  @click="openCategoryModal(category)"
                  class="action-btn action-btn-edit group flex-1 justify-center"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  <span class="action-btn-text">Edit</span>
                </button>
                <button
                  @click="deleteCategory(category)"
                  class="action-btn action-btn-delete group flex-1 justify-center"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  <span class="action-btn-text">Delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Modal -->
    <ProductModal
      v-if="showProductModal"
      :product="selectedProduct"
      :categories="categories"
      @close="closeProductModal"
      @saved="handleProductSaved"
    />

    <!-- Stock Adjustment Modal -->
    <StockAdjustmentModal
      v-if="showStockModal"
      :product="selectedProduct"
      @close="closeStockModal"
      @saved="handleStockAdjusted"
    />

    <!-- Category Modal -->
    <CategoryModal
      v-if="showCategoryModal"
      :category="selectedCategory"
      @close="closeCategoryModal"
      @saved="handleCategorySaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { productsApi, categoriesApi } from '@/services/api';
import { useToastStore } from '@/stores/toast';
import ProductModal from '@/components/inventory/ProductModal.vue';
import StockAdjustmentModal from '@/components/inventory/StockAdjustmentModal.vue';
import CategoryModal from '@/components/inventory/CategoryModal.vue';

const toastStore = useToastStore();

const activeTab = ref('products');
const loadingProducts = ref(false);
const loadingCategories = ref(false);

const products = ref([]);
const categories = ref([]);
const productPagination = ref({});

const productFilters = ref({
  search: '',
  category_id: '',
  is_active: '',
});

const showProductModal = ref(false);
const showStockModal = ref(false);
const showCategoryModal = ref(false);
const selectedProduct = ref(null);
const selectedCategory = ref(null);

const fetchProducts = async (page = 1) => {
  loadingProducts.value = true;
  try {
    const response = await productsApi.list({
      ...productFilters.value,
      page,
      per_page: 15,
    });
    console.log('Products API Response:', response);
    console.log('Response.data:', response.data);
    
    // Laravel pagination wraps data in response.data.data
    const responseData = response.data;
    products.value = Array.isArray(responseData.data) ? responseData.data : [];
    
    // Store pagination metadata
    productPagination.value = {
      current_page: responseData.current_page || 1,
      per_page: responseData.per_page || 15,
      total: responseData.total || 0,
      from: responseData.from || 0,
      to: responseData.to || 0,
      last_page: responseData.last_page || 1,
      prev_page_url: responseData.prev_page_url,
      next_page_url: responseData.next_page_url
    };
    
    console.log('Products array:', products.value);
    console.log('Products count:', products.value.length);
    console.log('Pagination:', productPagination.value);
  } catch (error) {
    console.error('Failed to fetch products:', error);
    console.error('Error response:', error.response);
    toastStore.error('Failed to load products');
    products.value = [];
  } finally {
    loadingProducts.value = false;
  }
};

const fetchCategories = async () => {
  loadingCategories.value = true;
  try {
    const response = await categoriesApi.list({ per_page: 100 });
    const responseData = response.data;
    categories.value = Array.isArray(responseData.data) ? responseData.data : (Array.isArray(responseData) ? responseData : []);
    console.log('Categories loaded:', categories.value.length);
  } catch (error) {
    console.error('Failed to fetch categories:', error);
    toastStore.error('Failed to load categories');
    categories.value = [];
  } finally {
    loadingCategories.value = false;
  }
};

const changePage = (page) => {
  fetchProducts(page);
};

const openProductModal = (product = null) => {
  selectedProduct.value = product;
  showProductModal.value = true;
};

const closeProductModal = () => {
  selectedProduct.value = null;
  showProductModal.value = false;
};

const handleProductSaved = () => {
  closeProductModal();
  fetchProducts();
};

const openStockModal = (product) => {
  selectedProduct.value = product;
  showStockModal.value = true;
};

const closeStockModal = () => {
  selectedProduct.value = null;
  showStockModal.value = false;
};

const handleStockAdjusted = () => {
  closeStockModal();
  fetchProducts();
};

const openCategoryModal = (category = null) => {
  selectedCategory.value = category;
  showCategoryModal.value = true;
};

const closeCategoryModal = () => {
  selectedCategory.value = null;
  showCategoryModal.value = false;
};

const handleCategorySaved = () => {
  closeCategoryModal();
  fetchCategories();
  if (activeTab.value === 'products') {
    fetchProducts();
  }
};

const deleteProduct = async (product) => {
  if (!confirm(`Are you sure you want to delete "${product.name}"?`)) {
    return;
  }

  try {
    await productsApi.delete(product.id);
    toastStore.success('Product deleted successfully');
    fetchProducts();
  } catch (error) {
    console.error('Failed to delete product:', error);
    toastStore.error(error.response?.data?.message || 'Failed to delete product');
  }
};

const deleteCategory = async (category) => {
  if (!confirm(`Are you sure you want to delete "${category.name}"?`)) {
    return;
  }

  try {
    await categoriesApi.delete(category.id);
    toastStore.success('Category deleted successfully');
    fetchCategories();
  } catch (error) {
    console.error('Failed to delete category:', error);
    toastStore.error(error.response?.data?.message || 'Failed to delete category');
  }
};

onMounted(() => {
  fetchProducts();
  fetchCategories();
});
</script>

<style scoped>
/* Action Buttons - Enhanced with depth and hierarchy */
.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.875rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease-in-out;
  border: 1px solid transparent;
  cursor: pointer;
  
  /* Small shadow for depth - light on top, dark on bottom */
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.05),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.1);
}

/* Adjust Stock Button - Cyan/Teal (Information action) */
.action-btn-adjust {
  background: linear-gradient(to bottom, #06b6d4, #0891b2);
  color: white;
  border-color: #0891b2;
}

.action-btn-adjust:hover {
  background: linear-gradient(to bottom, #22d3ee, #06b6d4);
  
  /* Bigger shadow on hover for prominence */
  box-shadow: 
    0 4px 6px -1px rgba(6, 182, 212, 0.3),
    0 2px 4px -1px rgba(6, 182, 212, 0.2),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.15);
  
  transform: translateY(-1px);
}

.action-btn-adjust:active {
  transform: translateY(0);
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.1),
    inset 0 2px 4px 0 rgba(0, 0, 0, 0.1);
}

/* Edit Button - Blue/Primary (Standard action) */
.action-btn-edit {
  background: linear-gradient(to bottom, #3b82f6, #2563eb);
  color: white;
  border-color: #2563eb;
}

.action-btn-edit:hover {
  background: linear-gradient(to bottom, #60a5fa, #3b82f6);
  
  /* Bigger shadow on hover */
  box-shadow: 
    0 4px 6px -1px rgba(59, 130, 246, 0.3),
    0 2px 4px -1px rgba(59, 130, 246, 0.2),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.15);
  
  transform: translateY(-1px);
}

.action-btn-edit:active {
  transform: translateY(0);
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.1),
    inset 0 2px 4px 0 rgba(0, 0, 0, 0.1);
}

/* Delete Button - Red/Danger (Destructive action) */
.action-btn-delete {
  background: linear-gradient(to bottom, #ef4444, #dc2626);
  color: white;
  border-color: #dc2626;
}

.action-btn-delete:hover {
  background: linear-gradient(to bottom, #f87171, #ef4444);
  
  /* Bigger shadow on hover */
  box-shadow: 
    0 4px 6px -1px rgba(239, 68, 68, 0.3),
    0 2px 4px -1px rgba(239, 68, 68, 0.2),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.15);
  
  transform: translateY(-1px);
}

.action-btn-delete:active {
  transform: translateY(0);
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.1),
    inset 0 2px 4px 0 rgba(0, 0, 0, 0.1);
}

/* Icon styling */
.action-btn svg {
  flex-shrink: 0;
  transition: transform 0.2s ease-in-out;
}

.action-btn:hover svg {
  transform: scale(1.1);
}

/* Button text - responsive */
.action-btn-text {
  white-space: nowrap;
}

/* ===================================
   RESPONSIVE DESIGN - Mobile First
   =================================== */

/* Mobile: Icon-only buttons for space efficiency */
@media (max-width: 640px) {
  .action-btn-text {
    display: none;
  }
  
  .action-btn {
    padding: 0.625rem; /* 10px - Larger touch target for mobile */
    min-width: 2.5rem; /* 40px minimum for touch accessibility */
    min-height: 2.5rem; /* 40px minimum for touch accessibility */
    justify-content: center;
  }
  
  .action-btn svg {
    width: 1.125rem; /* 18px - Larger icons on mobile */
    height: 1.125rem;
  }
}

/* Tablet: Show icons with abbreviated text on larger tablets */
@media (min-width: 641px) and (max-width: 1024px) {
  .action-btn {
    padding: 0.5rem 0.75rem;
    min-height: 2.25rem; /* 36px touch target */
  }
  
  .action-btn-text {
    font-size: 0.813rem; /* Slightly smaller text */
  }
}

/* Desktop: Full buttons with icon + text */
@media (min-width: 1025px) {
  .action-btn {
    padding: 0.5rem 0.875rem;
  }
}

/* Stacking buttons on very small screens */
@media (max-width: 480px) {
  .action-btn {
    flex: 1;
    min-width: 0;
  }
  
  /* Stack buttons vertically on extra small screens if needed */
  td .flex {
    flex-direction: column;
    align-items: stretch;
    gap: 0.375rem;
  }
  
  .action-btn {
    width: 100%;
    justify-content: center;
  }
}

/* Focus states for accessibility */
.action-btn:focus {
  outline: none;
}

.action-btn-adjust:focus {
  box-shadow: 
    0 0 0 2px white,
    0 0 0 4px #06b6d4;
}

.action-btn-edit:focus {
  box-shadow: 
    0 0 0 2px white,
    0 0 0 4px #3b82f6;
}

.action-btn-delete:focus {
  box-shadow: 
    0 0 0 2px white,
    0 0 0 4px #ef4444;
}

/* ===================================
   TABLE RESPONSIVE ENHANCEMENTS
   =================================== */

/* Reduce table padding on mobile */
@media (max-width: 768px) {
  table th,
  table td {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
  
  /* Hide less critical columns on mobile */
  table th:nth-child(2), /* SKU */
  table td:nth-child(2),
  table th:nth-child(3), /* Category */
  table td:nth-child(3) {
    display: none;
  }
}

/* Extra small screens - show only essential info */
@media (max-width: 480px) {
  table th,
  table td {
    padding: 0.75rem 0.5rem !important;
    font-size: 0.813rem;
  }
  
  /* Further reduce visible columns */
  table th:nth-child(5), /* Stock */
  table td:nth-child(5) {
    display: none;
  }
}

/* Smooth scrolling hint for horizontal tables */
.overflow-x-auto {
  -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
  scroll-behavior: smooth;
}

/* Add scroll indicator shadow on mobile */
@media (max-width: 768px) {
  .overflow-x-auto {
    position: relative;
  }
  
  .overflow-x-auto::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 2rem;
    background: linear-gradient(to left, rgba(255, 255, 255, 0.9), transparent);
    pointer-events: none;
  }
}

/* ===================================
   PERFORMANCE OPTIMIZATIONS
   =================================== */

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  .action-btn,
  .action-btn svg {
    transition: none !important;
    transform: none !important;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .action-btn {
    border-width: 2px;
  }
}
</style>