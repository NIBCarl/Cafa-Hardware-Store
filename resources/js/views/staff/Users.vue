<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">User & Customer Management</h1>
        <p class="mt-1 text-sm text-gray-600">Manage system staff and customer accounts</p>
      </div>
      <button
        v-if="activeTab === 'staff'"
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700"
      >
        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Add User
      </button>
      <button
        v-if="activeTab === 'customers'"
        @click="openCreateCustomerModal"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700"
      >
        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Add Customer
      </button>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <button
          @click="activeTab = 'staff'"
          :class="[
            activeTab === 'staff'
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          Staff Users
        </button>
        <button
          @click="activeTab = 'customers'"
          :class="[
            activeTab === 'customers'
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          Customers
        </button>
      </nav>
    </div>

    <!-- Staff Content -->
    <div v-if="activeTab === 'staff'" class="space-y-6">
      <!-- Filters -->
      <div class="bg-white p-4 rounded-lg shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search by name or email..."
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select
              v-model="filters.role"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="cashier">Cashier</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="filters.is_active"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
              <option value="">All Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <BaseTable
          title="Staff Users"
          :columns="columns"
          :items="users"
          :loading="loading"
          :total-items="users.length"
          empty-message="No users found"
        >
          <template #role="{ item }">
            <span
              :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                getRoleBadgeClass(item.role)
              ]"
            >
              {{ item.role }}
            </span>
          </template>

          <template #is_active="{ item }">
            <button
              @click="toggleUserStatus(item)"
              :class="[
                'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-colors',
                item.is_active
                  ? 'bg-green-100 text-green-800 hover:bg-green-200'
                  : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
              ]"
            >
              {{ item.is_active ? 'Active' : 'Inactive' }}
            </button>
          </template>

          <template #created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #actions="{ item }">
            <div class="flex items-center justify-end gap-2">
              <!-- Edit Button -->
              <button
                @click="openEditModal(item)"
                class="action-btn action-btn-edit group"
                title="Edit user"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span class="action-btn-text">Edit</span>
              </button>
              
              <!-- Delete Button -->
              <button
                @click="deleteUser(item)"
                class="action-btn action-btn-delete group"
                title="Delete user"
                :disabled="item?.id === currentUser?.id"
                :class="{ 'opacity-50 cursor-not-allowed': item?.id === currentUser?.id }"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span class="action-btn-text">Delete</span>
              </button>
            </div>
          </template>
        </BaseTable>
      </div>
    </div>

    <!-- Customer Content -->
    <div v-if="activeTab === 'customers'" class="space-y-6">
      <!-- Customer Filters -->
      <div class="bg-white p-4 rounded-lg shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="customerFilters.search"
              type="text"
              placeholder="Search by name, email, or phone..."
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Status</label>
            <select
              v-model="customerFilters.email_verified"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
              <option value="">All</option>
              <option value="1">Verified</option>
              <option value="0">Unverified</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Account Status</label>
            <select
              v-model="customerFilters.is_active"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
              <option value="">All Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Customers Table -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <BaseTable
          title="Customers"
          :columns="customerColumns"
          :items="customers"
          :loading="loadingCustomers"
          :total-items="customersTotal"
          :per-page="customerPerPage"
          :current-page="customerPage"
          empty-message="No customers found"
          @page-change="handleCustomerPageChange"
        >
          <template #is_active="{ item }">
            <button
              @click="toggleCustomerStatus(item)"
              :class="[
                'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-colors',
                item.is_active
                  ? 'bg-green-100 text-green-800 hover:bg-green-200'
                  : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
              ]"
            >
              {{ item.is_active ? 'Active' : 'Inactive' }}
            </button>
          </template>

          <template #orders_count="{ item }">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
              {{ item.orders_count || 0 }} orders
            </span>
          </template>

          <template #created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #actions="{ item }">
            <div class="flex items-center justify-end gap-2">
              <!-- Edit Button -->
              <button
                @click="openEditCustomerModal(item)"
                class="action-btn action-btn-edit group"
                title="Edit customer"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span class="action-btn-text">Edit</span>
              </button>
              
              <!-- Delete Button -->
              <button
                @click="deleteCustomer(item)"
                class="action-btn action-btn-delete group"
                title="Delete customer"
                :disabled="item.orders_count > 0"
                :class="{ 'opacity-50 cursor-not-allowed': item.orders_count > 0 }"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span class="action-btn-text">Delete</span>
              </button>
            </div>
          </template>
        </BaseTable>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="submitForm">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="space-y-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                  {{ isEditing ? 'Edit User' : 'Create New User' }}
                </h3>

                <!-- Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    id="name"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                  <input
                    v-model="form.email"
                    type="email"
                    id="email"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </div>

                <!-- Role -->
                <div>
                  <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                  <select
                    v-model="form.role"
                    id="role"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  >
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="cashier">Cashier</option>
                  </select>
                </div>

                <!-- Password -->
                <div>
                  <label for="password" class="block text-sm font-medium text-gray-700">
                    Password {{ isEditing ? '(leave blank to keep current)' : '*' }}
                  </label>
                  <input
                    v-model="form.password"
                    type="password"
                    id="password"
                    :required="!isEditing"
                    minlength="8"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </div>

                <!-- Confirm Password -->
                <div>
                  <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirm Password
                  </label>
                  <input
                    v-model="form.password_confirmation"
                    type="password"
                    id="password_confirmation"
                    :required="!isEditing && form.password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </div>

                <!-- Active Status -->
                <div v-if="isEditing" class="flex items-center">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    id="is_active"
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Active
                  </label>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="submit"
                :disabled="submitting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                {{ submitting ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Customer Modal -->
    <div
      v-if="showCustomerModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeCustomerModal"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="submitCustomerForm">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="space-y-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                  {{ isEditingCustomer ? 'Edit Customer' : 'Create New Customer' }}
                </h3>

                <!-- Name -->
                <div>
                  <label for="customer-name" class="block text-sm font-medium text-gray-700">Name *</label>
                  <input
                    v-model="customerForm.name"
                    type="text"
                    id="customer-name"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </div>

                <!-- Email -->
                <div>
                  <label for="customer-email" class="block text-sm font-medium text-gray-700">Email *</label>
                  <input
                    v-model="customerForm.email"
                    type="email"
                    id="customer-email"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </div>

                <!-- Phone -->
                <div>
                  <label for="customer-phone" class="block text-sm font-medium text-gray-700">Phone *</label>
                  <input
                    v-model="customerForm.phone"
                    type="text"
                    id="customer-phone"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </div>

                <!-- Password -->
                <div>
                  <label for="customer-password" class="block text-sm font-medium text-gray-700">
                    Password {{ isEditingCustomer ? '(leave blank to keep current)' : '*' }}
                  </label>
                  <input
                    v-model="customerForm.password"
                    type="password"
                    id="customer-password"
                    :required="!isEditingCustomer"
                    minlength="8"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </div>

                <!-- Confirm Password -->
                <div>
                  <label for="customer-password-confirmation" class="block text-sm font-medium text-gray-700">
                    Confirm Password
                  </label>
                  <input
                    v-model="customerForm.password_confirmation"
                    type="password"
                    id="customer-password-confirmation"
                    :required="!isEditingCustomer && customerForm.password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </div>

                <!-- Address -->
                <div>
                  <label for="customer-address" class="block text-sm font-medium text-gray-700">Address</label>
                  <textarea
                    v-model="customerForm.address"
                    id="customer-address"
                    rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label for="customer-city" class="block text-sm font-medium text-gray-700">City</label>
                    <input
                      v-model="customerForm.city"
                      type="text"
                      id="customer-city"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    />
                  </div>
                  <div>
                    <label for="customer-postal" class="block text-sm font-medium text-gray-700">Postal Code</label>
                    <input
                      v-model="customerForm.postal_code"
                      type="text"
                      id="customer-postal"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                  <input
                    v-model="customerForm.is_active"
                    type="checkbox"
                    id="customer-is-active"
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <label for="customer-is-active" class="ml-2 block text-sm text-gray-900">
                    Active
                  </label>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="submit"
                :disabled="submittingCustomer"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                {{ submittingCustomer ? 'Saving...' : (isEditingCustomer ? 'Update Customer' : 'Create Customer') }}
              </button>
              <button
                type="button"
                @click="closeCustomerModal"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { usersApi, customersApi } from '@/services/api';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import BaseTable from '@/components/base/BaseTable.vue';

