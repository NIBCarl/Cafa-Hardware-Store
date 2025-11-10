<template>
  <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col h-full">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">Shopping Cart</h2>

    <!-- Cart Items -->
    <div class="flex-1 overflow-y-auto mb-4">
      <div v-if="cartStore.items.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="mt-2 text-sm text-gray-500">Cart is empty</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="item in cartStore.items"
          :key="item.product.id"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex-1 mr-4">
            <h3 class="text-sm font-semibold text-gray-900">{{ item.product.name }}</h3>
            <p class="text-xs text-gray-500">₱{{ Number(item.product.price).toFixed(2) }} each</p>
          </div>

          <div class="flex items-center space-x-2">
            <button
              @click="cartStore.decreaseQuantity(item.product.id)"
              class="w-7 h-7 flex items-center justify-center bg-white border border-gray-300 rounded-md hover:bg-gray-100"
            >
              <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
              </svg>
            </button>

            <span class="w-10 text-center font-medium text-sm">{{ item.quantity }}</span>

            <button
              @click="cartStore.increaseQuantity(item.product.id)"
              :disabled="item.quantity >= item.product.stock_quantity"
              class="w-7 h-7 flex items-center justify-center bg-white border border-gray-300 rounded-md hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </button>

            <button
              @click="cartStore.removeItem(item.product.id)"
              class="ml-2 text-red-600 hover:text-red-800"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>

          <div class="ml-4 text-right">
            <p class="text-sm font-bold text-gray-900">
              ₱{{ (Number(item.product.price) * item.quantity).toFixed(2) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Cart Summary -->
    <div class="border-t pt-4 space-y-2">
      <div class="flex justify-between text-lg font-bold">
        <span>Total:</span>
        <span class="text-primary-600">₱{{ cartStore.total.toFixed(2) }}</span>
      </div>
    </div>

    <!-- Customer Phone (Optional) -->
    <div class="mt-4">
      <label for="customer-phone" class="block text-sm font-medium text-gray-700 mb-1">
        Customer Phone (Optional)
      </label>
      <input
        id="customer-phone"
        v-model="customerPhone"
        type="tel"
        placeholder="+639123456789"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
      />
      <p class="mt-1 text-xs text-gray-500">Enter phone number to send receipt via SMS</p>
    </div>

    <!-- Payment Method -->
    <div class="mt-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
      <div class="grid grid-cols-2 gap-2">
        <button
          v-for="method in paymentMethods"
          :key="method.value"
          @click="selectedPaymentMethod = method.value"
          :class="[
            'px-4 py-2 rounded-md text-sm font-medium transition-colors',
            selectedPaymentMethod === method.value
              ? 'bg-primary-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
        >
          {{ method.label }}
        </button>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 space-y-2">
      <button
        @click="handleCheckout"
        :disabled="cartStore.items.length === 0 || processing"
        class="w-full bg-primary-600 text-white py-3 px-4 rounded-md font-semibold hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="processing">Processing...</span>
        <span v-else>Complete Sale</span>
      </button>

      <button
        @click="handleClearCart"
        :disabled="cartStore.items.length === 0"
        class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-md font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Clear Cart
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useToastStore } from '@/stores/toast';
import { transactionsApi } from '@/services/api';

const cartStore = useCartStore();
const toastStore = useToastStore();

const customerPhone = ref('');
const selectedPaymentMethod = ref('cash');
const processing = ref(false);
const currentOrderId = ref(null);

const paymentMethods = [
  { value: 'cash', label: 'Cash' },
  { value: 'digital_wallet', label: 'E-Wallet' },
];

const handleCheckout = async () => {
  if (cartStore.items.length === 0) {
    toastStore.error('Cart is empty');
    return;
  }

  processing.value = true;

  try {
    const transactionData = {
      order_id: currentOrderId.value || null,
      customer_phone: customerPhone.value || null,
      total_amount: cartStore.total,
      payment_method: selectedPaymentMethod.value,
      items: cartStore.items.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity,
      })),
    };

    const response = await transactionsApi.create(transactionData);

    toastStore.success('Sale completed successfully!');
    
    // Clear cart and reset form
    cartStore.clearCart();
    customerPhone.value = '';
    selectedPaymentMethod.value = 'cash';
    currentOrderId.value = null;

    // TODO: Optionally print receipt or show receipt modal
    console.log('Transaction completed:', response);

  } catch (error) {
    console.error('Checkout failed:', error);
    const errorMessage = error.response?.data?.message || 'Failed to process sale';
    toastStore.error(errorMessage);
  } finally {
    processing.value = false;
  }
};

const handleClearCart = () => {
  if (confirm('Are you sure you want to clear the cart?')) {
    cartStore.clearCart();
    customerPhone.value = '';
    currentOrderId.value = null;
    toastStore.success('Cart cleared');
  }
};

const setOrderData = (orderData) => {
  if (orderData.customerPhone) {
    customerPhone.value = orderData.customerPhone;
  }
  if (orderData.orderId) {
    currentOrderId.value = orderData.orderId;
  }
};

// Expose method to parent component
defineExpose({
  setOrderData
});
</script>