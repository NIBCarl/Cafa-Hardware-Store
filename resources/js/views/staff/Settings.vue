<template>
  <div class="h-full flex flex-col">
    <header class="bg-white shadow">
      <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Settings</h1>
      </div>
    </header>

    <main class="flex-1 py-6">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="space-y-6">
          <!-- Profile Settings -->
          <SettingsSection
            title="Profile Settings"
            description="Update your account information and password"
          >
            <ProfileForm :user="user" />
          </SettingsSection>

          <!-- SMS Settings (Admin Only) -->
          <SettingsSection
            v-if="isAdmin"
            title="SMS Settings"
            description="Configure Semaphore SMS integration and message templates"
          >
            <SmsSettingsForm :settings="smsSettings" />
          </SettingsSection>

          <!-- System Settings (Admin Only) -->
          <SettingsSection
            v-if="isAdmin"
            title="System Settings"
            description="Configure general system settings"
          >
            <form @submit.prevent="saveSystemSettings" class="space-y-6">
              <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                <div>
                  <BaseInput
                    id="store_name"
                    v-model="systemSettings.store_name"
                    label="Store Name"
                    required
                  />
                </div>

                <div>
                  <BaseInput
                    id="contact_number"
                    v-model="systemSettings.contact_number"
                    label="Store Contact Number"
                    required
                  />
                </div>

                <div>
                  <BaseInput
                    id="address"
                    v-model="systemSettings.address"
                    label="Store Address"
                    required
                  />
                </div>

                <div>
                  <BaseInput
                    id="tax_rate"
                    v-model="systemSettings.tax_rate"
                    type="number"
                    label="Tax Rate (%)"
                    required
                    min="0"
                    max="100"
                    step="0.01"
                  />
                </div>
              </div>

              <div class="flex justify-end">
                <BaseButton
                  type="submit"
                  variant="primary"
                  :loading="isSavingSystem"
                >
                  Save Settings
                </BaseButton>
              </div>
            </form>
          </SettingsSection>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { settingsApi } from '../../services/api';
import SettingsSection from '../../components/settings/SettingsSection.vue';
import ProfileForm from '../../components/settings/ProfileForm.vue';
import SmsSettingsForm from '../../components/settings/SmsSettingsForm.vue';
import BaseInput from '../../components/base/BaseInput.vue';
import BaseButton from '../../components/base/BaseButton.vue';

const authStore = useAuthStore();
const toastStore = useToastStore();

// User data
const user = computed(() => authStore.user);
const isAdmin = computed(() => user.value?.role === 'admin');

// SMS Settings
const smsSettings = ref({
  api_key: '',
  templates: {
    order_confirmation: '',
    low_stock: ''
  }
});

// System Settings
const systemSettings = reactive({
  store_name: '',
  contact_number: '',
  address: '',
  tax_rate: 12
});
const isSavingSystem = ref(false);

// Methods
const loadSmsSettings = async () => {
  try {
    const response = await settingsApi.getSms();
    smsSettings.value = response.data;
  } catch (error) {
    console.error('Failed to load SMS settings:', error);
    toastStore.error('Failed to load SMS settings');
  }
};

const loadSystemSettings = async () => {
  try {
    const response = await settingsApi.getSystem();
    Object.assign(systemSettings, response.data);
  } catch (error) {
    console.error('Failed to load system settings:', error);
    toastStore.error('Failed to load system settings');
  }
};

const saveSystemSettings = async () => {
  try {
    isSavingSystem.value = true;
    await settingsApi.updateSystem(systemSettings);
    toastStore.success('System settings saved successfully');
  } catch (error) {
    console.error('Failed to save system settings:', error);
    toastStore.error('Failed to save system settings');
  } finally {
    isSavingSystem.value = false;
  }
};

// Lifecycle
onMounted(async () => {
  if (isAdmin.value) {
    await Promise.all([
      loadSmsSettings(),
      loadSystemSettings()
    ]);
  }
});
</script>