const authStore = useAuthStore();
const toastStore = useToastStore();

const currentUser = computed(() => authStore.user || { id: null });

// Tab Management
const activeTab = ref('staff');

// Staff Management
const columns = [
  { key: 'id', label: 'ID', width: '60px' },
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role', width: '120px' },
  { key: 'is_active', label: 'Status', width: '120px' },
  { key: 'created_at', label: 'Created', width: '150px' },
];

const users = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const submitting = ref(false);
const currentEditUser = ref(null);

const filters = ref({
  search: '',
  role: '',
  is_active: '',
});

const form = ref({
  name: '',
  email: '',
  role: '',
  password: '',
  password_confirmation: '',
  is_active: true,
});

// Customer Management
const customerColumns = [
  { key: 'id', label: 'ID', width: '60px' },
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'phone', label: 'Phone' },
  { key: 'orders_count', label: 'Orders', width: '100px' },
  { key: 'is_active', label: 'Status', width: '120px' },
  { key: 'created_at', label: 'Joined', width: '150px' },
];

const customers = ref([]);
const loadingCustomers = ref(false);
const customersTotal = ref(0);
const customerPage = ref(1);
const customerPerPage = ref(15);
const showCustomerModal = ref(false);
const isEditingCustomer = ref(false);
const submittingCustomer = ref(false);
const currentEditCustomer = ref(null);

