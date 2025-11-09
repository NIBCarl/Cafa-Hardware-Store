import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

// Create a dedicated axios instance for customer API to avoid conflicts with staff auth
const customerApiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

export const useCustomerAuthStore = defineStore('customerAuth', () => {
  const customer = ref(null);
  const token = ref(localStorage.getItem('customer_token'));
  const isLoading = ref(false);

  const isAuthenticated = computed(() => !!customer.value);

  // Set auth headers on customer-specific axios instance
  if (token.value) {
    customerApiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  async function register(userData) {
    isLoading.value = true;
    try {
      const response = await customerApiClient.post('/customer/register', userData);
      customer.value = response.data.customer;
      token.value = response.data.token;
      localStorage.setItem('customer_token', token.value);
      customerApiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      return response.data;
    } catch (error) {
      throw error;
    } finally {
      isLoading.value = false;
    }
  }

  async function login(credentials) {
    isLoading.value = true;
    try {
      const response = await customerApiClient.post('/customer/login', credentials);
      customer.value = response.data.customer;
      token.value = response.data.token;
      localStorage.setItem('customer_token', token.value);
      customerApiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      return response.data;
    } catch (error) {
      throw error;
    } finally {
      isLoading.value = false;
    }
  }

  async function logout() {
    try {
      await customerApiClient.post('/customer/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      customer.value = null;
      token.value = null;
      localStorage.removeItem('customer_token');
      delete customerApiClient.defaults.headers.common['Authorization'];
    }
  }

  async function fetchProfile() {
    if (!token.value) return;

    try {
      const response = await customerApiClient.get('/customer/profile');
      customer.value = response.data.customer;
    } catch (error) {
      // Token might be expired
      await logout();
      throw error;
    }
  }

  async function updateProfile(profileData) {
    try {
      const response = await customerApiClient.put('/customer/profile', profileData);
      customer.value = response.data.customer;
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async function changePassword(passwordData) {
    try {
      const response = await customerApiClient.post('/customer/change-password', passwordData);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  return {
    customer,
    token,
    isLoading,
    isAuthenticated,
    register,
    login,
    logout,
    fetchProfile,
    updateProfile,
    changePassword,
  };
});

