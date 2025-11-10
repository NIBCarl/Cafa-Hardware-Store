<template>
  <div id="app">
    <!-- Router view -->
    <router-view />

    <!-- Toast notifications -->
    <div class="fixed bottom-4 right-4 z-50 space-y-2">
      <transition-group name="list">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'p-4 rounded-lg shadow-lg max-w-md',
            toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
          ]"
          class="text-white"
        >
          {{ toast.message }}
        </div>
      </transition-group>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useToastStore } from './stores/toast';
import { useCustomerAuthStore } from './stores/customerAuth';

const toastStore = useToastStore();
const toasts = computed(() => toastStore.toasts);

// Initialize customer profile on app load if token exists
const customerAuthStore = useCustomerAuthStore();

onMounted(async () => {
  // If token exists but customer data not loaded, fetch profile
  if (customerAuthStore.token && !customerAuthStore.customer) {
    try {
      await customerAuthStore.fetchProfile();
    } catch (error) {
      // Token expired/invalid, logout silently
      console.error('Failed to restore customer session:', error);
      await customerAuthStore.logout();
    }
  }
});
</script>

<style>
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.list-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>