<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Left section - Mobile menu button + Page title -->
        <div class="flex items-center space-x-4">
          <!-- Mobile menu button -->
          <button
            type="button"
            class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 transition-colors"
            @click="$emit('toggle-sidebar')"
          >
            <span class="sr-only">Open sidebar</span>
            <!-- Hamburger Icon -->
            <svg
              class="h-6 w-6"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              aria-hidden="true"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <h2 class="text-xl font-semibold text-gray-800">{{ pageTitle }}</h2>
        </div>

        <!-- Right section - User menu -->
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-600 hidden sm:inline">{{ user?.name || 'Loading...' }}</span>
          <button
            @click="handleLogout"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Logout
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const toastStore = useToastStore();

const user = computed(() => authStore.user);
const pageTitle = computed(() => route.name || 'Dashboard');

const handleLogout = async () => {
  try {
    await authStore.logout();
    toastStore.success('Logged out successfully');
    router.push({ name: 'Login' });
  } catch (error) {
    console.error('Logout failed:', error);
    toastStore.error('Failed to logout');
  }
};

// Define emits
defineEmits(['toggle-sidebar']);
</script>