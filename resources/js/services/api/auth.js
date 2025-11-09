import apiClient from './client';

export const authService = {
  /**
   * Login with credentials
   * @param {Object} credentials
   * @param {string} credentials.email
   * @param {string} credentials.password
   * @param {string} credentials.device_name
   * @returns {Promise}
   */
  login(credentials) {
    return apiClient.post('/login', credentials);
  },

  /**
   * Logout the current user
   * @returns {Promise}
   */
  logout() {
    return apiClient.post('/logout');
  },

  /**
   * Get the current authenticated user
   * @returns {Promise}
   */
  getCurrentUser() {
    return apiClient.get('/user');
  }
};
