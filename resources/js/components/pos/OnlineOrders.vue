<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold text-gray-900">Online Orders</h2>
      <button
        @click="loadOrders"
        class="text-primary-600 hover:text-primary-700"
        :disabled="loading"
      >
        <svg
          class="w-5 h-5"
          :class="{ 'animate-spin': loading }"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
          />
        </svg>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && orders.length === 0" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      <p class="mt-2 text-sm text-gray-500">Loading orders...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && orders.length === 0" class="text-center py-8">
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      <p class="mt-2 text-sm text-gray-500">No pending online orders</p>
    </div>

    <!-- Orders List -->
    <div v-else class="space-y-3 max-h-96 overflow-y-auto">
      <div
        v-for="order in orders"
        :key="order.id"
        class="border border-gray-200 rounded-lg p-4 hover:border-primary-500 transition-colors"
      >
        <div class="flex justify-between items-start mb-2">
          <div>
            <h3 class="text-sm font-semibold text-gray-900">{{ order.order_number }}</h3>
            <p class="text-xs text-gray-500 mt-1">
              {{ formatCustomerName(order.customer) }}
            </p>
          </div>
          <span
            :class="getStatusClass(order.status)"
            class="px-2 py-1 text-xs font-medium rounded-full"
          >
            {{ getStatusLabel(order.status) }}
          </span>
        </div>

        <div class="flex justify-between items-center text-sm mb-3">
          <span class="text-gray-600">{{ order.items?.length || 0 }} item(s)</span>
          <span class="font-bold text-primary-600">₱{{ formatAmount(order.total_amount) }}</span>
        </div>

        <button
          @click="handleLoadOrder(order)"
          :disabled="loadingOrderId === order.id"
          class="w-full bg-primary-600 text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loadingOrderId === order.id">Loading...</span>
          <span v-else>Load to Cart</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useToastStore } from '@/stores/toast';
import { ordersApi } from '@/services/api/orders';

const cartStore = useCartStore();
const toastStore = useToastStore();

const orders = ref([]);
const loading = ref(false);
const loadingOrderId = ref(null);

// Emit event when order is loaded
const emit = defineEmits(['order-loaded']);

const loadOrders = async () => {
  loading.value = true;
  try {
    const response = await ordersApi.getPendingOrders();
    orders.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to load orders:', error);
    toastStore.error('Failed to load online orders');
    orders.value = [];
  } finally {
    loading.value = false;
  }
};

const handleLoadOrder = async (order) => {
  loadingOrderId.value = order.id;
  
  try {
    // Fetch full order details to ensure we have all product data
    const response = await ordersApi.getOrder(order.id);
    const fullOrder = response.data;
    
    // Load order items into cart
    cartStore.loadFromOrder(fullOrder);
    
    // Emit event with order data (including customer phone and order ID)
    emit('order-loaded', {
      orderId: fullOrder.id,
      customerPhone: fullOrder.customer?.phone || '',
      customerName: fullOrder.customer?.name || ''
    });
    
    toastStore.success(`Order ${fullOrder.order_number} loaded to cart`);
    
    // Optionally remove the order from the list
    orders.value = orders.value.filter(o => o.id !== order.id);
  } catch (error) {
    console.error('Failed to load order details:', error);
    toastStore.error('Failed to load order to cart');
  } finally {
    loadingOrderId.value = null;
  }
};

const formatCustomerName = (customer) => {
  if (!customer) return 'Guest';
  return customer.name || 'Guest';
};

const formatAmount = (amount) => {
  return Number(amount).toFixed(2);
};

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    processing: 'Processing',
    ready: 'Ready',
    completed: 'Completed',
    cancelled: 'Cancelled'
  };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    ready: 'bg-green-100 text-green-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  loadOrders();
  
  // Auto-refresh every 30 seconds
  const interval = setInterval(loadOrders, 30000);
  
  // Clean up interval on unmount
  onBeforeUnmount(() => {
    clearInterval(interval);
  });
});

// Import onBeforeUnmount
import { onBeforeUnmount } from 'vue';
</script>

<style scoped>
/* Custom scrollbar for orders list */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>

