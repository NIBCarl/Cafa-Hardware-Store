<template>
  <div class="min-h-screen bg-gray-50">
    <CustomerHeader />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">My Profile</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-col items-center">
              <div class="h-24 w-24 rounded-full bg-primary-100 flex items-center justify-center mb-4">
                <span class="text-3xl font-bold text-primary-600">
                  {{ authStore.customer?.name?.charAt(0).toUpperCase() }}
                </span>
              </div>
              <h2 class="text-xl font-semibold text-gray-900">{{ authStore.customer?.name }}</h2>
              <p class="text-sm text-gray-500 mt-1">{{ authStore.customer?.email }}</p>
            </div>

            <div class="mt-6 space-y-2">
              <button
                @click="activeTab = 'profile'"
                :class="[
                  'w-full text-left px-4 py-2 rounded-md text-sm font-medium',
                  activeTab === 'profile' ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50'
                ]"
              >
                Profile Information
              </button>
              <button
                @click="activeTab = 'password'"
                :class="[
                  'w-full text-left px-4 py-2 rounded-md text-sm font-medium',
                  activeTab === 'password' ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50'
                ]"
              >
                Change Password
              </button>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2">
          <!-- Profile Information Tab -->
          <div v-if="activeTab === 'profile'" class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Profile Information</h3>

            <form @submit.prevent="handleUpdateProfile" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input
                  v-model="authStore.customer.email"
                  type="email"
                  disabled
                  class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed"
                />
                <p class="mt-1 text-xs text-gray-500">Email cannot be changed</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input
                  v-model="profileForm.phone"
                  type="tel"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea
                  v-model="profileForm.address"
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                  <input
                    v-model="profileForm.city"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                  <input
                    v-model="profileForm.postal_code"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
              </div>

              <div v-if="profileError" class="rounded-md bg-red-50 p-4">
                <div class="text-sm text-red-800">{{ profileError }}</div>
              </div>

              <div v-if="profileSuccess" class="rounded-md bg-green-50 p-4">
                <div class="text-sm text-green-800">{{ profileSuccess }}</div>
              </div>

              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="updatingProfile"
                  class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="updatingProfile">Saving...</span>
                  <span v-else>Save Changes</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Change Password Tab -->
          <div v-else-if="activeTab === 'password'" class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Change Password</h3>

            <form @submit.prevent="handleChangePassword" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input
                  v-model="passwordForm.password"
                  type="password"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                  placeholder="At least 8 characters"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              <div v-if="passwordError" class="rounded-md bg-red-50 p-4">
                <div class="text-sm text-red-800">{{ passwordError }}</div>
              </div>

              <div v-if="passwordSuccess" class="rounded-md bg-green-50 p-4">
                <div class="text-sm text-green-800">{{ passwordSuccess }}</div>
              </div>

              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="updatingPassword"
                  class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="updatingPassword">Updating...</span>
                  <span v-else>Update Password</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useCustomerAuthStore } from '@/stores/customerAuth';
import CustomerHeader from '@/components/customer/CustomerHeader.vue';

const authStore = useCustomerAuthStore();

const activeTab = ref('profile');

const profileForm = ref({
  name: '',
  phone: '',
  address: '',
  city: '',
  postal_code: '',
});

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatingProfile = ref(false);
const updatingPassword = ref(false);
const profileError = ref('');
const profileSuccess = ref('');
const passwordError = ref('');
const passwordSuccess = ref('');

const loadProfileData = () => {
  if (authStore.customer) {
    profileForm.value = {
      name: authStore.customer.name || '',
      phone: authStore.customer.phone || '',
      address: authStore.customer.address || '',
      city: authStore.customer.city || '',
      postal_code: authStore.customer.postal_code || '',
    };
  }
};

const handleUpdateProfile = async () => {
  profileError.value = '';
  profileSuccess.value = '';
  updatingProfile.value = true;

  try {
    await authStore.updateProfile(profileForm.value);
    profileSuccess.value = 'Profile updated successfully!';
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to update profile';
  } finally {
    updatingProfile.value = false;
  }
};

const handleChangePassword = async () => {
  passwordError.value = '';
  passwordSuccess.value = '';

  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    passwordError.value = 'Passwords do not match';
    return;
  }

  updatingPassword.value = true;

  try {
    await authStore.changePassword(passwordForm.value);
    passwordSuccess.value = 'Password changed successfully!';
    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: '',
    };
  } catch (error) {
    passwordError.value = error.response?.data?.message || 'Failed to change password';
  } finally {
    updatingPassword.value = false;
  }
};

onMounted(() => {
  loadProfileData();
});
</script>

