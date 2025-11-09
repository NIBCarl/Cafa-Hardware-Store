import apiClient from './client';

export const categoryService = {
  list(params = {}) {
    return apiClient.get('/categories', { params });
  },

  get(id) {
    return apiClient.get(`/categories/${id}`);
  },

  create(category) {
    return apiClient.post('/categories', category);
  },

  update(id, category) {
    return apiClient.put(`/categories/${id}`, category);
  },

  delete(id) {
    return apiClient.delete(`/categories/${id}`);
  }
};