<template>
  <div class="min-h-screen bg-gray-50">
    <CustomerHeader />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Back Button -->
      <router-link
        to="/customer/orders"
        class="inline-flex items-center text-primary-600 hover:text-primary-700 mb-6"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Orders
      </router-link>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        <p class="mt-4 text-gray-600">Loading order details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-2 text-lg font-medium text-red-900">Failed to load order</h3>
        <p class="mt-1 text-red-700">{{ error }}</p>
        <router-link
          to="/customer/orders"
          class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
        >
          Return to Orders
        </router-link>
      </div>

      <!-- Order Details -->
      <div v-else-if="order" class="space-y-6">
        <!-- Order Header Card -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-6 text-white">
            <div class="flex justify-between items-start">
              <div>
                <h1 class="text-2xl font-bold">Order #{{ order.order_number }}</h1>
                <p class="mt-2 text-primary-100">Placed on {{ formatDate(order.created_at) }}</p>
              </div>
              <span
                :class="[
                  'inline-flex px-4 py-2 text-sm font-semibold rounded-full',
                  getStatusColor(order.status)
                ]"
              >
                {{ getStatusLabel(order.status) }}
              </span>
            </div>
          </div>

          <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <p class="text-sm text-gray-500">Customer</p>
                <p class="mt-1 font-medium text-gray-900">{{ order.customer?.name || 'N/A' }}</p>
                <p class="text-sm text-gray-600">{{ order.customer?.email || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Payment Method</p>
                <p class="mt-1 font-medium text-gray-900">{{ formatPaymentMethod(order.payment_method) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Delivery Method</p>
                <p class="mt-1 font-medium text-gray-900">{{ formatDeliveryMethod(order.delivery_method) }}</p>
              </div>
            </div>

            <div v-if="order.delivery_address" class="mt-6 pt-6 border-t border-gray-200">
              <p class="text-sm text-gray-500">Delivery Address</p>
              <p class="mt-1 font-medium text-gray-900">{{ order.delivery_address }}</p>
            </div>

            <div v-if="order.notes" class="mt-4">
              <p class="text-sm text-gray-500">Order Notes</p>
              <p class="mt-1 text-gray-900">{{ order.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Order Items Card -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
          </div>

          <div class="px-6 py-4">
            <div class="space-y-4">
              <div
                v-for="item in order.items"
                :key="item.id"
                class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0"
              >
                <div class="flex items-center flex-1">
                  <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                    <img 
                      v-if="item.product?.image"
                      :src="getImageUrl(item.product.image)" 
                      :alt="item.product.name"
                      class="w-full h-full object-cover"
                    />
                    <svg v-else class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                  </div>
                  <div class="ml-6 flex-1">
                    <h3 class="text-lg font-medium text-gray-900">{{ item.product?.name || 'Product' }}</h3>
                    <p class="mt-1 text-sm text-gray-500">SKU: {{ item.product?.sku || 'N/A' }}</p>
                    <div class="mt-2 flex items-center space-x-4 text-sm">
                      <span class="text-gray-600">Qty: <span class="font-medium">{{ item.quantity }}</span></span>
                      <span class="text-gray-600">Price: <span class="font-medium">₱{{ formatPrice(item.unit_price) }}</span></span>
                    </div>
                  </div>
                </div>
                <div class="text-right ml-6">
                  <p class="text-lg font-semibold text-gray-900">₱{{ formatPrice(item.subtotal) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium text-gray-900">₱{{ formatPrice(calculateSubtotal()) }}</span>
              </div>
              <div v-if="order.discount_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600">Discount</span>
                <span class="font-medium text-green-600">-₱{{ formatPrice(order.discount_amount) }}</span>
              </div>
              <div v-if="order.tax_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600">Tax</span>
                <span class="font-medium text-gray-900">₱{{ formatPrice(order.tax_amount) }}</span>
              </div>
              <div class="pt-2 border-t border-gray-300 flex justify-between">
                <span class="text-lg font-semibold text-gray-900">Total Amount</span>
                <span class="text-2xl font-bold text-primary-600">₱{{ formatPrice(order.total_amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
          <router-link
            to="/customer/orders"
            class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Back to Orders
          </router-link>
          <button
            v-if="order.status === 'pending'"
            @click="handleCancelOrder"
            class="px-6 py-3 border border-red-300 rounded-lg text-sm font-medium text-red-700 hover:bg-red-50"
          >
            Cancel Order
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { customerOrdersApi } from '@/services/customerApi';
import { useToastStore } from '@/stores/toast';
import CustomerHeader from '@/components/customer/CustomerHeader.vue';

const route = useRoute();
const router = useRouter();
const toastStore = useToastStore();

const order = ref(null);
const loading = ref(false);
const error = ref(null);

const fetchOrderDetail = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const orderId = route.params.id;
    const response = await customerOrdersApi.show(orderId);
    order.value = response.data.order;
  } catch (err) {
    console.error('Failed to fetch order details:', err);
    error.value = err.response?.data?.message || 'Failed to load order details';
  } finally {
    loading.value = false;
  }
};

const handleCancelOrder = async () => {
  if (!confirm('Are you sure you want to cancel this order?')) {
    return;
  }

  try {
    await customerOrdersApi.cancel(order.value.id);
    toastStore.success('Order cancelled successfully');
    router.push('/customer/orders');
  } catch (err) {
    toastStore.error(err.response?.data?.message || 'Failed to cancel order');
  }
};

const formatPrice = (price) => {
  return parseFloat(price || 0).toFixed(2);
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
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
    ready: 'Ready for Pickup',
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

const calculateSubtotal = () => {
  if (!order.value?.items) return 0;
  return order.value.items.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0);
};

onMounted(() => {
  fetchOrderDetail();
});
</script>
