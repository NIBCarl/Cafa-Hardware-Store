import apiClient from './client';

export const userService = {
  list(params = {}) {
    return apiClient.get('/users', { params });
  },

  get(id) {
    return apiClient.get(`/users/${id}`);
  },

  create(data) {
    return apiClient.post('/users', data);
  },

  update(id, data) {
    return apiClient.put(`/users/${id}`, data);
  },

  delete(id) {
    return apiClient.delete(`/users/${id}`);
  },

  toggleStatus(id) {
    return apiClient.post(`/users/${id}/toggle-status`);
  },
};

