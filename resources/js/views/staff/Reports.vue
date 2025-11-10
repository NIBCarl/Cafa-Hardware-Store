<template>
  <div class="h-full flex flex-col">
    <header class="bg-white shadow">
      <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold tracking-tight text-gray-900">Reports</h1>
            <div class="flex items-center space-x-4">
            <!-- Date Range Picker -->
            <div class="flex items-center space-x-2">
              <BaseInput
                id="date-start"
                type="date"
                v-model="dateRange.start"
                class="w-40"
              />
              <span class="text-gray-500">to</span>
              <BaseInput
                id="date-end"
                type="date"
                v-model="dateRange.end"
                class="w-40"
              />
            </div>
            <!-- Refresh Button -->
            <BaseButton
              variant="secondary"
              :icon="ArrowPathIcon"
              @click="refreshReports"
              :loading="isRefreshing"
            >
              Refresh
            </BaseButton>
            <!-- Export Format Selector -->
            <select
              v-model="exportFormat"
              class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
              <option value="csv">CSV Format</option>
              <option value="xlsx">Excel Format</option>
            </select>
            <!-- Export Button -->
            <BaseButton
              variant="secondary"
              :icon="ArrowDownTrayIcon"
              @click="exportReport"
              :loading="isExporting"
            >
              Export
            </BaseButton>
          </div>
        </div>
      </div>
    </header>

    <main class="flex-1 p-4 lg:p-8 space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <StatsCard
          title="Total Sales"
          :value="stats.totalSales"
          format="currency"
          :trend="stats.salesTrend"
          :icon="CurrencyDollarIcon"
          subtitle="Total sales for the period"
        />
        <StatsCard
          title="Total Transactions"
          :value="stats.totalTransactions"
          format="number"
          :trend="stats.transactionsTrend"
          :icon="ShoppingCartIcon"
          subtitle="Number of transactions"
        />
        <StatsCard
          title="Average Transaction Value"
          :value="stats.averageTransactionValue"
          format="currency"
          :trend="stats.avgTransactionTrend"
          :icon="CalculatorIcon"
          subtitle="Average value per transaction"
        />
        <StatsCard
          title="Low Stock Items"
          :value="stats.lowStockItems"
          format="number"
          :icon="ExclamationTriangleIcon"
          :link="'/inventory?filter=low-stock'"
          subtitle="Items below threshold"
        />
      </div>

      <!-- Sales Trend Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-medium text-gray-900">Sales Trend</h2>
          <div class="flex items-center space-x-2">
            <select
              v-model="salesTrendInterval"
              class="rounded-md border-gray-300 text-sm"
            >
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
        </div>
        <div class="h-80">
          <LineChart
            :data="salesTrendData"
            :loading="isLoadingSalesTrend"
          />
        </div>
      </div>

      <!-- Top Products and Categories -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Top Products</h2>
          <div class="h-80">
            <BarChart
              :data="topProductsData"
              :loading="isLoadingTopProducts"
            />
          </div>
        </div>

        <!-- Top Categories -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Sales by Category</h2>
          <div class="h-80">
            <BarChart
              :data="categoryData"
              :loading="isLoadingCategories"
            />
          </div>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Transactions</h2>
          <BaseTable
            title="Recent Transactions"
            :columns="transactionColumns"
            :items="recentTransactions"
            :loading="isLoadingTransactions"
            :show-pagination="true"
            :current-page="transactionPage"
            :total-items="totalTransactions"
            :per-page="10"
            @page-change="handleTransactionPageChange"
            @search="handleTransactionSearch"
          >
            <template #filters>
              <select
                v-model="transactionStatus"
                @change="handleTransactionSearch('')"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
              >
                <option value="all">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="refunded">Refunded</option>
              </select>
            </template>
            <template #payment_method="{ value }">
              <span class="text-sm text-gray-700 capitalize">
                {{ value.replace(/_/g, ' ') }}
              </span>
            </template>
            <template #status="{ value }">
              <span
                :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  value === 'completed' ? 'bg-green-100 text-green-800' :
                  value === 'refunded' ? 'bg-red-100 text-red-800' :
                  'bg-yellow-100 text-yellow-800'
                ]"
              >
                {{ value.charAt(0).toUpperCase() + value.slice(1) }}
              </span>
            </template>
            <template #actions="slotProps">
              <button
                @click="viewTransactionDetails(slotProps.item)"
                class="text-primary-600 hover:text-primary-900 font-medium text-sm"
              >
                View
              </button>
            </template>
          </BaseTable>
        </div>
      </div>
    </main>

    <!-- Transaction Details Modal -->
    <BaseModal
      v-model="showTransactionDetails"
      title="Transaction Details"
      size="lg"
      @close="closeTransactionDetails"
    >
      <div v-if="isLoadingDetails" class="flex items-center justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>
      <div v-else-if="selectedTransaction" class="space-y-4">
        <!-- Transaction Info -->
        <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-200">
          <div>
            <p class="text-sm font-medium text-gray-500">Transaction ID</p>
            <p class="mt-1 text-sm text-gray-900">#{{ selectedTransaction.id }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Date</p>
            <p class="mt-1 text-sm text-gray-900">{{ new Date(selectedTransaction.created_at).toLocaleString() }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Customer Phone</p>
            <p class="mt-1 text-sm text-gray-900">{{ selectedTransaction.customer_phone || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Payment Method</p>
            <p class="mt-1 text-sm text-gray-900 capitalize">{{ selectedTransaction.payment_method.replace(/_/g, ' ') }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Status</p>
            <p class="mt-1">
              <span
                :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  selectedTransaction.status === 'completed' ? 'bg-green-100 text-green-800' :
                  selectedTransaction.status === 'refunded' ? 'bg-red-100 text-red-800' :
                  'bg-yellow-100 text-yellow-800'
                ]"
              >
                {{ selectedTransaction.status.charAt(0).toUpperCase() + selectedTransaction.status.slice(1) }}
              </span>
            </p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Staff</p>
            <p class="mt-1 text-sm text-gray-900">{{ selectedTransaction.staff?.name || 'N/A' }}</p>
          </div>
        </div>

        <!-- Items -->
        <div>
          <h4 class="text-sm font-medium text-gray-900 mb-3">Items</h4>
          <div class="space-y-2">
            <div
              v-for="item in selectedTransaction.items"
              :key="item.id"
              class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg"
            >
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ item.product?.name || 'Unknown Product' }}</p>
                <p class="text-xs text-gray-500">Quantity: {{ item.quantity }} × ₱{{ Number(item.price).toFixed(2) }}</p>
              </div>
              <p class="text-sm font-semibold text-gray-900">₱{{ Number(item.subtotal).toFixed(2) }}</p>
            </div>
          </div>
        </div>

        <!-- Total -->
        <div class="pt-4 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <p class="text-base font-semibold text-gray-900">Total Amount</p>
            <p class="text-lg font-bold text-primary-600">₱{{ Number(selectedTransaction.total_amount).toFixed(2) }}</p>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="selectedTransaction.notes" class="pt-4 border-t border-gray-200">
          <p class="text-sm font-medium text-gray-500">Notes</p>
          <p class="mt-1 text-sm text-gray-900">{{ selectedTransaction.notes }}</p>
        </div>
      </div>

      <template #footer>
        <BaseButton variant="secondary" @click="closeTransactionDetails">
          Close
        </BaseButton>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import {
  CurrencyDollarIcon,
  ShoppingCartIcon,
  CalculatorIcon,
  ExclamationTriangleIcon,
  ArrowDownTrayIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline';
import { useToastStore } from '../../stores/toast';
import apiClient from '../../services/api/client';
import LineChart from '../../components/charts/LineChart.vue';
import BarChart from '../../components/charts/BarChart.vue';
import StatsCard from '../../components/reports/StatsCard.vue';
import BaseInput from '../../components/base/BaseInput.vue';
import BaseButton from '../../components/base/BaseButton.vue';
import BaseTable from '../../components/base/BaseTable.vue';
import BaseModal from '../../components/base/BaseModal.vue';

const toastStore = useToastStore();

// State
const dateRange = reactive({
  start: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0]
});

const salesTrendInterval = ref('daily');
const exportFormat = ref('csv');
const isExporting = ref(false);
const isRefreshing = ref(false);
const isLoadingSalesTrend = ref(false);
const isLoadingTopProducts = ref(false);
const isLoadingCategories = ref(false);
const isLoadingTransactions = ref(false);

const stats = reactive({
  totalSales: 0,
  salesTrend: 0,
  totalTransactions: 0,
  transactionsTrend: 0,
  averageTransactionValue: 0,
  avgTransactionTrend: 0,
  lowStockItems: 0
});

const salesTrendData = ref({
  labels: [],
  datasets: [
    {
      label: 'Sales',
      data: [],
      borderColor: '#4F46E5',
      tension: 0.4
    }
  ]
});

const topProductsData = ref({
  labels: [],
  datasets: [
    {
      label: 'Sales Amount',
      data: [],
      backgroundColor: '#4F46E5'
    }
  ]
});

const categoryData = ref({
  labels: [],
  datasets: [
    {
      label: 'Sales Amount',
      data: [],
      backgroundColor: '#4F46E5'
    }
  ]
});

const transactionColumns = [
  { key: 'id', label: 'ID' },
  { key: 'created_at', label: 'Date', format: 'datetime' },
  { key: 'total_amount', label: 'Amount', format: 'currency' },
  { key: 'payment_method', label: 'Payment Method' },
  { key: 'status', label: 'Status' }
];

const recentTransactions = ref([]);
const transactionPage = ref(1);
const totalTransactions = ref(0);
const transactionSearch = ref('');
const transactionStatus = ref('all');
const showTransactionDetails = ref(false);
const selectedTransaction = ref(null);
const isLoadingDetails = ref(false);

// Methods
const loadStats = async () => {
  try {
    const response = await apiClient.get(`/reports/stats`, {
      params: {
        start: dateRange.start,
        end: dateRange.end
      }
    });
    Object.assign(stats, response.data);
  } catch (error) {
    console.error('Failed to load statistics:', error);
    toastStore.error('Failed to load statistics');
  }
};

const loadSalesTrend = async () => {
  try {
    isLoadingSalesTrend.value = true;
    const response = await apiClient.get(`/reports/sales-trend`, {
      params: {
        start: dateRange.start,
        end: dateRange.end,
        interval: salesTrendInterval.value
      }
    });
    const data = response.data;
    // Deep clone to completely remove Vue reactivity for Chart.js
    salesTrendData.value = JSON.parse(JSON.stringify({
      labels: data.labels || [],
      datasets: [{
        label: 'Sales',
        data: data.values || [],
        borderColor: '#4F46E5',
        tension: 0.4
      }]
    }));
  } catch (error) {
    console.error('Failed to load sales trend:', error);
    toastStore.error('Failed to load sales trend');
  } finally {
    isLoadingSalesTrend.value = false;
  }
};

const loadTopProducts = async () => {
  try {
    isLoadingTopProducts.value = true;
    const response = await apiClient.get(`/reports/top-products`, {
      params: {
        start: dateRange.start,
        end: dateRange.end
      }
    });
    const data = response.data;
    // Deep clone to completely remove Vue reactivity for Chart.js
    topProductsData.value = JSON.parse(JSON.stringify({
      labels: data.labels || [],
      datasets: [{
        label: 'Sales Amount',
        data: data.values || [],
        backgroundColor: '#4F46E5'
      }]
    }));
  } catch (error) {
    console.error('Failed to load top products:', error);
    toastStore.error('Failed to load top products');
  } finally {
    isLoadingTopProducts.value = false;
  }
};

const loadCategoryData = async () => {
  try {
    isLoadingCategories.value = true;
    const response = await apiClient.get(`/reports/category-sales`, {
      params: {
        start: dateRange.start,
        end: dateRange.end
      }
    });
    const data = response.data;
    // Deep clone to completely remove Vue reactivity for Chart.js
    categoryData.value = JSON.parse(JSON.stringify({
      labels: data.labels || [],
      datasets: [{
        label: 'Sales Amount',
        data: data.values || [],
        backgroundColor: '#4F46E5'
      }]
    }));
  } catch (error) {
    console.error('Failed to load category data:', error);
    toastStore.error('Failed to load category data');
  } finally {
    isLoadingCategories.value = false;
  }
};

const loadTransactions = async (page = 1) => {
  try {
    isLoadingTransactions.value = true;
    const response = await apiClient.get(`/reports/transactions`, {
      params: {
        page,
        start: dateRange.start,
        end: dateRange.end,
        customer_phone: transactionSearch.value || undefined,
        status: transactionStatus.value !== 'all' ? transactionStatus.value : undefined
      }
    });
    const data = response.data;
    recentTransactions.value = data.data || [];
    totalTransactions.value = data.total || 0;
  } catch (error) {
    console.error('Failed to load transactions:', error);
    toastStore.error('Failed to load transactions');
  } finally {
    isLoadingTransactions.value = false;
  }
};

const handleTransactionSearch = (query) => {
  transactionSearch.value = query;
  transactionPage.value = 1; // Reset to first page on search
  loadTransactions(1);
};

const handleTransactionPageChange = (page) => {
  transactionPage.value = page;
  loadTransactions(page);
};

const viewTransactionDetails = async (transaction) => {
  if (!transaction || !transaction.id) {
    console.error('Invalid transaction:', transaction);
    toastStore.error('Invalid transaction data');
    return;
  }
  
  try {
    isLoadingDetails.value = true;
    showTransactionDetails.value = true;
    const response = await apiClient.get(`/transactions/${transaction.id}`);
    selectedTransaction.value = response.data;
  } catch (error) {
    console.error('Failed to load transaction details:', error);
    toastStore.error('Failed to load transaction details');
    showTransactionDetails.value = false;
  } finally {
    isLoadingDetails.value = false;
  }
};

const closeTransactionDetails = () => {
  showTransactionDetails.value = false;
  selectedTransaction.value = null;
};

const refreshReports = async () => {
  try {
    isRefreshing.value = true;
    await Promise.all([
      loadStats(),
      loadSalesTrend(),
      loadTopProducts(),
      loadCategoryData(),
      loadTransactions(transactionPage.value)
    ]);
    toastStore.success('Reports refreshed successfully');
  } catch (error) {
    console.error('Failed to refresh reports:', error);
    toastStore.error('Failed to refresh reports');
  } finally {
    isRefreshing.value = false;
  }
};

const exportReport = async () => {
  try {
    isExporting.value = true;
    const response = await apiClient.get(`/reports/export`, {
      params: {
        start: dateRange.start,
        end: dateRange.end,
        format: exportFormat.value
      },
      responseType: 'blob'
    });
    
    // Determine MIME type and file extension based on format
    const mimeType = exportFormat.value === 'xlsx' 
      ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      : 'text/csv';
    const fileExtension = exportFormat.value === 'xlsx' ? 'xlsx' : 'csv';
    
    const blob = new Blob([response.data], { type: mimeType });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `transactions-report-${dateRange.start}-to-${dateRange.end}.${fileExtension}`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
    toastStore.success(`Report exported successfully as ${exportFormat.value.toUpperCase()}`);
  } catch (error) {
    console.error('Failed to export report:', error);
    // Handle validation errors from backend
    if (error.response?.status === 422) {
      const message = error.response.data?.message || 'Invalid export parameters';
      toastStore.error(message);
    } else {
      toastStore.error('Failed to export report');
    }
  } finally {
    isExporting.value = false;
  }
};

// Watchers
watch([() => dateRange.start, () => dateRange.end], () => {
  loadStats();
  loadSalesTrend();
  loadTopProducts();
  loadCategoryData();
  loadTransactions(1);
});

watch(salesTrendInterval, () => {
  loadSalesTrend();
});

// Lifecycle
onMounted(() => {
  loadStats();
  loadSalesTrend();
  loadTopProducts();
  loadCategoryData();
  loadTransactions(1);
});
</script>
