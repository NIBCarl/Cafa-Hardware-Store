<template>
  <div class="fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            {{ product ? 'Edit Product' : 'Add New Product' }}
          </h3>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                <input
                  id="sku"
                  v-model="form.sku"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                />
              </div>

              <div>
                <label for="barcode" class="block text-sm font-medium text-gray-700">Barcode</label>
                <input
                  id="barcode"
                  v-model="form.barcode"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                />
              </div>
            </div>

            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              ></textarea>
            </div>

            <!-- Image Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
              <div class="flex items-center space-x-4">
                <!-- Image Preview -->
                <div class="flex-shrink-0">
                  <div v-if="imagePreview || product?.image" class="relative w-24 h-24 rounded-lg overflow-hidden border-2 border-gray-300">
                    <img 
                      :src="imagePreview || getImageUrl(product?.image)" 
                      alt="Product preview"
                      class="w-full h-full object-cover"
                    />
                    <button
                      v-if="imagePreview"
                      type="button"
                      @click="clearImage"
                      class="absolute top-0 right-0 bg-red-500 text-white rounded-bl-lg p-1 hover:bg-red-600"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <div v-else class="w-24 h-24 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                </div>

                <!-- Upload Button -->
                <div class="flex-1">
                  <label class="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Choose Image
                    <input
                      type="file"
                      class="hidden"
                      accept="image/*"
                      @change="handleImageSelect"
                    />
                  </label>
                  <p class="mt-1 text-xs text-gray-500">
                    PNG, JPG, GIF up to 2MB
                  </p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Price (₱)</label>
                <input
                  id="price"
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                />
              </div>

              <div>
                <label for="cost" class="block text-sm font-medium text-gray-700">Cost (₱)</label>
                <input
                  id="cost"
                  v-model="form.cost"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                <input
                  id="stock_quantity"
                  v-model="form.stock_quantity"
                  type="number"
                  min="0"
                  required
                  :disabled="!!product"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm disabled:bg-gray-100"
                />
                <p v-if="product" class="mt-1 text-xs text-gray-500">Use "Adjust Stock" to modify quantity</p>
              </div>

              <div>
                <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700">Low Stock Alert</label>
                <input
                  id="low_stock_threshold"
                  v-model="form.low_stock_threshold"
                  type="number"
                  min="0"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                />
              </div>
            </div>

            <div>
              <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
              <select
                id="category_id"
                v-model="form.category_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              >
                <option value="">None</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active
              </label>
            </div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
              <button
                type="submit"
                :disabled="saving"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:col-start-2 sm:text-sm disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
              <button
                type="button"
                @click="$emit('close')"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { productsApi } from '@/services/api';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
  product: Object,
  categories: Array,
});

const emit = defineEmits(['close', 'saved']);
const toastStore = useToastStore();

const saving = ref(false);
const imageFile = ref(null);
const imagePreview = ref(null);

const form = ref({
  name: '',
  sku: '',
  barcode: '',
  description: '',
  price: 0,
  cost: 0,
  stock_quantity: 0,
  low_stock_threshold: 10,
  category_id: '',
  is_active: true,
});

// Initialize form with product data if editing
watch(() => props.product, (product) => {
  // Clear image selection when switching products
  imageFile.value = null;
  imagePreview.value = null;
  
  if (product) {
    form.value = {
      name: product.name,
      sku: product.sku,
      barcode: product.barcode || '',
      description: product.description || '',
      price: product.price,
      cost: product.cost || 0,
      stock_quantity: product.stock_quantity,
      low_stock_threshold: product.low_stock_threshold,
      category_id: product.category_id || '',
      is_active: product.is_active,
    };
  } else {
    // Reset form for new product
    form.value = {
      name: '',
      sku: '',
      barcode: '',
      description: '',
      price: 0,
      cost: 0,
      stock_quantity: 0,
      low_stock_threshold: 10,
      category_id: '',
      is_active: true,
    };
  }
}, { immediate: true });

const handleImageSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    imageFile.value = file;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const clearImage = () => {
  imageFile.value = null;
  imagePreview.value = null;
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath.startsWith('http')) return imagePath;
  return `/storage/${imagePath}`;
};

const handleSubmit = async () => {
  saving.value = true;

  try {
    // Determine if we need FormData (for file upload) or regular JSON
    const hasNewImage = !!imageFile.value;
    
    if (hasNewImage) {
      // Use FormData for file upload
      const formData = new FormData();
      
      // Append all form fields with proper type conversion
      Object.keys(form.value).forEach(key => {
        let value = form.value[key];
        
        // Skip null or empty values
        if (value === null || value === '') return;
        
        // Convert boolean to integer for Laravel
        if (typeof value === 'boolean') {
          value = value ? 1 : 0;
        }
        
        formData.append(key, value);
      });

      // Append the new image file
      formData.append('image', imageFile.value);

      if (props.product) {
        // For updates with file upload, use POST with _method spoofing
        formData.append('_method', 'PUT');
        await productsApi.createOrUpdateWithImage(props.product.id, formData);
        toastStore.success('Product updated successfully');
      } else {
        // Create new product with image
        await productsApi.create(formData);
        toastStore.success('Product created successfully');
      }
    } else {
      // No new image - use regular JSON payload
      if (props.product) {
        await productsApi.update(props.product.id, form.value);
        toastStore.success('Product updated successfully');
      } else {
        await productsApi.create(form.value);
        toastStore.success('Product created successfully');
      }
    }

    emit('saved');
  } catch (error) {
    console.error('Failed to save product:', error);
    toastStore.error(error.response?.data?.message || 'Failed to save product');
  } finally {
    saving.value = false;
  }
};
</script>
