<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div class="space-y-6">
      <BaseInput
        id="api_key"
        v-model="form.api_key"
        label="Semaphore API Key"
        :error="errors.api_key"
        required
        type="password"
      />

      <div>
        <label class="block text-sm font-medium text-gray-700">
          SMS Templates
        </label>
        <p class="mt-1 text-sm text-gray-500">
          Configure message templates for different notifications. Available variables: {customer_name}, {order_number}, {amount}, {items}, {date}
        </p>

        <div class="mt-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Order Confirmation
            </label>
            <textarea
              v-model="form.templates.order_confirmation"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              :class="{ 'border-red-300': errors['templates.order_confirmation'] }"
            ></textarea>
            <p
              v-if="errors['templates.order_confirmation']"
              class="mt-1 text-sm text-red-600"
            >
              {{ errors['templates.order_confirmation'] }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              Low Stock Alert
            </label>
            <textarea
              v-model="form.templates.low_stock"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              :class="{ 'border-red-300': errors['templates.low_stock'] }"
            ></textarea>
            <p
              v-if="errors['templates.low_stock']"
              class="mt-1 text-sm text-red-600"
            >
              {{ errors['templates.low_stock'] }}
            </p>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">
          Test SMS
        </label>
        <div class="mt-1 flex space-x-3">
          <BaseInput
            id="test_number"
            v-model="testNumber"
            placeholder="Enter phone number"
            class="flex-1"
          />
          <BaseButton
            type="button"
            variant="secondary"
            :loading="isTesting"
            @click="sendTestSms"
          >
            Send Test
          </BaseButton>
        </div>
      </div>
    </div>

    <div class="flex justify-end space-x-3">
      <BaseButton
        type="submit"
        variant="primary"
        :loading="isSubmitting"
      >
        Save Settings
      </BaseButton>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useToastStore } from '../../stores/toast';
import BaseInput from '../base/BaseInput.vue';
import BaseButton from '../base/BaseButton.vue';

const props = defineProps({
  settings: {
    type: Object,
    required: true
  }
});

const toastStore = useToastStore();
const isSubmitting = ref(false);
const isTesting = ref(false);
const errors = reactive({});
const testNumber = ref('');

const form = reactive({
  api_key: props.settings.api_key || '',
  templates: {
    order_confirmation: props.settings.templates?.order_confirmation || 'Thank you for your purchase! Order #{order_number} total: ₱{amount}. Items: {items}',
    low_stock: props.settings.templates?.low_stock || 'Alert: {items} is running low on stock. Current quantity: {amount}'
  }
});

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;
    // Clear previous errors
    Object.keys(errors).forEach(key => delete errors[key]);

    const response = await fetch('/api/settings/sms', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(form)
    });

    if (!response.ok) {
      if (response.status === 422) {
        const data = await response.json();
        Object.assign(errors, data.errors);
        return;
      }
      throw new Error('Failed to save settings');
    }

    toastStore.success('SMS settings saved successfully');
  } catch (error) {
    toastStore.error(error.message);
  } finally {
    isSubmitting.value = false;
  }
};

const sendTestSms = async () => {
  if (!testNumber.value) {
    toastStore.error('Please enter a phone number');
    return;
  }

  try {
    isTesting.value = true;
    const response = await fetch('/api/settings/sms/test', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        phone_number: testNumber.value
      })
    });

    if (!response.ok) {
      throw new Error('Failed to send test SMS');
    }

    toastStore.success('Test SMS sent successfully');
    testNumber.value = '';
  } catch (error) {
    toastStore.error(error.message);
  } finally {
    isTesting.value = false;
  }
};
</script>
