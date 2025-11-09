<template>
  <div class="space-y-6">
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div>
        <label for="email-address" class="block text-sm font-medium text-gray-700">
          Email address
        </label>
        <div class="mt-1">
          <input
            id="email-address"
            name="email"
            type="email"
            autocomplete="email"
            required
            v-model="form.email"
            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="admin@cafa.com"
          />
        </div>
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
          Password
        </label>
        <div class="mt-1">
          <input
            id="password"
            name="password"
            type="password"
            autocomplete="current-password"
            required
            v-model="form.password"
            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="password"
          />
        </div>
      </div>

      <div>
        <button
          type="submit"
          :disabled="loading"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loading">Signing in...</span>
          <span v-else>Sign in</span>
        </button>
      </div>
    </form>

    <div class="text-center text-sm text-gray-600">
      <p>Default credentials:</p>
      <p class="font-mono">Email: admin@cafa.com</p>
      <p class="font-mono">Password: password</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';

const router = useRouter();
const authStore = useAuthStore();
const toastStore = useToastStore();

const loading = ref(false);
const form = reactive({
  email: '',
  password: '',
  device_name: navigator.userAgent,
});

const handleSubmit = async () => {
  loading.value = true;
  try {
    console.log('Attempting login with:', form.email);
    await authStore.login(form);
    console.log('Login successful, token:', authStore.token);
    console.log('User:', authStore.user);
    
    toastStore.add({
      type: 'success',
      message: 'Successfully logged in!',
    });
    
    console.log('Redirecting to dashboard...');
    await router.push({ name: 'Dashboard' });
    console.log('Redirect complete');
  } catch (error) {
    console.error('Login error:', error);
    toastStore.add({
      type: 'error',
      message: error.response?.data?.message || 'Failed to login',
    });
  } finally {
    loading.value = false;
  }
};
</script>