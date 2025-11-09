<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Create Account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Join CAFA Hardware Store
        </p>
      </div>
      
      <form class="mt-8 space-y-4" @submit.prevent="handleRegister">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            Full Name
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="appearance-none rounded-lg block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="Juan Dela Cruz"
          />
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            Email Address
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="appearance-none rounded-lg block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="your@email.com"
          />
        </div>

        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
            Phone Number
          </label>
          <input
            id="phone"
            v-model="form.phone"
            type="tel"
            required
            class="appearance-none rounded-lg block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="+639123456789"
          />
          <p class="mt-1 text-xs text-gray-500">For order notifications via SMS</p>
        </div>

        <div>
          <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
            Address
          </label>
          <textarea
            id="address"
            v-model="form.address"
            rows="2"
            class="appearance-none rounded-lg block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="Street, Barangay"
          ></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
              City
            </label>
            <input
              id="city"
              v-model="form.city"
              type="text"
              class="appearance-none rounded-lg block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="City"
            />
          </div>
          <div>
            <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">
              Postal Code
            </label>
            <input
              id="postal_code"
              v-model="form.postal_code"
              type="text"
              class="appearance-none rounded-lg block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="8400"
            />
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Password
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="appearance-none rounded-lg block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="At least 8 characters"
          />
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
            Confirm Password
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="appearance-none rounded-lg block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="Repeat password"
          />
        </div>

        <div v-if="error" class="rounded-md bg-red-50 p-4">
          <div class="text-sm text-red-800">
            {{ error }}
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="isLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isLoading">Creating Account...</span>
            <span v-else>Register</span>
          </button>
        </div>

        <div class="text-center">
          <p class="text-sm text-gray-600">
            Already have an account?
            <router-link to="/customer/login" class="font-medium text-primary-600 hover:text-primary-500">
              Login here
            </router-link>
          </p>
        </div>

        <div class="text-center">
          <router-link to="/" class="text-sm text-gray-600 hover:text-gray-900">
            ← Back to Home
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useCustomerAuthStore } from '@/stores/customerAuth';
import { useToastStore } from '@/stores/toast';

const router = useRouter();
const authStore = useCustomerAuthStore();
const toastStore = useToastStore();

const form = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  postal_code: '',
  password: '',
  password_confirmation: '',
});

const error = ref('');
const isLoading = ref(false);

const handleRegister = async () => {
  error.value = '';

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match.';
    return;
  }

  isLoading.value = true;

  try {
    await authStore.register(form.value);
    toastStore.success('Registration successful! Welcome to CAFA Hardware!');
    router.push('/customer/shop');
  } catch (err) {
    error.value = err.response?.data?.message || 'Registration failed. Please try again.';
  } finally {
    isLoading.value = false;
  }
};
</script>

