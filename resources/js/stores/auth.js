import { defineStore } from 'pinia';
import { authApi } from '@/services/api';
import apiClient from '@/services/api/client';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async login(credentials) {
      try {
        const response = await authApi.login(credentials);
        const { token, user } = response.data;
        
        this.token = token;
        this.user = user;
        
        localStorage.setItem('token', token);
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        
        return response;
      } catch (error) {
        throw error;
      }
    },

    async logout() {
      try {
        await authApi.logout();
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
        delete apiClient.defaults.headers.common['Authorization'];
      }
    },

    async fetchUser() {
      try {
        const response = await authApi.getCurrentUser();
        this.user = response.data;
        return response;
      } catch (error) {
        this.logout();
        throw error;
      }
    },

    async updateProfile(data) {
      try {
        const response = await apiClient.put('/profile', data);
        this.user = response.data.user;
        return response;
      } catch (error) {
        throw error;
      }
    },

    async changePassword(data) {
      try {
        const response = await apiClient.post('/change-password', data);
        return response;
      } catch (error) {
        throw error;
      }
    },

    initializeAuth() {
      const token = localStorage.getItem('token');
      if (token) {
        this.token = token;
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        this.fetchUser();
      }
    }
  }
});