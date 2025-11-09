import apiClient from './client';

/**
 * Order Management API Service
 * Handles all staff-facing order management operations
 */

export const ordersApi = {
  /**
   * Get paginated list of customer orders
   * @param {Object} params - Query parameters (page, per_page, status, search, start_date, end_date)
   */
  getOrders(params = {}) {
    return apiClient.get('/orders', { params });
  },

  /**
   * Get a specific order by ID
   * @param {number} orderId 
   */
  getOrder(orderId) {
    return apiClient.get(`/orders/${orderId}`);
  },

  /**
   * Update order status
   * @param {number} orderId 
   * @param {string} status - pending, processing, ready, completed, cancelled
   */
  updateOrderStatus(orderId, status) {
    return apiClient.post(`/orders/${orderId}/update-status`, { status });
  },

  /**
   * Cancel an order (admin override)
   * @param {number} orderId 
   */
  cancelOrder(orderId) {
    return apiClient.post(`/orders/${orderId}/cancel`);
  },

  /**
   * Get order statistics
   */
  getStats() {
    return apiClient.get('/orders/stats');
  },

  /**
   * Get pending orders (for POS integration)
   * Fetches orders with status: pending, processing, or ready
   */
  getPendingOrders() {
    return apiClient.get('/orders', {
      params: {
        status: 'pending,processing,ready',
        per_page: 50
      }
    });
  },

  /**
   * Verify payment (approve or reject)
   * @param {number} orderId 
   * @param {Object} data - { action: 'approve|reject', payment_reference: string, notes: string }
   */
  verifyPayment(orderId, data) {
    return apiClient.post(`/orders/${orderId}/verify-payment`, data);
  }
};

export default ordersApi;