const customerFilters = ref({
  search: '',
  email_verified: '',
  is_active: '',
});

const customerForm = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  address: '',
  city: '',
  postal_code: '',
  is_active: true,
});

// Staff Functions
const fetchUsers = async () => {
  loading.value = true;
  try {
    const params = {};
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.role) params.role = filters.value.role;
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active;

    const response = await usersApi.list(params);
    users.value = response.data.data || response.data;
  } catch (error) {
    console.error('Failed to fetch users:', error);
    toastStore.error('Failed to load users');
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  currentEditUser.value = null;
  form.value = {
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
    is_active: true,
  };
  showModal.value = true;
};

const openEditModal = (user) => {
  isEditing.value = true;
  currentEditUser.value = user;
  form.value = {
    name: user.name,
    email: user.email,
    role: user.role,
    password: '',
    password_confirmation: '',
    is_active: user.is_active,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.value = {
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
    is_active: true,
  };
};

const submitForm = async () => {
  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    toastStore.error('Passwords do not match');
    return;
  }

  submitting.value = true;
  try {
    const data = { ...form.value };
    
    if (isEditing.value && !data.password) {
      delete data.password;
      delete data.password_confirmation;
    }

    if (isEditing.value) {
      await usersApi.update(currentEditUser.value.id, data);
      toastStore.success('User updated successfully');
    } else {
      await usersApi.create(data);
      toastStore.success('User created successfully');
    }

    closeModal();
    fetchUsers();
  } catch (error) {
    console.error('Failed to save user:', error);
    const message = error.response?.data?.message || 'Failed to save user';
    toastStore.error(message);
  } finally {
    submitting.value = false;
  }
};

const toggleUserStatus = async (user) => {
  if (user.id === currentUser.value?.id) {
    toastStore.error('You cannot change your own status');
    return;
  }

  try {
    await usersApi.toggleStatus(user.id);
    toastStore.success('User status updated');
    fetchUsers();
  } catch (error) {
    console.error('Failed to toggle user status:', error);
    const message = error.response?.data?.message || 'Failed to update user status';
    toastStore.error(message);
  }
};

