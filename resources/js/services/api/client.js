import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';

// Create axios instance
const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const authStore = useAuthStore();
    const toastStore = useToastStore();

    if (error.response) {
      // Handle different error status codes
      switch (error.response.status) {
        case 401:
          // Unauthorized - clear auth state and redirect to login
          authStore.logout();
          window.location.href = '/login';
          break;

        case 403:
          // Forbidden
          toastStore.error('You do not have permission to perform this action.');
          break;

        case 404:
          // Not Found
          toastStore.error('The requested resource was not found.');
          break;

        case 422:
          // Validation errors
          const validationErrors = error.response.data.errors;
          if (validationErrors) {
            const firstError = Object.values(validationErrors)[0][0];
            toastStore.error(firstError);
          }
          break;

        case 429:
          // Too Many Requests
          toastStore.error('Too many requests. Please try again later.');
          break;

        case 500:
          // Server Error
          toastStore.error('An unexpected error occurred. Please try again later.');
          break;

        default:
          toastStore.error('An error occurred. Please try again.');
      }
    } else if (error.request) {
      // Network error
      toastStore.error('Unable to connect to the server. Please check your internet connection.');
    } else {
      // Other errors
      toastStore.error('An unexpected error occurred.');
    }

    return Promise.reject(error);
  }
);

export default apiClient;
