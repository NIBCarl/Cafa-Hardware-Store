import apiClient from './client';

export const productService = {
  list(params = {}) {
    return apiClient.get('/products', { params });
  },

  get(id) {
    return apiClient.get(`/products/${id}`);
  },

  create(product) {
    return apiClient.post('/products', product, {
      headers: product instanceof FormData ? { 'Content-Type': 'multipart/form-data' } : {}
    });
  },

  update(id, product) {
    return apiClient.put(`/products/${id}`, product);
  },

  // Special method for updating with FormData (file upload)
  // Uses POST with _method spoofing since FormData doesn't work with PUT
  createOrUpdateWithImage(id, formData) {
    return apiClient.post(`/products/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  },

  delete(id) {
    return apiClient.delete(`/products/${id}`);
  },

  getLowStock() {
    return apiClient.get('/products/low-stock');
  },

  adjustStock(id, data) {
    return apiClient.post(`/products/${id}/adjust-stock`, data);
  }
};