<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
        <p class="mt-1 text-sm text-gray-600">Manage system users and their permissions</p>
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700"
      >
        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Add User
      </button>
    </div>

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
            <option value="manager">Manager</option>
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
        title="Users"
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
                    <option value="manager">Manager</option>
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
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { usersApi } from '@/services/api';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import BaseTable from '@/components/base/BaseTable.vue';

const authStore = useAuthStore();
const toastStore = useToastStore();

const currentUser = computed(() => authStore.user || { id: null });

const columns = [
  { key: 'id', label: 'ID', width: '60px' },
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role', width: '120px' },
  { key: 'is_active', label: 'Status', width: '120px' },
  { key: 'created_at', label: 'Created', width: '150px' },
  // Note: 'actions' column is handled automatically by BaseTable when using #actions slot
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
    
    // Remove password fields if empty (for edit)
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

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'bg-purple-100 text-purple-800',
    manager: 'bg-blue-100 text-blue-800',
    cashier: 'bg-green-100 text-green-800',
  };
  return classes[role] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString) => {
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
