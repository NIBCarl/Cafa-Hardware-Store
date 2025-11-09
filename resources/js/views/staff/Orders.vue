<template>
  <div class="orders-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Customer Orders</h1>
        <p class="page-description">View and manage orders placed by customers through the customer portal</p>
      </div>
      <div class="header-info">
        <div class="info-badge">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Customers create orders. Staff manages them here.</span>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <StatsCard
        v-for="stat in stats"
        :key="stat.title"
        v-bind="stat"
      />
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="filter-group">
        <BaseInput
          id="search-orders"
          v-model="filters.search"
          placeholder="Search by order number or customer name..."
          type="text"
          @input="debouncedSearch"
        />

        <select v-model="filters.status" class="filter-select" @change="loadOrders">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="ready">Ready</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <BaseInput
          id="start-date"
          v-model="filters.start_date"
          type="date"
          placeholder="Start Date"
          @change="loadOrders"
        />

        <BaseInput
          id="end-date"
          v-model="filters.end_date"
          type="date"
          placeholder="End Date"
          @change="loadOrders"
        />

        <BaseButton
          variant="secondary"
          @click="resetFilters"
        >
          Clear Filters
        </BaseButton>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && orders.length === 0" class="empty-state">
      <div class="empty-state-content">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="empty-title">No Customer Orders Yet</h3>
        <p class="empty-description">
          Customer orders will appear here once customers place orders through the customer portal.
        </p>
        <div class="empty-actions">
          <div class="info-card">
            <h4 class="info-title">How it works:</h4>
            <ol class="info-list">
              <li>Customers browse products on the customer portal</li>
              <li>They add items to cart and place an order</li>
              <li>Orders appear here for staff to manage</li>
              <li>You can update order status and track fulfillment</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <BaseTable
      v-else
      :items="orders"
      :columns="columns"
      :loading="loading"
      :current-page="currentPage"
      :total-items="totalOrders"
      :per-page="perPage"
      title="Customer Orders"
      @page-change="handlePageChange"
    >
      <template #order_number="{ item }">
        <div class="order-number">
          <span class="order-id">{{ item.order_number }}</span>
        </div>
      </template>

      <template #customer="{ item }">
        <div class="customer-info">
          <div class="customer-name">{{ getCustomerName(item.customer) }}</div>
          <div class="customer-contact">{{ getCustomerPhone(item.customer) }}</div>
        </div>
      </template>

      <template #items_count="{ item }">
        <div class="items-count">
          <span class="count-badge">{{ getItemsCount(item.items) }}</span>
        </div>
      </template>

      <template #total_amount="{ item }">
        <div class="amount">
          <span class="amount-value">₱{{ formatCurrency(item.total_amount) }}</span>
        </div>
      </template>

      <template #payment_method="{ item }">
        <div class="payment-method">
          <span :class="getPaymentMethodClass(item.payment_method)">
            {{ getPaymentMethodLabel(item.payment_method) }}
          </span>
        </div>
      </template>

      <template #status="{ item }">
        <span :class="getStatusClass(item.status)">
          {{ getStatusLabel(item.status) }}
        </span>
      </template>

      <template #created_at="{ item }">
        <div class="date-info">
          <div class="date-primary">{{ formatDate(item.created_at) }}</div>
          <div class="date-time">{{ formatTime(item.created_at) }}</div>
        </div>
      </template>

      <template #actions="{ item }">
        <div v-if="item" class="action-buttons">
          <button
            @click="viewOrder(item)"
            class="action-btn action-btn-view"
            title="View Details"
            type="button"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span class="action-label">View</span>
          </button>
          <button
            v-if="item?.status && item.status !== 'completed' && item.status !== 'cancelled'"
            @click="showStatusModal(item)"
            class="action-btn action-btn-edit"
            title="Update Status"
            type="button"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span class="action-label">Update</span>
          </button>
          <button
            v-if="item?.status && item.status !== 'completed' && item.status !== 'cancelled'"
            @click="confirmCancel(item)"
            class="action-btn action-btn-delete"
            title="Cancel Order"
            type="button"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span class="action-label">Cancel</span>
          </button>
        </div>
      </template>
    </BaseTable>

    <!-- View Order Modal -->
    <BaseModal
      v-model="showViewModal"
      title="Order Details"
      @close="closeViewModal"
    >
      <div v-if="selectedOrder" class="order-details">
        <div class="detail-grid">
          <div class="detail-item">
            <span class="detail-label">Order Number:</span>
            <span class="detail-value font-mono">{{ selectedOrder.order_number }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Status:</span>
            <span :class="getStatusClass(selectedOrder.status)">
              {{ getStatusLabel(selectedOrder.status) }}
            </span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Customer:</span>
            <span class="detail-value">{{ selectedOrder.customer?.name }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Email:</span>
            <span class="detail-value">{{ selectedOrder.customer?.email }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Phone:</span>
            <span class="detail-value">{{ selectedOrder.customer?.phone }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Order Date:</span>
            <span class="detail-value">{{ formatDate(selectedOrder.created_at) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Payment Method:</span>
            <span class="detail-value">{{ getPaymentMethodLabel(selectedOrder.payment_method) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Payment Status:</span>
            <span :class="getPaymentStatusClass(selectedOrder.payment_status)">
              {{ getPaymentStatusLabel(selectedOrder.payment_status) }}
            </span>
          </div>
        </div>

        <!-- Payment Proof Section -->
        <div v-if="selectedOrder.payment_proof_url" class="payment-proof-section">
          <h3 class="section-title">Payment Receipt</h3>
          <div class="receipt-container">
            <img 
              :src="selectedOrder.payment_proof_url" 
              alt="Payment Receipt"
              class="receipt-image"
              @click="openReceiptLightbox"
              @error="handleImageError"
            />
            <p class="receipt-hint">Click image to enlarge</p>
            <p v-if="imageError" class="text-red-600 text-sm mt-2">
              Failed to load image. URL: {{ selectedOrder.payment_proof_url }}
            </p>
          </div>
          
          <!-- Verification Actions -->
          <div v-if="selectedOrder.payment_status === 'pending' && selectedOrder.payment_proof" class="verification-actions">
            <div class="verification-info">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <span>Payment verification pending</span>
            </div>
            <div class="verification-buttons">
              <BaseButton 
                variant="danger" 
                @click="rejectPayment"
                :loading="verifying"
              >
                Reject Payment
              </BaseButton>
              <BaseButton 
                @click="approvePayment"
                :loading="verifying"
              >
                Approve Payment
              </BaseButton>
            </div>
          </div>

          <!-- Verified Info -->
          <div v-else-if="selectedOrder.payment_status === 'paid' && selectedOrder.verified_at" class="verified-info">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>
              Payment verified by {{ selectedOrder.verified_by?.name || 'Admin' }} 
              on {{ formatDate(selectedOrder.verified_at) }}
            </span>
          </div>
        </div>

        <div class="order-items-section">
          <h3 class="section-title">Order Items</h3>
          <table class="items-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in selectedOrder.items" :key="item.id">
                <td>{{ item.product?.name || 'N/A' }}</td>
                <td>{{ item.quantity }}</td>
                <td>₱{{ formatCurrency(item.price) }}</td>
                <td>₱{{ formatCurrency(item.quantity * item.price) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="text-right font-semibold">Total:</td>
                <td class="font-bold text-lg">₱{{ formatCurrency(selectedOrder.total_amount) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div v-if="selectedOrder.notes" class="notes-section">
          <h3 class="section-title">Notes</h3>
          <p class="notes-text">{{ selectedOrder.notes }}</p>
        </div>
      </div>

      <template #footer>
        <BaseButton variant="secondary" @click="closeViewModal">
          Close
        </BaseButton>
      </template>
    </BaseModal>

    <!-- Update Status Modal -->
    <BaseModal
      v-model="showUpdateStatusModal"
      title="Update Order Status"
      @close="closeStatusModal"
    >
      <div v-if="selectedOrder" class="status-form">
        <p class="mb-4">Update status for order <strong>{{ selectedOrder.order_number }}</strong></p>
        
        <div class="form-group">
          <label class="form-label">New Status</label>
          <select v-model="newStatus" class="form-select">
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="ready">Ready for Pickup</option>
            <option value="completed">Completed</option>
          </select>
        </div>
      </div>

      <template #footer>
        <BaseButton variant="secondary" @click="closeStatusModal">
          Cancel
        </BaseButton>
        <BaseButton @click="updateStatus" :loading="updating">
          Update Status
        </BaseButton>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { ordersApi } from '../../services/api/orders';
import BaseTable from '../../components/base/BaseTable.vue';
import BaseInput from '../../components/base/BaseInput.vue';
import BaseButton from '../../components/base/BaseButton.vue';
import BaseModal from '../../components/base/BaseModal.vue';
import StatsCard from '../../components/reports/StatsCard.vue';
import {
  ShoppingCartIcon,
  ClockIcon,
  ArrowPathIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';

// State
const orders = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const totalOrders = ref(0);
const perPage = ref(15);

const filters = ref({
  search: '',
  status: '',
  start_date: '',
  end_date: ''
});

const statsData = ref({
  total_orders: 0,
  pending_orders: 0,
  processing_orders: 0,
  ready_orders: 0,
  completed_orders: 0,
  cancelled_orders: 0,
  total_revenue: 0,
  pending_revenue: 0
});

const selectedOrder = ref(null);
const showViewModal = ref(false);
const showUpdateStatusModal = ref(false);
const newStatus = ref('');
const updating = ref(false);
const verifying = ref(false);
const imageError = ref(false);

// Computed
const stats = computed(() => [
  {
    title: 'Total Orders',
    value: statsData.value.total_orders || 0,
    icon: ShoppingCartIcon,
    subtitle: 'All customer orders'
  },
  {
    title: 'Pending',
    value: statsData.value.pending_orders || 0,
    icon: ClockIcon,
    subtitle: 'Awaiting processing'
  },
  {
    title: 'Processing',
    value: statsData.value.processing_orders || 0,
    icon: ArrowPathIcon,
    subtitle: 'Currently processing'
  },
  {
    title: 'Ready',
    value: statsData.value.ready_orders || 0,
    icon: CheckCircleIcon,
    subtitle: 'Ready for pickup'
  }
]);

const columns = [
  { key: 'order_number', label: 'Order #', width: '130px' },
  { key: 'customer', label: 'Customer', width: 'auto' },
  { key: 'items_count', label: 'Items', width: '80px' },
  { key: 'total_amount', label: 'Total', width: '120px' },
  { key: 'payment_method', label: 'Payment', width: '100px' },
  { key: 'status', label: 'Status', width: '120px' },
  { key: 'created_at', label: 'Date', width: '140px' }
  // Note: 'actions' column is handled automatically by BaseTable when using #actions slot
];

// Methods
const loadOrders = async () => {
  loading.value = true;
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      ...filters.value
    };

    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await ordersApi.getOrders(params);
    orders.value = response.data.data || [];
    totalOrders.value = response.data.total || 0;
    currentPage.value = response.data.current_page || 1;
  } catch (error) {
    console.error('Failed to load orders:', error);
    orders.value = [];
    totalOrders.value = 0;
  } finally {
    loading.value = false;
  }
};

const loadStats = async () => {
  try {
    const response = await ordersApi.getStats();
    statsData.value = response.data || {};
  } catch (error) {
    console.error('Failed to load stats:', error);
  }
};

const handlePageChange = (page) => {
  currentPage.value = page;
  loadOrders();
};

const resetFilters = () => {
  filters.value = {
    search: '',
    status: '',
    start_date: '',
    end_date: ''
  };
  currentPage.value = 1;
  loadOrders();
};

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    loadOrders();
  }, 500);
};

const viewOrder = async (order) => {
  try {
    const response = await ordersApi.getOrder(order.id);
    selectedOrder.value = response.data;
    imageError.value = false; // Reset image error state
    showViewModal.value = true;
    
    // Debug: Log the payment proof URL
    if (response.data.payment_proof_url) {
      console.log('Payment proof URL:', response.data.payment_proof_url);
      console.log('Payment proof path:', response.data.payment_proof);
    }
  } catch (error) {
    console.error('Failed to load order details:', error);
  }
};

const closeViewModal = () => {
  showViewModal.value = false;
  selectedOrder.value = null;
  imageError.value = false;
};

const handleImageError = (event) => {
  console.error('Failed to load image:', event.target.src);
  imageError.value = true;
};

const showStatusModal = (order) => {
  selectedOrder.value = order;
  newStatus.value = order.status;
  showUpdateStatusModal.value = true;
};

const closeStatusModal = () => {
  showUpdateStatusModal.value = false;
  selectedOrder.value = null;
  newStatus.value = '';
};

const updateStatus = async () => {
  if (!selectedOrder.value || !newStatus.value) return;

  updating.value = true;
  try {
    await ordersApi.updateOrderStatus(selectedOrder.value.id, newStatus.value);
    closeStatusModal();
    loadOrders();
    loadStats();
  } catch (error) {
    console.error('Failed to update order status:', error);
    alert('Failed to update order status');
  } finally {
    updating.value = false;
  }
};

const confirmCancel = async (order) => {
  if (!confirm(`Are you sure you want to cancel order ${order.order_number}? This will restore the inventory.`)) {
    return;
  }

  try {
    await ordersApi.cancelOrder(order.id);
    loadOrders();
    loadStats();
  } catch (error) {
    console.error('Failed to cancel order:', error);
    alert(error.response?.data?.message || 'Failed to cancel order');
  }
};

// Utility functions to parse and format data
const parseJSON = (data) => {
  if (!data) return null;
  if (typeof data === 'object') return data;
  try {
    return JSON.parse(data);
  } catch (e) {
    return null;
  }
};

const getCustomerName = (customer) => {
  const parsed = parseJSON(customer);
  return parsed?.name || 'N/A';
};

const getCustomerPhone = (customer) => {
  const parsed = parseJSON(customer);
  return parsed?.phone || parsed?.email || '';
};

const getItemsCount = (items) => {
  const parsed = parseJSON(items);
  if (Array.isArray(parsed)) {
    return parsed.length;
  }
  return 0;
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatTime = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleTimeString('en-PH', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'status-badge status-pending',
    processing: 'status-badge status-processing',
    ready: 'status-badge status-ready',
    completed: 'status-badge status-completed',
    cancelled: 'status-badge status-cancelled'
  };
  return classes[status] || 'status-badge';
};

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    processing: 'Processing',
    ready: 'Ready for Pickup',
    completed: 'Completed',
    cancelled: 'Cancelled'
  };
  return labels[status] || status;
};

const getPaymentMethodLabel = (method) => {
  const labels = {
    cash: 'Cash',
    card: 'Card',
    gcash: 'GCash',
    digital_wallet: 'Digital Wallet'
  };
  return labels[method] || method;
};

const getPaymentMethodClass = (method) => {
  const classes = {
    cash: 'payment-method-cash',
    card: 'payment-method-card',
    gcash: 'payment-method-gcash',
    digital_wallet: 'payment-method-digital'
  };
  return classes[method] || 'payment-method-cash';
};

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'payment-status-pending',
    paid: 'payment-status-paid',
    refunded: 'payment-status-refunded'
  };
  return classes[status] || 'payment-status-pending';
};

const getPaymentStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    paid: 'Paid',
    refunded: 'Refunded'
  };
  return labels[status] || status;
};

const openReceiptLightbox = () => {
  if (selectedOrder.value?.payment_proof_url) {
    window.open(selectedOrder.value.payment_proof_url, '_blank');
  }
};

const approvePayment = async () => {
  if (!selectedOrder.value) return;
  
  if (!confirm('Approve this payment? The order will be confirmed.')) {
    return;
  }

  verifying.value = true;
  try {
    await ordersApi.verifyPayment(selectedOrder.value.id, {
      action: 'approve'
    });
    
    // Reload order details
    const response = await ordersApi.getOrder(selectedOrder.value.id);
    selectedOrder.value = response.data;
    
    // Reload orders list and stats
    loadOrders();
    loadStats();
    
    alert('Payment approved successfully!');
  } catch (error) {
    console.error('Failed to approve payment:', error);
    alert(error.response?.data?.message || 'Failed to approve payment');
  } finally {
    verifying.value = false;
  }
};

const rejectPayment = async () => {
  if (!selectedOrder.value) return;
  
  const notes = prompt('Enter reason for rejection (optional):');
  if (notes === null) return; // User cancelled
  
  if (!confirm('Reject this payment? The order will be cancelled and inventory will be restored.')) {
    return;
  }

  verifying.value = true;
  try {
    await ordersApi.verifyPayment(selectedOrder.value.id, {
      action: 'reject',
      notes: notes || undefined
    });
    
    // Close modal and reload
    closeViewModal();
    loadOrders();
    loadStats();
    
    alert('Payment rejected successfully. Order cancelled.');
  } catch (error) {
    console.error('Failed to reject payment:', error);
    alert(error.response?.data?.message || 'Failed to reject payment');
  } finally {
    verifying.value = false;
  }
};

// Lifecycle
onMounted(() => {
  loadOrders();
  loadStats();
});
</script>

<style scoped>
.orders-container {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  gap: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.page-description {
  color: #6b7280;
  font-size: 1rem;
  max-width: 600px;
}

.header-info {
  flex-shrink: 0;
}

.info-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background-color: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 0.5rem;
  color: #1e40af;
  font-size: 0.875rem;
  font-weight: 500;
}

.info-badge svg {
  flex-shrink: 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.filters-section {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.filter-group {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr auto;
  gap: 1rem;
  align-items: center;
}

.filter-select {
  padding: 0.625rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  background-color: white;
  transition: all 0.2s;
}

.filter-select:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Empty State */
.empty-state {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 3rem 2rem;
}

.empty-state-content {
  max-width: 600px;
  margin: 0 auto;
  text-align: center;
}

.empty-icon {
  width: 4rem;
  height: 4rem;
  margin: 0 auto 1.5rem;
  color: #9ca3af;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.empty-description {
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.6;
  margin-bottom: 2rem;
}

.empty-actions {
  margin-top: 2rem;
}

.info-card {
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
  text-align: left;
}

.info-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.info-list {
  list-style: decimal;
  padding-left: 1.5rem;
  color: #4b5563;
  line-height: 1.8;
}

.info-list li {
  margin-bottom: 0.5rem;
}

/* Table Cell Styles */
.order-number {
  font-family: 'Courier New', monospace;
}

.order-id {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  background-color: #f3f4f6;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #4f46e5;
}

.customer-info {
  line-height: 1.4;
}

.customer-name {
  font-weight: 500;
  color: #1f2937;
  font-size: 0.9375rem;
}

.customer-contact {
  font-size: 0.8125rem;
  color: #6b7280;
  margin-top: 0.125rem;
}

.items-count {
  text-align: center;
}

.count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2rem;
  padding: 0.25rem 0.625rem;
  background-color: #e0e7ff;
  color: #4338ca;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}

.amount {
  text-align: right;
}

.amount-value {
  font-weight: 600;
  color: #059669;
  font-size: 0.9375rem;
}

.date-info {
  line-height: 1.4;
}

.date-primary {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.date-time {
  font-size: 0.75rem;
  color: #9ca3af;
  margin-top: 0.125rem;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  transition: all 0.2s;
  border: 1px solid;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
}

.action-label {
  display: inline-block;
}

.action-btn-view {
  background-color: #dbeafe;
  border-color: #93c5fd;
  color: #1e40af;
}

.action-btn-view:hover {
  background-color: #bfdbfe;
  border-color: #60a5fa;
}

.action-btn-edit {
  background-color: #fef3c7;
  border-color: #fcd34d;
  color: #92400e;
}

.action-btn-edit:hover {
  background-color: #fde68a;
  border-color: #fbbf24;
}

.action-btn-delete {
  background-color: #fee2e2;
  border-color: #fca5a5;
  color: #991b1b;
}

.action-btn-delete:hover {
  background-color: #fecaca;
  border-color: #f87171;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-processing {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-ready {
  background-color: #d1fae5;
  color: #065f46;
}

.status-completed {
  background-color: #d1fae5;
  color: #065f46;
}

.status-cancelled {
  background-color: #fee2e2;
  color: #991b1b;
}

.order-details {
  padding: 1rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.detail-value {
  font-size: 1rem;
  color: #1f2937;
}

.order-items-section {
  margin-top: 2rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
}

.items-table thead {
  background-color: #f9fafb;
}

.items-table th {
  padding: 0.75rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
}

.items-table td {
  padding: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}

.items-table tfoot td {
  border-top: 2px solid #e5e7eb;
  padding-top: 1rem;
}

.notes-section {
  margin-top: 2rem;
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
}

.notes-text {
  color: #374151;
  line-height: 1.6;
}

/* Payment Proof Section */
.payment-proof-section {
  margin-top: 2rem;
  padding: 1.5rem;
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
}

.receipt-container {
  text-align: center;
  margin-bottom: 1.5rem;
}

.receipt-image {
  max-width: 100%;
  max-height: 400px;
  border: 2px solid #d1d5db;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.receipt-image:hover {
  transform: scale(1.02);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.receipt-hint {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
  font-style: italic;
}

.verification-actions {
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #fffbeb;
  border: 1px solid #fcd34d;
  border-radius: 0.5rem;
}

.verification-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  color: #92400e;
  font-weight: 500;
}

.verification-buttons {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.verified-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #d1fae5;
  border: 1px solid #6ee7b7;
  border-radius: 0.5rem;
  color: #065f46;
  font-weight: 500;
}

.payment-status-pending {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  background-color: #fef3c7;
  color: #92400e;
  text-transform: uppercase;
}

.payment-status-paid {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  background-color: #d1fae5;
  color: #065f46;
  text-transform: uppercase;
}

.payment-status-refunded {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  background-color: #fee2e2;
  color: #991b1b;
  text-transform: uppercase;
}

/* Payment Method Badges (for table) */
.payment-method {
  display: flex;
  align-items: center;
}

.payment-method span {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.payment-method-cash {
  background-color: #d1fae5;
  color: #065f46;
}

.payment-method-card {
  background-color: #dbeafe;
  color: #1e40af;
}

.payment-method-gcash {
  background-color: #ddd6fe;
  color: #5b21b6;
}

.payment-method-digital {
  background-color: #fce7f3;
  color: #9f1239;
}

.status-form {
  padding: 1rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-select {
  width: 100%;
  padding: 0.625rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  background-color: white;
}

.form-select:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

@media (max-width: 1024px) {
  .action-label {
    display: none;
  }

  .action-btn {
    padding: 0.5rem;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 1rem;
  }

  .filter-group {
    grid-template-columns: 1fr;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    flex-wrap: wrap;
  }
}
</style>

