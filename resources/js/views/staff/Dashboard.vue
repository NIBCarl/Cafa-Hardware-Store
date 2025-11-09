<template>
  <div class="space-y-6 p-6 bg-gradient-to-br from-gray-50 via-white to-gray-50 min-h-screen">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
      <p class="mt-1 text-sm text-gray-600">Welcome back! Here's what's happening today.</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Today's Sales Card -->
      <div class="bg-white overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 rounded-xl border border-gray-100 transform hover:-translate-y-1">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-gradient-to-br from-green-50 to-green-100 p-3 rounded-lg">
              <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Today's Sales</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">
                    ₱{{ stats.todaySales?.toFixed(2) || '0.00' }}
                  </div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3 shadow-inner">
          <div class="text-sm">
            <span class="font-medium text-green-600">{{ stats.todayTransactions || 0 }}</span>
            <span class="text-gray-500"> transactions</span>
          </div>
        </div>
      </div>

      <!-- Total Products Card -->
      <div class="bg-white overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 rounded-xl border border-gray-100 transform hover:-translate-y-1">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-gradient-to-br from-blue-50 to-blue-100 p-3 rounded-lg">
              <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Products</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">
                    {{ stats.totalProducts || 0 }}
                  </div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3 shadow-inner">
          <div class="text-sm">
            <span class="font-medium text-green-600">{{ stats.activeProducts || 0 }}</span>
            <span class="text-gray-500"> active</span>
          </div>
        </div>
      </div>

      <!-- Low Stock Items Card -->
      <div class="bg-white overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 rounded-xl border border-gray-100 transform hover:-translate-y-1">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-gradient-to-br from-yellow-50 to-yellow-100 p-3 rounded-lg">
              <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Low Stock Items</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">
                    {{ stats.lowStockCount || 0 }}
                  </div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3 shadow-inner">
          <router-link :to="{ name: 'Inventory' }" class="text-sm font-medium text-yellow-600 hover:text-yellow-700 transition-colors">
            View details →
          </router-link>
        </div>
      </div>

      <!-- Inventory Value Card -->
      <div class="bg-white overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 rounded-xl border border-gray-100 transform hover:-translate-y-1">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-gradient-to-br from-purple-50 to-purple-100 p-3 rounded-lg">
              <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Inventory Value</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">
                    ₱{{ (stats.inventoryValue || 0).toFixed(0) }}
                  </div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3 shadow-inner">
          <div class="text-sm text-gray-500">
            Retail value
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Transactions & Low Stock Alert -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
      <!-- Recent Transactions -->
      <div class="bg-white shadow-lg rounded-xl border border-gray-100 overflow-hidden">
        <div class="px-4 py-5 sm:p-6 bg-gradient-to-br from-white to-gray-50">
          <h3 class="text-lg leading-6 font-semibold text-gray-900 mb-4">Recent Transactions</h3>
          
          <div v-if="loadingTransactions" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
          </div>
          
          <div v-else-if="recentTransactions.length === 0" class="text-center py-8 text-gray-500">
            No recent transactions
          </div>
          
          <div v-else class="bg-gray-50 rounded-lg shadow-inner p-3">
            <ul class="space-y-1">
              <li v-for="transaction in recentTransactions" :key="transaction.id" class="p-3 hover:bg-white hover:shadow-sm rounded-lg transition-all duration-200">
                <div class="flex items-center space-x-4">
                  <div class="flex-shrink-0 bg-gradient-to-br from-primary-50 to-primary-100 p-2 rounded-lg">
                    <svg class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                      Transaction #{{ String(transaction.id).padStart(6, '0') }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ new Date(transaction.created_at).toLocaleString() }}
                    </p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900">
                      ₱{{ Number(transaction.total_amount).toFixed(2) }}
                    </p>
                    <p class="text-xs text-gray-500 capitalize">
                      {{ transaction.payment_method }}
                    </p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Low Stock Alert -->
      <div class="bg-white shadow-lg rounded-xl border border-gray-100 overflow-hidden">
        <div class="px-4 py-5 sm:p-6 bg-gradient-to-br from-white to-gray-50">
          <h3 class="text-lg leading-6 font-semibold text-gray-900 mb-4">Low Stock Alerts</h3>
          
          <div v-if="loadingLowStock" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
          </div>
          
          <div v-else-if="lowStockProducts.length === 0" class="text-center py-8 text-gray-500">
            All products are well stocked
          </div>
          
          <div v-else class="bg-gray-50 rounded-lg shadow-inner p-3">
            <ul class="space-y-1">
              <li v-for="product in lowStockProducts" :key="product.id" class="p-3 hover:bg-white hover:shadow-sm rounded-lg transition-all duration-200">
                <div class="flex items-center space-x-4">
                  <div class="flex-shrink-0 bg-gradient-to-br from-yellow-50 to-yellow-100 p-2 rounded-lg">
                    <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                      {{ product.name }}
                    </p>
                    <p class="text-xs text-gray-500">
                      SKU: {{ product.sku }}
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 shadow-sm">
                      {{ product.stock_quantity }} left
                    </span>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-100 overflow-hidden">
      <div class="px-4 py-5 sm:p-6 bg-gradient-to-br from-white to-gray-50">
        <h3 class="text-lg leading-6 font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <router-link
            :to="{ name: 'POS' }"
            class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:scale-105 hover:border-primary-400 transition-all duration-300"
          >
            <div class="bg-gradient-to-br from-primary-50 to-primary-100 p-3 rounded-lg mb-3">
              <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <span class="text-sm font-medium text-gray-900">New Sale</span>
          </router-link>

          <router-link
            :to="{ name: 'Inventory' }"
            class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:scale-105 hover:border-blue-400 transition-all duration-300"
          >
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-3 rounded-lg mb-3">
              <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
            </div>
            <span class="text-sm font-medium text-gray-900">Manage Inventory</span>
          </router-link>

          <router-link
            :to="{ name: 'Reports' }"
            class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:scale-105 hover:border-purple-400 transition-all duration-300"
          >
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-3 rounded-lg mb-3">
              <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <span class="text-sm font-medium text-gray-900">View Reports</span>
          </router-link>

          <router-link
            :to="{ name: 'Settings' }"
            class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:scale-105 hover:border-gray-400 transition-all duration-300"
          >
            <div class="bg-gradient-to-br from-gray-100 to-gray-200 p-3 rounded-lg mb-3">
              <svg class="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <span class="text-sm font-medium text-gray-900">Settings</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { productsApi, transactionsApi } from '@/services/api';
