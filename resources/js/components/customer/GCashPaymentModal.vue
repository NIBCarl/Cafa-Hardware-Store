<template>
  <BaseModal
    v-model="isOpen"
    title="GCash Payment"
    size="lg"
    @close="handleClose"
  >
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
      <p class="mt-4 text-gray-600">Loading payment information...</p>
    </div>

    <div v-else class="space-y-6">
      <!-- Payment Instructions -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
          <svg class="h-6 w-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">Payment Instructions</h3>
            <div class="mt-2 text-sm text-blue-700">
              <ol class="list-decimal list-inside space-y-1">
                <li>Send the exact amount via GCash to the number below</li>
                <li>Take a screenshot of your payment receipt</li>
                <li>Upload the screenshot using the form below</li>
                <li>Click "Submit Order" to complete your purchase</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- GCash Details -->
      <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Send Payment To:</h3>
          <svg class="h-10 w-10" viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="24" r="22" fill="white" fill-opacity="0.2"/>
            <text x="24" y="30" text-anchor="middle" fill="white" font-size="20" font-weight="bold">₱</text>
          </svg>
        </div>
        
        <div class="space-y-3">
          <div>
            <p class="text-blue-200 text-sm">GCash Number</p>
            <div class="flex items-center justify-between bg-white bg-opacity-20 rounded-md p-3 mt-1">
              <p class="text-2xl font-bold tracking-wide">{{ paymentInfo.gcash_number || 'Not configured' }}</p>
              <button
                v-if="paymentInfo.gcash_number"
                @click="copyToClipboard(paymentInfo.gcash_number)"
                class="ml-2 text-white hover:text-blue-100"
                title="Copy number"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
              </button>
            </div>
          </div>

          <div>
            <p class="text-blue-200 text-sm">Account Name</p>
            <p class="text-lg font-medium mt-1">{{ paymentInfo.gcash_name || 'CAFA Hardware Store' }}</p>
          </div>

          <div class="pt-3 border-t border-blue-500">
            <p class="text-blue-200 text-sm">Amount to Pay</p>
            <p class="text-3xl font-bold mt-1">₱{{ formatPrice(totalAmount) }}</p>
          </div>
        </div>
      </div>

      <!-- File Upload -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Payment Receipt <span class="text-red-500">*</span>
        </label>
        <div class="mt-1">
          <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/jpg,image/png"
            @change="handleFileChange"
            class="hidden"
          />
          
          <!-- Upload Button/Preview -->
          <div
            v-if="!selectedFile"
            @click="$refs.fileInput.click()"
            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-primary-500 transition-colors"
          >
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <p class="mt-2 text-sm font-medium text-gray-900">Click to upload receipt</p>
            <p class="mt-1 text-xs text-gray-500">PNG, JPG up to 5MB</p>
          </div>

          <!-- File Preview -->
          <div v-else class="relative">
            <div class="border border-gray-300 rounded-lg p-4">
              <img
                v-if="filePreview"
                :src="filePreview"
                alt="Receipt preview"
                class="max-h-64 mx-auto rounded-md"
              />
              <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center text-sm text-gray-600">
                  <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ selectedFile.name }} ({{ formatFileSize(selectedFile.size) }})
                </div>
                <button
                  @click="clearFile"
                  class="text-red-600 hover:text-red-800"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
        <p v-if="fileError" class="mt-2 text-sm text-red-600">{{ fileError }}</p>
        <p v-else class="mt-2 text-xs text-gray-500">
          Upload a clear screenshot of your GCash payment receipt
        </p>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
          <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="ml-3 text-sm text-red-800">{{ error }}</p>
        </div>
      </div>
    </div>

    <template #footer>
      <button
        @click="handleSubmit"
        :disabled="!selectedFile || submitting"
        class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent bg-primary-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed sm:text-sm"
      >
        <span v-if="submitting" class="flex items-center">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Processing...
        </span>
        <span v-else>Submit Order</span>
      </button>
      <button
        @click="handleClose"
        :disabled="submitting"
        class="mt-3 w-full sm:mt-0 sm:w-auto inline-flex justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 sm:text-sm"
      >
        Cancel
      </button>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import BaseModal from '@/components/base/BaseModal.vue';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  totalAmount: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'submit', 'close']);

const isOpen = ref(props.modelValue);
const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const fileError = ref('');

const paymentInfo = ref({
  gcash_enabled: true,
  gcash_number: '09489770597',
  gcash_name: 'CAFA Hardware Store'
});

const selectedFile = ref(null);
const filePreview = ref(null);
const fileInput = ref(null);

watch(() => props.modelValue, (newValue) => {
  isOpen.value = newValue;
  if (newValue) {
    loadPaymentInfo();
  }
});

watch(isOpen, (newValue) => {
  emit('update:modelValue', newValue);
});

const loadPaymentInfo = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const response = await axios.get('/api/payment-info');
    paymentInfo.value = response.data.gcash;
    
    // Use default values if not configured
    if (!paymentInfo.value.gcash_number) {
      paymentInfo.value.gcash_number = '09489770597';
    }
    if (!paymentInfo.value.gcash_name) {
      paymentInfo.value.gcash_name = 'CAFA Hardware Store';
    }
  } catch (err) {
    error.value = 'Failed to load payment information. Please try again.';
    console.error('Error loading payment info:', err);
  } finally {
    loading.value = false;
  }
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  fileError.value = '';
  
  if (!file) return;
  
  // Validate file type
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
  if (!validTypes.includes(file.type)) {
    fileError.value = 'Please upload a JPG or PNG image';
    event.target.value = '';
    return;
  }
  
  // Validate file size (5MB)
  const maxSize = 5 * 1024 * 1024;
  if (file.size > maxSize) {
    fileError.value = 'File size must not exceed 5MB';
    event.target.value = '';
    return;
  }
  
  selectedFile.value = file;
  
  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    filePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const clearFile = () => {
  selectedFile.value = null;
  filePreview.value = null;
  fileError.value = '';
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const handleSubmit = () => {
  if (!selectedFile.value) {
    fileError.value = 'Please upload a payment receipt';
    return;
  }
  
  emit('submit', selectedFile.value);
};

const handleClose = () => {
  if (!submitting.value) {
    clearFile();
    error.value = '';
    isOpen.value = false;
    emit('close');
  }
};

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text);
    // Could add a toast notification here
    alert('GCash number copied to clipboard!');
  } catch (err) {
    console.error('Failed to copy:', err);
  }
};

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2);
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
  if (props.modelValue) {
    loadPaymentInfo();
  }
});

// Expose methods if needed
defineExpose({
  setSubmitting: (value) => {
    submitting.value = value;
  },
  setError: (message) => {
    error.value = message;
  }
});
</script>
