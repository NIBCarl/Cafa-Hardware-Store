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
        class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg p-4 hover:border-primary-500 hover:shadow-md transition-all duration-200"
      >
        <!-- Header: Order Number and Status -->
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="text-sm font-semibold text-gray-900">{{ order.order_number }}</h3>
            <p class="text-xs text-gray-500 mt-1">
              {{ formatCustomerName(order.customer) }}
            </p>
          </div>
          <span
            :class="getStatusClass(order.status)"
            class="px-2 py-1 text-xs font-medium rounded-full shadow-sm"
          >
            {{ getStatusLabel(order.status) }}
          </span>
        </div>

        <!-- Order Details Card -->
        <div class="bg-white rounded-md shadow-sm p-3 mb-3 border border-gray-100">
          <!-- Items and Total -->
          <div class="flex justify-between items-center text-sm mb-2 pb-2 border-b border-gray-100">
            <span class="text-gray-600">{{ order.items?.length || 0 }} item(s)</span>
            <span class="font-bold text-primary-600">₱{{ formatAmount(order.total_amount) }}</span>
          </div>

          <!-- Payment & Delivery Info Grid -->
          <div class="grid grid-cols-2 gap-2 text-xs">
            <!-- Payment Method -->
            <div class="bg-gray-50 rounded p-2">
              <p class="text-gray-500 text-[10px] uppercase tracking-wide mb-1">Payment</p>
              <p class="font-medium text-gray-900">{{ formatPaymentMethod(order.payment_method) }}</p>
              
              <!-- Payment Receipt View Icon (for GCash) -->
              <div v-if="order.payment_proof_url" class="mt-1">
                <button
                  @click="viewReceipt(order)"
                  class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 transition-colors"
                  title="View receipt"
                >
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <span class="text-[10px]">View Receipt</span>
                </button>
              </div>
            </div>

            <!-- Delivery Method -->
            <div class="bg-gray-50 rounded p-2">
              <p class="text-gray-500 text-[10px] uppercase tracking-wide mb-1">Delivery</p>
              <p class="font-medium text-gray-900">{{ formatDeliveryMethod(order.delivery_method) }}</p>
            </div>
          </div>

          <!-- Delivery Address (conditionally shown) -->
          <div v-if="order.delivery_method === 'delivery' && order.delivery_address" class="mt-2 pt-2 border-t border-gray-100">
            <p class="text-gray-500 text-[10px] uppercase tracking-wide mb-1">Delivery Address</p>
            <p class="text-xs text-gray-900 leading-relaxed">{{ order.delivery_address }}</p>
          </div>
        </div>

        <!-- Load to Cart Button -->
        <button
          @click="handleLoadOrder(order)"
          :disabled="loadingOrderId === order.id"
          class="w-full bg-primary-600 text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-primary-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
        >
          <span v-if="loadingOrderId === order.id">Loading...</span>
          <span v-else>Load to Cart</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Receipt Modal -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showReceiptModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
        @click.self="closeReceiptModal"
      >
        <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
          <!-- Modal Header -->
          <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-100">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Payment Receipt</h3>
              <p class="text-sm text-gray-600 mt-1">{{ selectedOrder?.order_number }}</p>
            </div>
            <button
              @click="closeReceiptModal"
              class="text-gray-500 hover:text-gray-700 transition-colors p-1 rounded-full hover:bg-white"
            >
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal Body - Receipt Image -->
          <div class="p-4 bg-gray-50 overflow-y-auto max-h-[calc(90vh-120px)]">
            <div class="bg-white rounded-lg shadow-inner p-2">
              <img
                :src="selectedOrder?.payment_proof_url"
                :alt="`Receipt for ${selectedOrder?.order_number}`"
                class="w-full h-auto rounded"
                @error="handleImageError"
              />
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex justify-end gap-2 p-4 border-t border-gray-200 bg-gray-50">
            <a
              :href="selectedOrder?.payment_proof_url"
              target="_blank"
              class="px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-md hover:bg-primary-100 transition-colors inline-flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
              Open in New Tab
            </a>
            <button
              @click="closeReceiptModal"
              class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 hover:shadow-lg transition-all duration-200"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
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
const showReceiptModal = ref(false);
const selectedOrder = ref(null);

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

const formatPaymentMethod = (method) => {
  const methods = {
    cash: 'Cash',
    gcash: 'GCash',
    digital_wallet: 'Digital Wallet',
    bank_transfer: 'Bank Transfer'
  };
  return methods[method] || method;
};

const formatDeliveryMethod = (method) => {
  const methods = {
    pickup: 'Store Pickup',
    delivery: 'Home Delivery'
  };
  return methods[method] || method;
};

const viewReceipt = (order) => {
  if (order.payment_proof_url) {
    selectedOrder.value = order;
    showReceiptModal.value = true;
  }
};

const closeReceiptModal = () => {
  showReceiptModal.value = false;
  selectedOrder.value = null;
};

const handleImageError = (event) => {
  console.error('Failed to load receipt image');
  toastStore.error('Failed to load receipt image');
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

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .bg-white,
.modal-leave-active .bg-white {
  transition: transform 0.3s ease;
}

.modal-enter-from .bg-white {
  transform: scale(0.9);
}

.modal-leave-to .bg-white {
  transform: scale(0.9);
}
</style>

