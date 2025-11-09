<template>
  <div class="fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Adjust Stock for {{ product.name }}
          </h3>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Stock</label>
              <div class="text-2xl font-bold text-gray-900">
                {{ product.stock_quantity }} units
              </div>
            </div>

            <div>
              <label for="new_quantity" class="block text-sm font-medium text-gray-700">New Quantity</label>
              <input
                id="new_quantity"
                v-model="form.new_quantity"
                type="number"
                min="0"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              />
              <p v-if="difference !== 0" class="mt-1 text-sm" :class="difference > 0 ? 'text-green-600' : 'text-red-600'">
                {{ difference > 0 ? '+' : '' }}{{ difference }} units
              </p>
            </div>

            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                placeholder="Reason for adjustment..."
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              ></textarea>
            </div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
              <button
                type="submit"
                :disabled="saving || form.new_quantity === product.stock_quantity"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:col-start-2 sm:text-sm disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Adjust Stock' }}
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
import { ref, computed } from 'vue';
import { productsApi } from '@/services/api';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close', 'saved']);
const toastStore = useToastStore();

const saving = ref(false);
const form = ref({
  new_quantity: props.product.stock_quantity,
  notes: '',
});

const difference = computed(() => {
  return form.value.new_quantity - props.product.stock_quantity;
});

const handleSubmit = async () => {
  saving.value = true;

  try {
    await productsApi.adjustStock(props.product.id, form.value);
    toastStore.success('Stock adjusted successfully');
    emit('saved');
  } catch (error) {
    console.error('Failed to adjust stock:', error);
    toastStore.error(error.response?.data?.message || 'Failed to adjust stock');
  } finally {
    saving.value = false;
  }
};
</script>
