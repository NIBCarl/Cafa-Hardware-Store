<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
      <div>
        <BaseInput
          id="name"
          v-model="form.name"
          label="Full Name"
          :error="errors.name"
          required
        />
      </div>

      <div>
        <BaseInput
          id="email"
          v-model="form.email"
          type="email"
          label="Email Address"
          :error="errors.email"
          required
        />
      </div>

      <div>
        <BaseInput
          id="phone_number"
          v-model="form.phone_number"
          label="Phone Number"
          :error="errors.phone_number"
        />
      </div>
    </div>

    <div class="space-y-1">
      <label class="block text-sm font-medium text-gray-700">
        Current Role
      </label>
      <p class="text-sm text-gray-500">
        {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
      </p>
    </div>

    <div class="border-t border-gray-200 pt-6">
      <h3 class="text-lg font-medium leading-6 text-gray-900">Change Password</h3>
      <p class="mt-1 text-sm text-gray-500">
        Leave password fields empty if you don't want to change it.
      </p>

      <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
        <div>
          <BaseInput
            id="current_password"
            v-model="form.current_password"
            type="password"
            label="Current Password"
            :error="errors.current_password"
          />
        </div>

        <div>
          <BaseInput
            id="new_password"
            v-model="form.new_password"
            type="password"
            label="New Password"
            :error="errors.new_password"
          />
        </div>

        <div>
          <BaseInput
            id="new_password_confirmation"
            v-model="form.new_password_confirmation"
            type="password"
            label="Confirm New Password"
            :error="errors.new_password_confirmation"
          />
        </div>
      </div>
    </div>

    <div class="flex justify-end space-x-3">
      <BaseButton
        type="submit"
        variant="primary"
        :loading="isSubmitting"
      >
        Save Changes
      </BaseButton>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import BaseInput from '../base/BaseInput.vue';
import BaseButton from '../base/BaseButton.vue';

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
});

const authStore = useAuthStore();
const toastStore = useToastStore();
const isSubmitting = ref(false);
const errors = reactive({});

const form = reactive({
  name: props.user.name,
  email: props.user.email,
  phone_number: props.user.phone_number,
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;
    // Clear previous errors
    Object.keys(errors).forEach(key => delete errors[key]);

    const profileData = {
      name: form.name,
      email: form.email,
      phone_number: form.phone_number
    };

    // Update profile
    await authStore.updateProfile(profileData);

    // Only change password if user provided current password
    if (form.current_password && form.new_password) {
      const passwordData = {
        current_password: form.current_password,
        new_password: form.new_password,
        new_password_confirmation: form.new_password_confirmation
      };
      await authStore.changePassword(passwordData);
    }
    
    // Reset password fields
    form.current_password = '';
    form.new_password = '';
    form.new_password_confirmation = '';

    toastStore.success('Profile updated successfully');
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors);
    } else {
      toastStore.error('Failed to update profile');
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script>