import { useDashboardEvents, useInventoryEvents } from '@/composables/useRealtime';
import { useToastStore } from '@/stores/toast';

const toastStore = useToastStore();

const stats = ref({
  todaySales: 0,
  todayTransactions: 0,
  totalProducts: 0,
  activeProducts: 0,
  lowStockCount: 0,
  inventoryValue: 0,
});

const recentTransactions = ref([]);
const lowStockProducts = ref([]);
const loadingTransactions = ref(false);
const loadingLowStock = ref(false);

const fetchDashboardData = async () => {
  try {
    // Fetch transactions from today
    const today = new Date().toISOString().split('T')[0];
    const transactionsResponse = await transactionsApi.list({
      date_from: today,
      date_to: today,
      per_page: 5,
    });

    const transactionsData = transactionsResponse.data?.data || transactionsResponse.data || [];
    recentTransactions.value = transactionsData;
    stats.value.todayTransactions = transactionsResponse.data?.total || transactionsData.length;
    stats.value.todaySales = transactionsData.reduce((sum, t) => sum + Number(t.total_amount), 0);

    // Fetch all products for stats
    const productsResponse = await productsApi.list({ per_page: 1000 });
    const allProducts = productsResponse.data?.data || productsResponse.data || [];
    
    stats.value.totalProducts = allProducts.length;
    stats.value.activeProducts = allProducts.filter(p => p.is_active).length;
    stats.value.lowStockCount = allProducts.filter(p => p.stock_quantity <= p.low_stock_threshold).length;
    stats.value.inventoryValue = allProducts.reduce((sum, p) => sum + (p.stock_quantity * p.price), 0);

    // Fetch low stock products
    const lowStockResponse = await productsApi.getLowStock();
    const lowStockData = lowStockResponse.data || [];
    lowStockProducts.value = (Array.isArray(lowStockData) ? lowStockData : []).slice(0, 5);

  } catch (error) {
    console.error('Failed to fetch dashboard data:', error);
  }
};

// Real-time event listeners
useDashboardEvents({
  onTransaction: (event) => {
    // Add new transaction to the top of the list
    recentTransactions.value.unshift(event.transaction);
    if (recentTransactions.value.length > 5) {
      recentTransactions.value.pop();
    }
    
    // Update stats
    stats.value.todaySales += Number(event.transaction.total_amount);
    stats.value.todayTransactions++;
    
    // Show notification
    toastStore.success(event.message || 'New transaction completed');
  },
});

useInventoryEvents({
  onLowStock: (event) => {
    // Add to low stock list
    const exists = lowStockProducts.value.some(p => p.id === event.product.id);
    if (!exists) {
      lowStockProducts.value.unshift(event.product);
      if (lowStockProducts.value.length > 5) {
        lowStockProducts.value.pop();
      }
      stats.value.lowStockCount++;
    }
    
    // Show alert
    toastStore.warning(event.message || `Low stock alert: ${event.product.name}`);
  },
  onInventoryUpdate: (event) => {
    // Update product in low stock list if it exists
    const index = lowStockProducts.value.findIndex(p => p.id === event.product.id);
    if (index !== -1) {
      if (event.product.is_low_stock) {
        lowStockProducts.value[index] = event.product;
      } else {
        // Remove from low stock list if no longer low
        lowStockProducts.value.splice(index, 1);
        stats.value.lowStockCount = Math.max(0, stats.value.lowStockCount - 1);
      }
    }
  },
});

onMounted(() => {
  fetchDashboardData();
});
</script>