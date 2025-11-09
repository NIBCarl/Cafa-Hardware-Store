<template>
  <div class="fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            {{ category ? 'Edit Category' : 'Add New Category' }}
          </h3>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              />
            </div>

            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              ></textarea>
            </div>

            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active
              </label>
            </div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
              <button
                type="submit"
                :disabled="saving"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:col-start-2 sm:text-sm disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
              <button
                type="button"
                @click="$emit('close')"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { categoriesApi } from '@/services/api';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
  category: Object,
});

const emit = defineEmits(['close', 'saved']);
const toastStore = useToastStore();

const saving = ref(false);
const form = ref({
  name: '',
  description: '',
  is_active: true,
});

// Initialize form with category data if editing
watch(() => props.category, (category) => {
  if (category) {
    form.value = {
      name: category.name,
      description: category.description || '',
      is_active: category.is_active,
    };
  }
}, { immediate: true });

const handleSubmit = async () => {
  saving.value = true;

  try {
    if (props.category) {
      // Update existing category
      await categoriesApi.update(props.category.id, form.value);
      toastStore.success('Category updated successfully');
    } else {
      // Create new category
      await categoriesApi.create(form.value);
      toastStore.success('Category created successfully');
    }

    emit('saved');
  } catch (error) {
    console.error('Failed to save category:', error);
    toastStore.error(error.response?.data?.message || 'Failed to save category');
  } finally {
    saving.value = false;
  }
};
</script>
