<template>
  <div class="min-h-screen bg-gray-50">
    <CustomerHeader />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

      <div v-if="cartStore.items.length === 0" class="text-center py-12">
        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="mt-2 text-xl font-medium text-gray-900">Your cart is empty</h3>
        <p class="mt-1 text-gray-500">Start shopping to add items to your cart</p>
        <router-link
          to="/customer/shop"
          class="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
        >
          Continue Shopping
        </router-link>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-4">
          <div
            v-for="item in cartStore.items"
            :key="item.product.id"
            class="bg-white rounded-lg shadow-sm p-6 flex items-center"
          >
            <!-- Product Image -->
            <div class="w-24 h-24 bg-gray-200 rounded-md flex items-center justify-center flex-shrink-0 overflow-hidden">
              <img 
                v-if="item.product.image"
                :src="getImageUrl(item.product.image)" 
                :alt="item.product.name"
                class="w-full h-full object-cover"
              />
              <svg v-else class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>

            <!-- Product Details -->
            <div class="ml-6 flex-1">
              <h3 class="text-lg font-semibold text-gray-900">{{ item.product.name }}</h3>
              <p class="text-sm text-gray-500">{{ item.product.category?.name }}</p>
              <p class="mt-1 text-lg font-semibold text-primary-600">₱{{ formatPrice(item.product.price) }}</p>
            </div>

            <!-- Quantity Controls -->
            <div class="flex items-center space-x-4">
              <div class="flex items-center border border-gray-300 rounded-md">
                <button
                  @click="cartStore.updateQuantity(item.product.id, item.quantity - 1)"
                  class="px-3 py-1 text-gray-600 hover:bg-gray-100"
                >
                  -
                </button>
                <span class="px-4 py-1 text-gray-900 font-medium">{{ item.quantity }}</span>
                <button
                  @click="cartStore.updateQuantity(item.product.id, item.quantity + 1)"
                  :disabled="item.quantity >= item.product.stock_quantity"
                  class="px-3 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  +
                </button>
              </div>

              <button
                @click="cartStore.removeItem(item.product.id)"
                class="text-red-600 hover:text-red-800"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>

            <div class="space-y-3 mb-4">
              <div class="flex justify-between">
                <span class="text-lg font-semibold">Total</span>
                <span class="text-lg font-bold text-primary-600">₱{{ formatPrice(cartStore.total) }}</span>
              </div>
            </div>

            <!-- Checkout Form -->
            <div class="space-y-4 mb-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                <select
                  v-model="checkoutForm.payment_method"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                >
                  <option value="cash">Cash on Delivery/Pickup</option>
                  <option value="gcash">GCash</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Method</label>
                <select
                  v-model="checkoutForm.delivery_method"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                >
                  <option value="pickup">Pickup at Store</option>
                  <option value="delivery">Home Delivery</option>
                </select>
              </div>

              <div v-if="checkoutForm.delivery_method === 'delivery'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                <textarea
                  v-model="checkoutForm.delivery_address"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                  placeholder="Enter your delivery address"
                  required
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                <textarea
                  v-model="checkoutForm.notes"
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                  placeholder="Any special instructions?"
                ></textarea>
              </div>
            </div>

            <button
              @click="handleCheckout"
              :disabled="processing"
              class="w-full bg-primary-600 text-white py-3 px-4 rounded-md font-semibold hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="processing">Processing...</span>
              <span v-else>Place Order</span>
            </button>

            <p class="mt-4 text-xs text-gray-500 text-center">
              By placing your order, you agree to our terms and conditions
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- GCash Payment Modal -->
    <GCashPaymentModal
      v-model="showGCashModal"
      :total-amount="cartStore.total"
      @submit="handleGCashPayment"
      @close="showGCashModal = false"
      ref="gcashModalRef"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useCustomerCartStore } from '@/stores/customerCart';