const deleteUser = async (user) => {
  if (user.id === currentUser.value?.id) {
    toastStore.error('You cannot delete your own account');
    return;
  }

  if (!confirm(`Are you sure you want to delete ${user.name}?`)) {
    return;
  }

  try {
    await usersApi.delete(user.id);
    toastStore.success('User deleted successfully');
    fetchUsers();
  } catch (error) {
    console.error('Failed to delete user:', error);
    const message = error.response?.data?.message || 'Failed to delete user';
    toastStore.error(message);
  }
};

// Customer Functions
const fetchCustomers = async () => {
  loadingCustomers.value = true;
  try {
    const params = {
      page: customerPage.value,
      per_page: customerPerPage.value
    };
    
    if (customerFilters.value.search) params.search = customerFilters.value.search;
    if (customerFilters.value.email_verified !== '') params.email_verified = customerFilters.value.email_verified;
    if (customerFilters.value.is_active !== '') params.is_active = customerFilters.value.is_active;

    const response = await customersApi.list(params);
    const data = response.data;
    
    customers.value = data.data;
    customersTotal.value = data.total;
    customerPerPage.value = data.per_page;
  } catch (error) {
    console.error('Failed to fetch customers:', error);
    toastStore.error('Failed to load customers');
  } finally {
    loadingCustomers.value = false;
  }
};

const handleCustomerPageChange = (page) => {
  customerPage.value = page;
  fetchCustomers();
};

const openCreateCustomerModal = () => {
  isEditingCustomer.value = false;
  currentEditCustomer.value = null;
  customerForm.value = {
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    address: '',
    city: '',
    postal_code: '',
    is_active: true,
  };
  showCustomerModal.value = true;
};

const openEditCustomerModal = (customer) => {
  isEditingCustomer.value = true;
  currentEditCustomer.value = customer;
  customerForm.value = {
    name: customer.name,
    email: customer.email,
    phone: customer.phone,
    password: '',
    password_confirmation: '',
    address: customer.address || '',
    city: customer.city || '',
    postal_code: customer.postal_code || '',
    is_active: customer.is_active,
  };
  showCustomerModal.value = true;
};

const closeCustomerModal = () => {
  showCustomerModal.value = false;
  customerForm.value = {
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    address: '',
    city: '',
    postal_code: '',
    is_active: true,
  };
};

const submitCustomerForm = async () => {
  if (customerForm.value.password && customerForm.value.password !== customerForm.value.password_confirmation) {
    toastStore.error('Passwords do not match');
    return;
  }

  submittingCustomer.value = true;
  try {
    const data = { ...customerForm.value };

    // Remove password fields if empty (for edit)
    if (isEditingCustomer.value && !data.password) {
      delete data.password;
      delete data.password_confirmation;
    }

    if (isEditingCustomer.value) {
      await customersApi.update(currentEditCustomer.value.id, data);
      toastStore.success('Customer updated successfully');
    } else {
      await customersApi.create(data);
      toastStore.success('Customer created successfully');
    }
    
    closeCustomerModal();
    fetchCustomers();
  } catch (error) {
    console.error('Failed to save customer:', error);
    const message = error.response?.data?.message || 'Failed to save customer';
    toastStore.error(message);
  } finally {
    submittingCustomer.value = false;
  }
};

const toggleCustomerStatus = async (customer) => {
  try {
    await customersApi.toggleStatus(customer.id);
    toastStore.success('Customer status updated');
    fetchCustomers();
  } catch (error) {
    console.error('Failed to toggle customer status:', error);
    const message = error.response?.data?.message || 'Failed to update customer status';
    toastStore.error(message);
  }
};

const deleteCustomer = async (customer) => {
  if (customer.orders_count > 0) {
    toastStore.error('Cannot delete customer with existing orders');
    return;
  }

  if (!confirm(`Are you sure you want to delete ${customer.name}?`)) {
    return;
  }

  try {
    await customersApi.delete(customer.id);
    toastStore.success('Customer deleted successfully');
    fetchCustomers();
  } catch (error) {
    console.error('Failed to delete customer:', error);
    const message = error.response?.data?.message || 'Failed to delete customer';
    toastStore.error(message);
  }
};

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'bg-purple-100 text-purple-800',
    cashier: 'bg-green-100 text-green-800',
  };
  return classes[role] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// Watch filters and refetch
