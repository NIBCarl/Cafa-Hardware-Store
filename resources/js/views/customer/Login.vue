<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Customer Login
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Welcome back to CAFA Hardware Store
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
              Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              placeholder="your@email.com"
            />
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
              class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              placeholder="Password"
            />
          </div>
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
            <span v-if="isLoading">Logging in...</span>
            <span v-else>Sign in</span>
          </button>
        </div>

        <div class="text-center">
          <p class="text-sm text-gray-600">
            Don't have an account?
            <router-link to="/customer/register" class="font-medium text-primary-600 hover:text-primary-500">
              Register here
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
  email: '',
  password: '',
});

const error = ref('');
const isLoading = ref(false);

const handleLogin = async () => {
  error.value = '';
  isLoading.value = true;

  try {
    await authStore.login(form.value);
    toastStore.success('Login successful!');
    router.push('/customer/shop');
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Please check your credentials.';
  } finally {
    isLoading.value = false;
  }
};
</script>

