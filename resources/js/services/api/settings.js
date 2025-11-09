import apiClient from './client';

export const settingsService = {
  // SMS Settings
  getSms() {
    return apiClient.get('/settings/sms');
  },

  updateSms(data) {
    return apiClient.post('/settings/sms', data);
  },

  // System Settings
  getSystem() {
    return apiClient.get('/settings/system');
  },

  updateSystem(data) {
    return apiClient.post('/settings/system', data);
  },
};

