import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCustomerCartStore = defineStore('customerCart', () => {
  const items = ref([]);

  const itemCount = computed(() => {
    return items.value.reduce((sum, item) => sum + item.quantity, 0);
  });

  const subtotal = computed(() => {
    return items.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
  });

  const total = computed(() => {
    return subtotal.value;
  });

  function addToCart(product, quantity = 1) {
    const existingItem = items.value.find(item => item.product.id === product.id);

    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      items.value.push({
        product,
        quantity,
      });
    }

    saveToLocalStorage();
  }

  function updateQuantity(productId, quantity) {
    const item = items.value.find(item => item.product.id === productId);
    if (item) {
      if (quantity <= 0) {
        removeItem(productId);
      } else {
        item.quantity = quantity;
        saveToLocalStorage();
      }
    }
  }

  function removeItem(productId) {
    items.value = items.value.filter(item => item.product.id !== productId);
    saveToLocalStorage();
  }

  function clearCart() {
    items.value = [];
    saveToLocalStorage();
  }

  function saveToLocalStorage() {
    localStorage.setItem('customer_cart', JSON.stringify(items.value));
  }

  function loadFromLocalStorage() {
    const saved = localStorage.getItem('customer_cart');
    if (saved) {
      try {
        items.value = JSON.parse(saved);
      } catch (e) {
        console.error('Failed to load cart from localStorage', e);
        items.value = [];
      }
    }
  }

  // Load cart from localStorage on initialization
  loadFromLocalStorage();

  return {
    items,
    itemCount,
    subtotal,
    total,
    addToCart,
    updateQuantity,
    removeItem,
    clearCart,
  };
});

