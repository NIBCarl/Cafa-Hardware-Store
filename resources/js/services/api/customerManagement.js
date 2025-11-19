import apiClient from './client';

export const customerManagementApi = {
  /**
   * Get paginated list of customers
   * @param {Object} params - Query parameters
   */
  list(params = {}) {
    return apiClient.get('/customers', { params });
  },

  /**
   * Create a new customer
   * @param {Object} data 
   */
  create(data) {
    return apiClient.post('/customers', data);
  },

  /**
   * Get a specific customer
   * @param {number} id 
   */
  show(id) {
    return apiClient.get(`/customers/${id}`);
  },

  /**
   * Update a customer
   * @param {number} id 
   * @param {Object} data 
   */
  update(id, data) {
    return apiClient.put(`/customers/${id}`, data);
  },

  /**
   * Delete a customer
   * @param {number} id 
   */
  delete(id) {
    return apiClient.delete(`/customers/${id}`);
  },

  /**
   * Toggle customer active status
   * @param {number} id 
   */
  toggleStatus(id) {
    return apiClient.post(`/customers/${id}/toggle-status`);
  },

  /**
   * Get customer statistics
   */
  stats() {
    return apiClient.get('/customers/stats');
  }
};

export default customerManagementApi;
