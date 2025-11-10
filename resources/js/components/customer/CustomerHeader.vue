<template>
  <header class="bg-gradient-to-r from-white via-gray-50 to-white shadow-lg sticky top-0 z-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <!-- Logo -->
        <router-link to="/customer/shop" class="flex items-center group">
          <div class="bg-gradient-to-br from-primary-500 to-primary-600 p-2 rounded-lg shadow-md group-hover:shadow-lg transition-all">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="ml-3">
            <span class="text-4xl font-bold bg-gradient-to-r from-primary-600 to-primary-700 bg-clip-text text-transparent">CAFA</span>
            <span class="ml-2 text-xl text-gray-600 font-semibold">Hardware Store</span>
          </div>
        </router-link>

        <!-- Navigation -->
        <nav class="hidden md:flex space-x-2">
          <router-link
            to="/customer/shop"
            class="px-4 py-2 text-gray-700 hover:text-primary-600 font-medium rounded-lg hover:bg-gradient-to-r hover:from-primary-50 hover:to-primary-100 transition-all"
            active-class="text-primary-700 bg-gradient-to-r from-primary-50 to-primary-100 shadow-sm"
          >
            Shop
          </router-link>
          <router-link
            to="/customer/orders"
            class="px-4 py-2 text-gray-700 hover:text-primary-600 font-medium rounded-lg hover:bg-gradient-to-r hover:from-primary-50 hover:to-primary-100 transition-all"
            active-class="text-primary-700 bg-gradient-to-r from-primary-50 to-primary-100 shadow-sm"
          >
            My Orders
          </router-link>
          <router-link
            to="/customer/profile"
            class="px-4 py-2 text-gray-700 hover:text-primary-600 font-medium rounded-lg hover:bg-gradient-to-r hover:from-primary-50 hover:to-primary-100 transition-all"
            active-class="text-primary-700 bg-gradient-to-r from-primary-50 to-primary-100 shadow-sm"
          >
            Profile
          </router-link>
        </nav>

        <!-- Cart and User Menu -->
        <div class="flex items-center space-x-3">
          <!-- Cart -->
          <router-link
            to="/customer/cart"
            class="relative p-2.5 text-gray-700 hover:text-primary-600 bg-gray-100 hover:bg-gradient-to-br hover:from-primary-50 hover:to-primary-100 rounded-lg transition-all shadow-sm hover:shadow-md"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span
              v-if="cartStore.itemCount > 0"
              class="absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold shadow-md"
            >
              {{ cartStore.itemCount }}
            </span>
          </router-link>

          <!-- User Menu -->
          <div v-if="authStore.isAuthenticated" class="relative">
            <!-- Loading State - Profile data is being fetched -->
            <div v-if="!authStore.isProfileLoaded" class="flex items-center space-x-2 bg-gray-100 px-3 py-2 rounded-lg shadow-sm">
              <div class="h-8 w-8 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 animate-pulse"></div>
              <div class="h-4 w-4 bg-gray-300 rounded animate-pulse"></div>
            </div>
            
            <!-- Loaded State - Show user menu -->
            <button
              v-else
              @click.stop="toggleMenu"
              class="flex items-center space-x-2 text-gray-700 hover:text-primary-600 focus:outline-none bg-gray-100 hover:bg-gradient-to-br hover:from-primary-50 hover:to-primary-100 px-3 py-2 rounded-lg transition-all shadow-sm hover:shadow-md"
              title="Account menu"
              type="button"
            >
              <div class="h-8 w-8 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center border-2 border-primary-300 shadow-sm">
                <span class="text-sm font-semibold text-primary-700">
                  {{ authStore.customer?.name?.charAt(0).toUpperCase() }}
                </span>
              </div>
              <!-- Dropdown indicator -->
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <!-- Dropdown Menu - Only show when profile is loaded -->
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="showUserMenu && authStore.isProfileLoaded"
                v-click-outside="() => showUserMenu = false"
                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl py-2 z-50 border border-gray-200 overflow-hidden"
              >
              <div class="px-4 py-3 bg-gradient-to-r from-primary-50 to-primary-100 border-b border-primary-200">
                <p class="text-sm font-semibold text-gray-900">{{ authStore.customer?.name }}</p>
                <p class="text-xs text-gray-600 truncate">{{ authStore.customer?.email }}</p>
              </div>
              <router-link
                to="/customer/profile"
                class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-primary-50 hover:to-primary-100 hover:text-primary-700 transition-all"
                @click="showUserMenu = false"
              >
                <div class="flex items-center">
                  <div class="bg-gray-100 p-1.5 rounded-lg mr-3">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </div>
                  <span class="font-medium">My Profile</span>
                </div>
              </router-link>
              <router-link
                to="/customer/orders"
                class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-primary-50 hover:to-primary-100 hover:text-primary-700 transition-all"
                @click="showUserMenu = false"
              >
                <div class="flex items-center">
                  <div class="bg-gray-100 p-1.5 rounded-lg mr-3">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </div>
                  <span class="font-medium">My Orders</span>
                </div>
              </router-link>
              <hr class="my-1 border-gray-200">
              <button
                @click="handleLogout"
                class="block w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-semibold transition-all"
              >
                <div class="flex items-center">
                  <div class="bg-red-50 p-1.5 rounded-lg mr-3">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                  </div>
                  <span>Logout</span>
                </div>
              </button>
              </div>
            </transition>
          </div>

          <router-link
            v-else
            to="/customer/login"
            class="text-sm font-medium text-primary-600 hover:text-primary-700"
          >
            Login
          </router-link>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useCustomerAuthStore } from '@/stores/customerAuth';
import { useCustomerCartStore } from '@/stores/customerCart';
import { useToastStore } from '@/stores/toast';

const router = useRouter();
const authStore = useCustomerAuthStore();
const cartStore = useCustomerCartStore();
const toastStore = useToastStore();

const showUserMenu = ref(false);

const toggleMenu = () => {
  console.log('Toggle menu clicked. Current state:', showUserMenu.value);
  showUserMenu.value = !showUserMenu.value;
  console.log('New state:', showUserMenu.value);
};

const handleLogout = async () => {
  console.log('Logout clicked');
  showUserMenu.value = false;
  await authStore.logout();
  toastStore.success('Logged out successfully');
  router.push('/customer/login');
};

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value();
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  },
};
</script>

