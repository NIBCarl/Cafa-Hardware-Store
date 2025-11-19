<template>
  <div class="flex h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Sidebar -->
    <div class="hidden md:flex md:flex-shrink-0">
      <div class="flex flex-col w-64">
        <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto bg-gradient-to-b from-red-600 to-red-700 border-r border-red-800 shadow-lg">
          <!-- Logo/Header -->
          <div class="flex items-center flex-shrink-0 px-4 mb-2">
            <div class="bg-gradient-to-br from-red-800 to-red-900 p-2 rounded-lg shadow-md">
              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h1 class="ml-3 text-xl font-bold text-white">CAFA Hardware</h1>
          </div>
          
          <!-- Navigation -->
          <nav class="mt-5 flex-1 px-3 space-y-1">
            <router-link
              v-for="item in navigation"
              :key="item.name"
              :to="item.to"
              :class="[
                isActive(item.to)
                  ? 'bg-gradient-to-r from-red-800 to-red-900 text-white shadow-md border-l-4 border-white'
                  : 'text-red-100 hover:bg-gradient-to-r hover:from-red-700 hover:to-red-800 hover:text-white hover:shadow-sm border-l-4 border-transparent',
                'group flex items-center px-3 py-2.5 text-sm font-medium rounded-r-lg transition-all duration-200'
              ]"
            >
              <div :class="[
                isActive(item.to) 
                  ? 'bg-gradient-to-br from-red-900 to-red-950' 
                  : 'bg-red-800 group-hover:bg-red-900',
                'p-2 rounded-lg mr-3 transition-all duration-200'
              ]">
                <component
                  :is="item.icon"
                  :class="[
                    isActive(item.to) ? 'text-white' : 'text-red-200 group-hover:text-white',
                    'flex-shrink-0 h-5 w-5'
                  ]"
                  aria-hidden="true"
                />
              </div>
              <span :class="[isActive(item.to) ? 'font-semibold' : 'font-medium']">
                {{ item.name }}
              </span>
            </router-link>
          </nav>
        </div>
      </div>
    </div>

    <!-- Mobile menu -->
    <Transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isOpen" class="md:hidden">
        <div class="fixed inset-0 flex z-40">
          <div class="fixed inset-0" @click="$emit('close')">
            <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
          </div>
        <div class="relative flex-1 flex flex-col max-w-xs w-full bg-gradient-to-b from-red-600 to-red-700 shadow-2xl">
          <div class="absolute top-0 right-0 -mr-12 pt-2">
            <button
              type="button"
              class="ml-1 flex items-center justify-center h-10 w-10 rounded-full bg-gray-800 bg-opacity-50 hover:bg-opacity-75 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white transition-all"
              @click="$emit('close')"
            >
              <span class="sr-only">Close sidebar</span>
              <svg
                class="h-6 w-6 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
            <!-- Logo/Header -->
            <div class="flex items-center flex-shrink-0 px-4 mb-2">
              <div class="bg-gradient-to-br from-red-800 to-red-900 p-2 rounded-lg shadow-md">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <h1 class="ml-3 text-xl font-bold text-white">CAFA Hardware</h1>
            </div>
            
            <!-- Navigation -->
            <nav class="mt-5 px-3 space-y-1">
              <router-link
                v-for="item in navigation"
                :key="item.name"
                :to="item.to"
                :class="[
                  isActive(item.to)
                    ? 'bg-gradient-to-r from-red-800 to-red-900 text-white shadow-md border-l-4 border-white'
                    : 'text-red-100 hover:bg-gradient-to-r hover:from-red-700 hover:to-red-800 hover:text-white hover:shadow-sm border-l-4 border-transparent',
                  'group flex items-center px-3 py-2.5 text-base font-medium rounded-r-lg transition-all duration-200'
                ]"
                @click="$emit('close')"
              >
                <div :class="[
                  isActive(item.to) 
                    ? 'bg-gradient-to-br from-red-900 to-red-950' 
                    : 'bg-red-800 group-hover:bg-red-900',
                  'p-2 rounded-lg mr-3 transition-all duration-200'
                ]">
                  <component
                    :is="item.icon"
                    :class="[
                      isActive(item.to) ? 'text-white' : 'text-red-200 group-hover:text-white',
                      'flex-shrink-0 h-6 w-6'
                    ]"
                    aria-hidden="true"
                  />
                </div>
                <span :class="[isActive(item.to) ? 'font-semibold' : 'font-medium']">
                  {{ item.name }}
                </span>
              </router-link>
            </nav>
          </div>
        </div>
        <div class="flex-shrink-0 w-14"></div>
      </div>
    </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import {
  HomeIcon,
  ShoppingCartIcon,
  CubeIcon,
  ClipboardDocumentListIcon,
  ChartBarIcon,
  CogIcon,
  UsersIcon
} from '@heroicons/vue/24/outline';
import { useAuthStore } from '@/stores/auth';

// Props
defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
});

// Emits
defineEmits(['close']);

const route = useRoute();
const authStore = useAuthStore();

const navigation = computed(() => {
  const role = authStore.user?.role;
  
  // Base items for all staff (including cashier)
  const items = [
    { name: 'Dashboard', to: '/staff/dashboard', icon: HomeIcon },
    { name: 'POS', to: '/staff/pos', icon: ShoppingCartIcon },
  ];

  // Inventory and Reports for Admin only
  if (role === 'admin') {
    items.push({ name: 'Inventory', to: '/staff/inventory', icon: CubeIcon });
    items.push({ name: 'Reports', to: '/staff/reports', icon: ChartBarIcon });
  }

  // Orders for all staff
  items.push({ name: 'Orders', to: '/staff/orders', icon: ClipboardDocumentListIcon });
  
  // Settings for all staff (internal components restricted by role)
  items.push({ name: 'Settings', to: '/staff/settings', icon: CogIcon });

  // Users menu for admin only
  if (role === 'admin') {
    items.push({ name: 'Users', to: '/staff/users', icon: UsersIcon });
  }

  return items;
});

const isActive = (path) => {
  return route.path === path;
};
</script>
