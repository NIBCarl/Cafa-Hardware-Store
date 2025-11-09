import axios from 'axios';

// Create a dedicated axios instance for customer API with automatic auth token
const customerApiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

// Add interceptor to automatically include customer token
customerApiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('customer_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Only set Content-Type to JSON if it's not FormData
    // For FormData, let axios set it automatically with boundary
    if (!(config.data instanceof FormData)) {
      config.headers['Content-Type'] = 'application/json';
    }
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

const API_BASE = '/customer';

export const customerProductsApi = {
  list(params = {}) {
    return customerApiClient.get(`${API_BASE}/products`, { params });
  },

  show(id) {
    return customerApiClient.get(`${API_BASE}/products/${id}`);
  },

  featured(limit = 8) {
    return customerApiClient.get(`${API_BASE}/products/featured/list`, { params: { limit } });
  },

  categories() {
    return customerApiClient.get(`${API_BASE}/categories`);
  },
};

export const customerOrdersApi = {
  list(params = {}) {
    return customerApiClient.get(`${API_BASE}/orders`, { params });
  },

  create(orderData) {
    // The interceptor will automatically handle Content-Type
    // based on whether orderData is FormData or not
    return customerApiClient.post(`${API_BASE}/orders`, orderData);
  },

  show(id) {
    return customerApiClient.get(`${API_BASE}/orders/${id}`);
  },

  cancel(id) {
    return customerApiClient.post(`${API_BASE}/orders/${id}/cancel`);
  },
};

