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
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <button
                      @click="openStockModal(product)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      Adjust Stock
                    </button>
                    <button
                      @click="openProductModal(product)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteProduct(product)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
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
              <div class="mt-4 flex space-x-2">
                <button
                  @click="openCategoryModal(category)"
                  class="text-sm text-primary-600 hover:text-primary-900"
                >
                  Edit
                </button>
                <button
                  @click="deleteCategory(category)"
                  class="text-sm text-red-600 hover:text-red-900"
                >
                  Delete
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