import { useCustomerAuthStore } from '@/stores/customerAuth';
import { useToastStore } from '@/stores/toast';
import { customerOrdersApi } from '@/services/customerApi';
import CustomerHeader from '@/components/customer/CustomerHeader.vue';
import GCashPaymentModal from '@/components/customer/GCashPaymentModal.vue';

const router = useRouter();
const cartStore = useCustomerCartStore();
const authStore = useCustomerAuthStore();
const toastStore = useToastStore();

const processing = ref(false);
const showGCashModal = ref(false);
const gcashModalRef = ref(null);
const checkoutForm = ref({
  payment_method: 'cash',
  delivery_method: 'pickup',
  delivery_address: '',
  notes: '',
});

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2);
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath.startsWith('http')) return imagePath;
  return `/storage/${imagePath}`;
};

const handleCheckout = async () => {
  if (!authStore.isAuthenticated) {
    toastStore.error('Please login to place an order');
    router.push('/customer/login');
    return;
  }

  if (checkoutForm.value.delivery_method === 'delivery' && !checkoutForm.value.delivery_address.trim()) {
    toastStore.error('Please enter a delivery address');
    return;
  }

  // Check if payment method is GCash - show modal instead of submitting
  if (checkoutForm.value.payment_method === 'gcash' || checkoutForm.value.payment_method === 'digital_wallet') {
    showGCashModal.value = true;
    return;
  }

  // For non-GCash payments, proceed normally
  await submitOrder();
};

const handleGCashPayment = async (paymentProofFile) => {
  // Set modal to submitting state
  if (gcashModalRef.value) {
    gcashModalRef.value.setSubmitting(true);
  }

  try {
    await submitOrder(paymentProofFile);
    
    // Close modal on success
    showGCashModal.value = false;
  } catch (error) {
    // Show error in modal
    if (gcashModalRef.value) {
      const errorMessage = error.response?.data?.message || 'Failed to place order. Please try again.';
      gcashModalRef.value.setError(errorMessage);
    }
  } finally {
    if (gcashModalRef.value) {
      gcashModalRef.value.setSubmitting(false);
    }
  }
};

const submitOrder = async (paymentProofFile = null) => {
  processing.value = true;

  try {
    // Prepare form data
    const formData = new FormData();
    
    // Add items
    cartStore.items.forEach((item, index) => {
      formData.append(`items[${index}][product_id]`, item.product.id);
      formData.append(`items[${index}][quantity]`, item.quantity);
    });

    // Add other fields
    formData.append('payment_method', checkoutForm.value.payment_method);
    formData.append('delivery_method', checkoutForm.value.delivery_method);
    
    if (checkoutForm.value.delivery_method === 'delivery' && checkoutForm.value.delivery_address) {
      formData.append('delivery_address', checkoutForm.value.delivery_address);
    }
    
    if (checkoutForm.value.notes) {
      formData.append('notes', checkoutForm.value.notes);
    }

    // Add payment proof if provided (for GCash)
    if (paymentProofFile) {
      console.log('Adding payment proof file:', paymentProofFile);
      formData.append('payment_proof', paymentProofFile, paymentProofFile.name);
    }

    // Debug: Log FormData contents
    console.log('FormData contents:');
    for (let pair of formData.entries()) {
      console.log(pair[0], pair[1]);
    }

    const response = await customerOrdersApi.create(formData);
    
    const message = paymentProofFile 
      ? 'Order placed successfully! Payment verification pending. You will receive an SMS once verified.'
      : 'Order placed successfully! You will receive an SMS confirmation.';
    
    toastStore.success(message);
    cartStore.clearCart();
    router.push('/customer/orders');
  } catch (error) {
    console.error('Order submission error:', error.response?.data);
    const errorMessage = error.response?.data?.message || 
                        error.response?.data?.errors?.payment_proof?.[0] ||
                        'Failed to place order. Please try again.';
    toastStore.error(errorMessage);
    throw error; // Re-throw to be caught by handleGCashPayment
  } finally {
    processing.value = false;
  }
};
</script>

