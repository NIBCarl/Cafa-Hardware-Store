<template>
  <div class="h-full flex flex-col">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Point of Sale</h1>
      <p class="mt-1 text-sm text-gray-600">Process customer transactions</p>
    </div>

    <div class="flex-1 grid grid-cols-1 lg:grid-cols-4 gap-6 overflow-hidden">
      <!-- Online Orders Section -->
      <div class="lg:col-span-1 overflow-auto">
        <OnlineOrders @order-loaded="handleOrderLoaded" />
      </div>

      <!-- Product Search Section -->
      <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6 overflow-auto">
        <ProductSearch />
      </div>

      <!-- Shopping Cart Section -->
      <div class="lg:col-span-1">
        <ShoppingCart ref="shoppingCartRef" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ProductSearch from '@/components/pos/ProductSearch.vue';
import ShoppingCart from '@/components/pos/ShoppingCart.vue';
import OnlineOrders from '@/components/pos/OnlineOrders.vue';

const shoppingCartRef = ref(null);

const handleOrderLoaded = (orderData) => {
  // Pass order data to shopping cart
  if (shoppingCartRef.value) {
    shoppingCartRef.value.setOrderData(orderData);
  }
};
</script>