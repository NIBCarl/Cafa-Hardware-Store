import apiClient from './client';

export const transactionService = {
  list(params = {}) {
    return apiClient.get('/transactions', { params });
  },

  get(id) {
    return apiClient.get(`/transactions/${id}`);
  },

  create(transaction) {
    return apiClient.post('/transactions', transaction);
  },

  refund(id, data) {
    return apiClient.post(`/transactions/${id}/refund`, data);
  }
};