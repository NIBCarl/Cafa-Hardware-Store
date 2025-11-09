<template>
  <div class="bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 group transform hover:-translate-y-2">
    <!-- Product Image -->
    <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden relative">
      <img 
        v-if="product.image"
        :src="getImageUrl(product.image)" 
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
        @error="handleImageError"
      />
      <svg v-else class="h-24 w-24 text-gray-400 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
      </svg>
      
      <!-- Stock Badge -->
      <div v-if="product.stock_quantity > 0 && product.stock_quantity <= 10" class="absolute top-3 right-3">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 shadow-sm">
          Only {{ product.stock_quantity }} left!
        </span>
      </div>
      <div v-else-if="product.stock_quantity === 0" class="absolute inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
        <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-red-500 text-white shadow-lg">
          Out of Stock
        </span>
      </div>
    </div>

    <!-- Product Details -->
    <div class="p-5 bg-gradient-to-b from-white to-gray-50">
      <!-- Category Badge -->
      <div class="mb-2">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gradient-to-r from-primary-50 to-primary-100 text-primary-700 shadow-sm">
          {{ product.category?.name || 'Uncategorized' }}
        </span>
      </div>
      
      <!-- Product Name -->
      <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors">
        {{ product.name }}
      </h3>

      <!-- Description -->
      <p v-if="product.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
        {{ product.description }}
      </p>

      <!-- Price -->
      <div class="flex items-baseline justify-between mb-4">
        <div class="flex items-baseline">
          <span class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-primary-700 bg-clip-text text-transparent">₱{{ formatPrice(product.price) }}</span>
        </div>
        <div v-if="product.stock_quantity > 10" class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-md">
          In Stock
        </div>
      </div>

      <!-- Add to Cart Button -->
      <button
        @click="addToCart"
        :disabled="product.stock_quantity === 0"
        class="w-full bg-gradient-to-r from-primary-600 to-primary-700 text-white py-3 px-4 rounded-lg font-semibold shadow-md hover:shadow-xl hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:from-gray-400 disabled:to-gray-500 transition-all duration-200 transform active:scale-95"
      >
        <span v-if="product.stock_quantity > 0" class="flex items-center justify-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Add to Cart
        </span>
        <span v-else>Out of Stock</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['add-to-cart']);

const imageError = ref(false);

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2);
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath.startsWith('http')) return imagePath;
  return `/storage/${imagePath}`;
};

const handleImageError = () => {
  imageError.value = true;
};

const addToCart = () => {
  emit('add-to-cart', props.product);
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

