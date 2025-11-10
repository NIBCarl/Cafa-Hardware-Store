import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
  }),

  getters: {
    subtotal: (state) => {
      return state.items.reduce((total, item) => {
        return total + (item.product.price * item.quantity);
      }, 0);
    },

    total() {
      return this.subtotal;
    },

    itemCount: (state) => {
      return state.items.reduce((count, item) => count + item.quantity, 0);
    },
  },

  actions: {
    addItem(product) {
      const existingItem = this.items.find(item => item.product.id === product.id);
      
      if (existingItem) {
        existingItem.quantity++;
      } else {
        this.items.push({
          product,
          quantity: 1,
        });
      }
    },

    removeItem(productId) {
      const index = this.items.findIndex(item => item.product.id === productId);
      if (index > -1) {
        this.items.splice(index, 1);
      }
    },

    increaseQuantity(productId) {
      const item = this.items.find(item => item.product.id === productId);
      if (item) {
        item.quantity++;
      }
    },

    decreaseQuantity(productId) {
      const item = this.items.find(item => item.product.id === productId);
      if (item && item.quantity > 1) {
        item.quantity--;
      } else if (item && item.quantity === 1) {
        this.removeItem(productId);
      }
    },

    clearCart() {
      this.items = [];
    },

    loadFromOrder(order) {
      // Clear existing cart
      this.clearCart();
      
      // Load items from order
      if (order.items && Array.isArray(order.items)) {
        order.items.forEach(orderItem => {
          if (orderItem.product) {
            this.items.push({
              product: orderItem.product,
              quantity: orderItem.quantity,
            });
          }
        });
      }
    },
  },
});
