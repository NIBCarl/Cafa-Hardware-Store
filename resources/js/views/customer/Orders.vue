<template>
  <div class="min-h-screen bg-gray-50">
    <CustomerHeader />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">My Orders</h1>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        <p class="mt-4 text-gray-600">Loading orders...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="orders.length === 0" class="text-center py-12">
        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-xl font-medium text-gray-900">No orders yet</h3>
        <p class="mt-1 text-gray-500">Start shopping to place your first order</p>
        <router-link
          to="/customer/shop"
          class="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
        >
          Start Shopping
        </router-link>
      </div>

      <!-- Orders List -->
      <div v-else class="space-y-6">
        <div
          v-for="order in orders"
          :key="order.id"
          class="bg-white rounded-lg shadow-sm overflow-hidden"
        >
          <!-- Order Header -->
          <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Order #{{ order.order_number }}</h3>
                <p class="text-sm text-gray-500 mt-1">
                  Placed on {{ formatDate(order.created_at) }}
                </p>
              </div>
              <div class="text-right">
                <span
                  :class="[
                    'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
                    getStatusColor(order.status)
                  ]"
                >
                  {{ getStatusLabel(order.status) }}
                </span>
                <p class="text-lg font-bold text-gray-900 mt-2">
                  ₱{{ formatPrice(order.total_amount) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Order Items -->
          <div class="px-6 py-4">
            <div class="space-y-3">
              <div
                v-for="item in order.items"
                :key="item.id"
                class="flex items-center justify-between"
              >
                <div class="flex items-center">
                  <div class="w-16 h-16 bg-gray-200 rounded-md flex items-center justify-center flex-shrink-0 overflow-hidden">
                    <img 
                      v-if="item.product.image"
                      :src="getImageUrl(item.product.image)" 
                      :alt="item.product.name"
                      class="w-full h-full object-cover"
                    />
                    <svg v-else class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="font-medium text-gray-900">{{ item.product.name }}</p>
                    <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
                  </div>
                </div>
                <p class="font-semibold text-gray-900">₱{{ formatPrice(item.subtotal) }}</p>
              </div>
            </div>

            <!-- Order Details -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <p class="text-gray-500">Payment Method:</p>
                  <p class="font-medium text-gray-900">{{ formatPaymentMethod(order.payment_method) }}</p>
                </div>
                <div>
                  <p class="text-gray-500">Delivery Method:</p>
                  <p class="font-medium text-gray-900">{{ formatDeliveryMethod(order.delivery_method) }}</p>
                </div>
                <div v-if="order.delivery_address" class="col-span-2">
                  <p class="text-gray-500">Delivery Address:</p>
                  <p class="font-medium text-gray-900">{{ order.delivery_address }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Actions -->
          <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
            <router-link
              :to="`/customer/orders/${order.id}`"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              View Details
            </router-link>
            <button
              v-if="order.status === 'pending'"
              @click="handleCancelOrder(order.id)"
              class="px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 hover:bg-red-50"
            >
              Cancel Order
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { customerOrdersApi } from '@/services/customerApi';
import { useToastStore } from '@/stores/toast';
import { useCustomerAuthStore } from '@/stores/customerAuth';
import { useCustomerChannel } from '@/composables/useRealtime';
import CustomerHeader from '@/components/customer/CustomerHeader.vue';

const toastStore = useToastStore();
const authStore = useCustomerAuthStore();

const customerId = computed(() => authStore.customer?.id);

const orders = ref([]);
const loading = ref(false);

const fetchOrders = async () => {
  loading.value = true;
  try {
    const response = await customerOrdersApi.list();
    orders.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch orders:', error);
    toastStore.error('Failed to load orders');
  } finally {
    loading.value = false;
  }
};

const handleCancelOrder = async (orderId) => {
  if (!confirm('Are you sure you want to cancel this order?')) {
    return;
  }

  try {
    await customerOrdersApi.cancel(orderId);
    toastStore.success('Order cancelled successfully');
    fetchOrders();
  } catch (error) {
    toastStore.error('Failed to cancel order');
  }
};

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2);
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath.startsWith('http')) return imagePath;
  return `/storage/${imagePath}`;
};

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    processing: 'Processing',
    ready: 'Ready',
    completed: 'Completed',
    cancelled: 'Cancelled',
  };
  return labels[status] || status;
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-purple-100 text-purple-800',
    ready: 'bg-green-100 text-green-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatPaymentMethod = (method) => {
  const methods = {
    cash: 'Cash',
    digital_wallet: 'Digital Wallet',
    gcash: 'GCash',
  };
  return methods[method] || method;
};

const formatDeliveryMethod = (method) => {
  const methods = {
    pickup: 'Store Pickup',
    delivery: 'Home Delivery',
  };
  return methods[method] || method;
};

// Listen for real-time order status updates
useCustomerChannel(customerId, {
  onOrderStatusChange: (event) => {
    // Update order in the list
    const orderIndex = orders.value.findIndex(o => o.id === event.order.id);
    if (orderIndex !== -1) {
      orders.value[orderIndex].status = event.new_status;
    }
    
    // Show notification
    toastStore.info(event.message || `Order status updated to ${event.new_status}`);
  },
});

onMounted(() => {
  fetchOrders();
});
</script>

