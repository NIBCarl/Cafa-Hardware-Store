<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <!-- Basic Information -->
      <div class="space-y-4">
        <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
        
        <BaseInput
          id="name"
          v-model="form.name"
          label="Product Name"
          :error="errors.name"
          required
        />

        <BaseInput
          id="sku"
          v-model="form.sku"
          label="SKU"
          :error="errors.sku"
          required
        />

        <BaseInput
          id="barcode"
          v-model="form.barcode"
          label="Barcode"
          :error="errors.barcode"
        />

        <div>
          <label class="block text-sm font-medium text-gray-700">
            Category
          </label>
          <select
            v-model="form.category_id"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
          >
            <option value="">Select a category</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
          <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">
            {{ errors.category_id }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">
            Description
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">
            {{ errors.description }}
          </p>
        </div>
      </div>

      <!-- Pricing and Inventory -->
      <div class="space-y-4">
        <h3 class="text-lg font-medium text-gray-900">Pricing & Inventory</h3>

        <BaseInput
          id="price"
          v-model="form.price"
          type="number"
          label="Selling Price"
          :error="errors.price"
          required
          min="0"
          step="0.01"
        />

        <BaseInput
          id="cost"
          v-model="form.cost"
          type="number"
          label="Cost Price"
          :error="errors.cost"
          min="0"
          step="0.01"
        />

        <BaseInput
          id="stock_quantity"
          v-model="form.stock_quantity"
          type="number"
          label="Current Stock"
          :error="errors.stock_quantity"
          required
          min="0"
          :disabled="!isNew"
        />

        <BaseInput
          id="low_stock_threshold"
          v-model="form.low_stock_threshold"
          type="number"
          label="Low Stock Alert Threshold"
          :error="errors.low_stock_threshold"
          required
          min="0"
        />

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
      </div>
    </div>

    <div class="flex justify-end space-x-3">
      <BaseButton
        type="button"
        variant="secondary"
        @click="$emit('cancel')"
      >
        Cancel
      </BaseButton>
      <BaseButton
        type="submit"
        variant="primary"
        :loading="isSubmitting"
      >
        {{ isNew ? 'Create Product' : 'Update Product' }}
      </BaseButton>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useToastStore } from '../../stores/toast';
import { categoryService, productService } from '../../services/api';

const props = defineProps({
  product: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['submit', 'cancel']);
const toastStore = useToastStore();

const isNew = computed(() => !props.product);
const isSubmitting = ref(false);
const categories = ref([]);
const errors = reactive({});

const form = reactive({
  name: '',
  sku: '',
  barcode: '',
  description: '',
  category_id: '',
  price: '',
  cost: '',
  stock_quantity: 0,
  low_stock_threshold: 10,
  is_active: true
});

// Initialize form with product data if editing
if (props.product) {
  Object.assign(form, props.product);
}

const loadCategories = async () => {
  try {
    const response = await categoryService.getCategories({
      is_active: true
    });
    categories.value = response.data.data;
  } catch (error) {
    toastStore.error('Failed to load categories');
  }
};

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;
    // Clear previous errors
    Object.keys(errors).forEach(key => delete errors[key]);

    const response = isNew.value
      ? await productService.createProduct(form)
      : await productService.updateProduct(props.product.id, form);

    toastStore.success(
      isNew.value
        ? 'Product created successfully'
        : 'Product updated successfully'
    );
    emit('submit', response.data);
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors);
    } else {
      toastStore.error(
        isNew.value
          ? 'Failed to create product'
          : 'Failed to update product'
      );
    }
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  loadCategories();
});
</script>