watch(filters, () => {
  fetchUsers();
}, { deep: true });

watch(customerFilters, () => {
  customerPage.value = 1;
  fetchCustomers();
}, { deep: true });

// Watch active tab to fetch data
watch(activeTab, (newTab) => {
  if (newTab === 'staff' && users.value.length === 0) {
    fetchUsers();
  } else if (newTab === 'customers' && customers.value.length === 0) {
    fetchCustomers();
  }
});

onMounted(() => {
  fetchUsers();
});
</script>

<style scoped>
/* Action Buttons - Enhanced with depth and hierarchy */
.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.875rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease-in-out;
  border: 1px solid transparent;
  cursor: pointer;
  
  /* Small shadow for depth - light on top, dark on bottom */
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.05),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.1);
}

/* Edit Button - Blue/Primary (Standard action) */
.action-btn-edit {
  background: linear-gradient(to bottom, #3b82f6, #2563eb);
  color: white;
  border-color: #2563eb;
}

.action-btn-edit:hover:not(:disabled) {
  background: linear-gradient(to bottom, #60a5fa, #3b82f6);
  box-shadow: 
    0 4px 6px -1px rgba(59, 130, 246, 0.3),
    0 2px 4px -1px rgba(59, 130, 246, 0.2),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.15);
  transform: translateY(-1px);
}

.action-btn-edit:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.1),
    inset 0 2px 4px 0 rgba(0, 0, 0, 0.1);
}

/* Delete Button - Red/Danger (Destructive action) */
.action-btn-delete {
  background: linear-gradient(to bottom, #ef4444, #dc2626);
  color: white;
  border-color: #dc2626;
}

.action-btn-delete:hover:not(:disabled) {
  background: linear-gradient(to bottom, #f87171, #ef4444);
  box-shadow: 
    0 4px 6px -1px rgba(239, 68, 68, 0.3),
    0 2px 4px -1px rgba(239, 68, 68, 0.2),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.15);
  transform: translateY(-1px);
}

.action-btn-delete:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.1),
    inset 0 2px 4px 0 rgba(0, 0, 0, 0.1);
}

/* Icon styling */
.action-btn svg {
  flex-shrink: 0;
  transition: transform 0.2s ease-in-out;
}

.action-btn:hover:not(:disabled) svg {
  transform: scale(1.1);
}

/* Button text - responsive */
.action-btn-text {
  white-space: nowrap;
}

/* ===================================
   RESPONSIVE DESIGN - Mobile First
   =================================== */

/* Mobile: Icon-only buttons */
@media (max-width: 640px) {
  .action-btn-text {
    display: none;
  }
  
  .action-btn {
    padding: 0.625rem;
    min-width: 2.5rem;
    min-height: 2.5rem;
    justify-content: center;
  }
  
  .action-btn svg {
    width: 1.125rem;
    height: 1.125rem;
  }
}

/* Tablet: Show icons with text */
@media (min-width: 641px) and (max-width: 1024px) {
  .action-btn {
    padding: 0.5rem 0.75rem;
    min-height: 2.25rem;
  }
  
  .action-btn-text {
    font-size: 0.813rem;
  }
}

/* Desktop: Full buttons */
@media (min-width: 1025px) {
  .action-btn {
    padding: 0.5rem 0.875rem;
  }
}

/* Focus states for accessibility */
.action-btn:focus {
  outline: none;
}

.action-btn-edit:focus {
  box-shadow: 
    0 0 0 2px white,
    0 0 0 4px #3b82f6;
}

.action-btn-delete:focus {
  box-shadow: 
    0 0 0 2px white,
    0 0 0 4px #ef4444;
}

/* Disabled state */
.action-btn:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

/* Performance optimizations */
@media (prefers-reduced-motion: reduce) {
  .action-btn,
  .action-btn svg {
    transition: none !important;
    transform: none !important;
  }
}

@media (prefers-contrast: high) {
  .action-btn {
    border-width: 2px;
  }
}
</style>
